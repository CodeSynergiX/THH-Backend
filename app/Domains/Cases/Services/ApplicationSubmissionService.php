<?php

namespace App\Domains\Cases\Services;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Urgency;
use App\Domains\Content\Models\Category;
use App\Domains\Settings\Services\MailSettingsService;
use App\Mail\AccountCreatedMail;
use App\Mail\ApplicationReceivedMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class ApplicationSubmissionService
{
    public function __construct(
        protected SLAEngineService $slaEngine,
    ) {}

    /**
     * Resolve the citizen for an application. Creates an account only when the email is new.
     *
     * @return array{user: User, created: bool, password: string|null}
     */
    public function resolveCitizen(?User $authenticated, array $input): array
    {
        if ($authenticated) {
            return ['user' => $authenticated, 'created' => false, 'password' => null];
        }

        $email = isset($input['email']) ? strtolower(trim((string) $input['email'])) : '';
        $phone = $input['phone'] ?? $input['beneficiary_phone'] ?? null;
        $name = $input['name'] ?? $input['beneficiary_name'] ?? 'Citizen';

        if ($email !== '') {
            $existing = User::query()->where('email', $email)->first();
            if ($existing) {
                return ['user' => $existing, 'created' => false, 'password' => null];
            }
        }

        $password = Str::password(12);
        Role::findOrCreate('citizen', 'web');
        Role::findOrCreate('citizen', 'sanctum');

        $user = User::create([
            'name' => $name,
            'email' => $email !== '' ? $email : null,
            'phone' => $phone,
            'password' => Hash::make($password),
            'locale' => 'gu',
            'is_active' => true,
            'village_id' => $input['village_id'] ?? null,
            'district_id' => $input['district_id'] ?? null,
            'taluka_id' => $input['taluka_id'] ?? null,
        ]);
        $user->assignRole('citizen');

        return ['user' => $user, 'created' => true, 'password' => $password];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function submit(User $user, array $input, ?string $idempotencyKey = null, bool $accountCreated = false, ?string $plainPassword = null): Application
    {
        if ($idempotencyKey) {
            $existing = Application::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        $urgency = Urgency::normalize($input['urgency'] ?? null);
        $priority = Urgency::toPriority($urgency);

        $categoryId = $input['category_id'] ?? null;
        if (! $categoryId && ! empty($input['module'])) {
            $category = Category::query()->where('slug', $input['module'])->first()
                ?? Category::query()->where('is_active', true)->first();
            $categoryId = $category ? $category->id : 1;
        }

        $application = Application::create([
            'case_no' => Application::generateCaseNo(),
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'sub_category_id' => $input['sub_category_id'] ?? null,
            'title' => $input['title'],
            'description' => $input['description'],
            'urgency' => $urgency,
            'priority' => $priority,
            'status' => Application::STATUS_RECEIVED,
            'village_id' => $input['village_id'] ?? $user->village_id,
            'lat' => $input['lat'] ?? null,
            'lng' => $input['lng'] ?? null,
            'sla_due_at' => $this->slaEngine->calculateDueDate($urgency, $priority),
            'idempotency_key' => $idempotencyKey,
        ]);

        if (! empty($input['documents']) && is_array($input['documents'])) {
            foreach ($input['documents'] as $doc) {
                if (! empty($doc['base64'])) {
                    $raw = $doc['base64'];
                    $ext = 'jpg';
                    if (preg_match('/^data:([^;]+);base64,/', $raw, $m)) {
                        $mime = $m[1];
                        $raw = substr($raw, strpos($raw, ',') + 1);
                        $ext = explode('/', $mime)[1] ?? 'jpg';
                    } elseif (! empty($doc['name']) && pathinfo($doc['name'], PATHINFO_EXTENSION)) {
                        $ext = pathinfo($doc['name'], PATHINFO_EXTENSION);
                    }
                    $decoded = base64_decode($raw, true);
                    if ($decoded !== false) {
                        $docFileName = 'doc_'.time().'_'.uniqid().'.'.$ext;
                        $storedPath = "applications/{$application->id}/{$docFileName}";
                        Storage::disk('public')->put($storedPath, $decoded);
                        $application->documents()->create([
                            'type' => $doc['type'] ?? 'general_doc',
                            'path' => $storedPath,
                            'status' => 'pending',
                        ]);

                        continue;
                    }
                }

                if (! empty($doc['path']) || ! empty($doc['name'])) {
                    $application->documents()->create([
                        'type' => $doc['type'] ?? 'general_doc',
                        'path' => $doc['path'] ?? $doc['name'],
                        'status' => 'pending',
                    ]);
                }
            }
        }

        $application->timelineEvents()->create([
            'event_type' => 'case_created',
            'from_status' => null,
            'to_status' => Application::STATUS_RECEIVED,
            'title_key' => 'app.timeline.case_received',
            'body' => 'Help request registered with case number '.$application->case_no,
            'actor_id' => $user->id,
            'actor_role' => 'citizen',
            'visibility' => 'public',
            'notification_status' => ['email' => 'pending', 'push' => 'skipped'],
            'created_at' => now(),
        ]);

        // NOTE: No auto-assignment on submission.
        // current_assignee_id remains null until a staff member or admin
        // manually assigns the application from the admin panel.

        $this->sendEmails($application, $user, $accountCreated, $plainPassword);

        return $application->fresh(['category', 'village', 'timelineEvents']);
    }

    private function sendEmails(Application $application, User $user, bool $accountCreated, ?string $plainPassword): void
    {
        $event = $application->timelineEvents()->latest('id')->first();
        $status = ['email' => 'skipped', 'push' => 'skipped'];

        if ($user->email) {
            MailSettingsService::apply();

            try {
                Mail::to($user->email)->send(new ApplicationReceivedMail($application));
                $status['email'] = 'sent';
            } catch (\Throwable $e) {
                Log::warning('Failed to send ApplicationReceivedMail: '.$e->getMessage());
                $status['email'] = 'failed';
            }

            if ($accountCreated && $plainPassword) {
                try {
                    Mail::to($user->email)->send(new AccountCreatedMail($user, $plainPassword));
                } catch (\Throwable $e) {
                    Log::warning('Failed to send AccountCreatedMail: '.$e->getMessage());
                    $status['email'] = 'failed';
                }
            }
        }

        if ($event) {
            $event->update(['notification_status' => $status]);
        }
    }
}
