<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Content\Models\BloodRequest;
use App\Domains\Content\Models\BusinessIdea;
use App\Domains\Content\Models\Donation;
use App\Domains\Content\Models\FamilySupportCase;
use App\Domains\Content\Models\HealthCamp;
use App\Domains\Content\Models\Hospital;
use App\Domains\Content\Models\JobPosting;
use App\Domains\Content\Models\Library;
use App\Domains\Content\Models\MentorProfile;
use App\Domains\Content\Models\MentorQuestion;
use App\Domains\Content\Models\MockTest;
use App\Domains\Content\Models\Plantation;
use App\Domains\Content\Models\SakhiCircle;
use App\Domains\Content\Models\Scheme;
use App\Domains\Content\Models\Scholarship;
use App\Domains\Content\Models\StudyMaterial;
use App\Domains\Content\Models\VillageReport;
use App\Domains\Content\Models\Volunteer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function schemes(Request $request): JsonResponse
    {
        $schemes = Scheme::where('is_published', true)->paginate(15);

        return response()->json(['success' => true, 'data' => $schemes]);
    }

    public function matchSchemes(Request $request): JsonResponse
    {
        $user = $request->user();
        $userProfile = [
            'age' => $user->age ?? 25,
            'gender' => $user->gender ?? 'male',
            'income_category' => $user->income_category ?? 'BPL',
            'community' => $user->community ?? 'Tribal',
        ];

        $matched = Scheme::where('is_published', true)
            ->get()
            ->filter(fn (Scheme $scheme) => $scheme->matchesUser($userProfile))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Matched schemes based on citizen profile.',
            'data' => $matched,
        ]);
    }

    public function libraries(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Library::with('district')->where('is_active', true)->get(),
        ]);
    }

    public function scholarships(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Scholarship::where('is_published', true)->get(),
        ]);
    }

    public function mockTests(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => MockTest::withCount('questions')->where('is_active', true)->get(),
        ]);
    }

    public function mockTestDetail(int $id): JsonResponse
    {
        $test = MockTest::with('questions')->findOrFail($id);

        return response()->json(['success' => true, 'data' => $test]);
    }

    public function studyMaterials(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => StudyMaterial::where('is_active', true)->get(),
        ]);
    }

    public function mentors(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => MentorProfile::with('user')->where('is_available', true)->get(),
        ]);
    }

    public function askMentor(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['question' => ['required', 'string', 'min:10']]);

        $question = MentorQuestion::create([
            'mentor_id' => $id,
            'user_id' => $request->user()->id,
            'question' => $validated['question'],
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Question submitted.', 'data' => $question], 201);
    }

    public function jobs(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => JobPosting::where('is_active', true)->paginate(15),
        ]);
    }

    public function businessIdeas(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => BusinessIdea::where('is_active', true)->get(),
        ]);
    }

    public function familySupport(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => FamilySupportCase::where('status', 'active')->paginate(10),
        ]);
    }

    public function donate(Request $request, int $id): JsonResponse
    {
        $case = FamilySupportCase::findOrFail($id);
        $validated = $request->validate(['amount' => ['required', 'numeric', 'min:10']]);

        $donation = Donation::create([
            'case_id' => $case->id,
            'donor_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'gateway_ref' => 'REF_'.strtoupper(uniqid()),
            'status' => 'success',
        ]);

        $case->increment('amount_funded', $validated['amount']);

        return response()->json(['success' => true, 'message' => 'Donation recorded.', 'data' => $donation]);
    }

    public function healthCamps(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => HealthCamp::with('district')->where('is_active', true)->get(),
        ]);
    }

    public function hospitals(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Hospital::with('district')->where('is_active', true)->get(),
        ]);
    }

    public function bloodRequests(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => BloodRequest::with('hospital')->where('status', 'active')->latest()->get(),
        ]);
    }

    public function storeBloodRequest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_name' => ['required', 'string', 'max:150'],
            'blood_group' => ['required', 'string', 'max:10'],
            'hospital_id' => ['nullable', 'exists:hospitals,id'],
            'units_required' => ['nullable', 'integer', 'min:1', 'max:20'],
            'urgency' => ['nullable', 'string', 'in:urgent,medium,low'],
            'contact_phone' => ['required', 'string', 'max:20'],
        ]);

        $bloodRequest = BloodRequest::create([
            'patient_name' => $validated['patient_name'],
            'blood_group' => strtoupper(trim($validated['blood_group'])),
            'hospital_id' => $validated['hospital_id'] ?? null,
            'units_required' => $validated['units_required'] ?? 1,
            'urgency' => $validated['urgency'] ?? 'urgent',
            'status' => 'active',
            'contact_phone' => $validated['contact_phone'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Emergency SOS blood broadcast registered successfully.',
            'data' => $bloodRequest->load('hospital'),
        ], 201);
    }

    public function updateBloodRequestStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,fulfilled,cancelled'],
        ]);

        $bloodRequest = BloodRequest::findOrFail($id);
        $bloodRequest->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => "Emergency SOS blood request marked as {$validated['status']}.",
            'data' => $bloodRequest->load('hospital'),
        ]);
    }

    public function sakhi(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SakhiCircle::with('village')->where('is_active', true)->get(),
        ]);
    }

    public function villageReports(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => VillageReport::with(['village', 'user'])->paginate(15),
        ]);
    }

    public function storeVillageReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string'],
            'description' => ['required', 'string', 'min:10'],
            'photos' => ['nullable', 'array'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        $report = VillageReport::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Village problem report submitted.', 'data' => $report], 201);
    }

    public function plantations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Plantation::with('village')->paginate(15),
        ]);
    }

    public function storePlantation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'tree_type' => ['required', 'string', 'max:100'],
            'photo_path' => ['nullable', 'string'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ]);

        $plantation = Plantation::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => 'planted',
            'last_checked_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Plantation logged.', 'data' => $plantation], 201);
    }

    public function volunteerSignup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'skills' => ['required', 'array'],
            'availability' => ['required', 'string'],
        ]);

        $user = $request->user();
        $volunteer = Volunteer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'skills' => $validated['skills'],
                'availability' => $validated['availability'],
                'hours_contributed' => 0,
            ]
        );

        if (! $user->hasRole('volunteer')) {
            $user->assignRole('volunteer');
        }

        return response()->json(['success' => true, 'message' => 'Volunteer profile registered.', 'data' => $volunteer]);
    }
}
