<?php

namespace App\Domains\Notifications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $email_enabled
 * @property bool $push_enabled
 * @property bool $email_case_status_change
 * @property bool $email_assignment
 * @property bool $email_follow_up_due
 * @property bool $email_sla_breach
 * @property bool $email_case_resolved
 * @property bool $push_case_status_change
 * @property bool $push_assignment
 * @property bool $push_follow_up_due
 * @property bool $push_sla_breach
 * @property bool $push_case_resolved
 * @property string|null $quiet_hours_start
 * @property string|null $quiet_hours_end
 */
class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',
        'email_enabled',
        'push_enabled',
        'email_case_status_change',
        'email_assignment',
        'email_follow_up_due',
        'email_sla_breach',
        'email_case_resolved',
        'push_case_status_change',
        'push_assignment',
        'push_follow_up_due',
        'push_sla_breach',
        'push_case_resolved',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'email_enabled' => true,
        'push_enabled' => true,
        'email_case_status_change' => true,
        'email_assignment' => true,
        'email_follow_up_due' => true,
        'email_sla_breach' => true,
        'email_case_resolved' => true,
        'push_case_status_change' => true,
        'push_assignment' => true,
        'push_follow_up_due' => true,
        'push_sla_breach' => true,
        'push_case_resolved' => true,
    ];

    /** @var array<string, string> */
    protected $casts = [
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'email_case_status_change' => 'boolean',
        'email_assignment' => 'boolean',
        'email_follow_up_due' => 'boolean',
        'email_sla_breach' => 'boolean',
        'email_case_resolved' => 'boolean',
        'push_case_status_change' => 'boolean',
        'push_assignment' => 'boolean',
        'push_follow_up_due' => 'boolean',
        'push_sla_breach' => 'boolean',
        'push_case_resolved' => 'boolean',
    ];

    /** Map event keys to preference column suffix for quick lookup. */
    public const EVENT_MAP = [
        'case.status_changed' => 'case_status_change',
        'case.assigned' => 'assignment',
        'case_assigned' => 'assignment',
        'case.follow_up_due' => 'follow_up_due',
        'case.sla_breached' => 'sla_breach',
        'case.resolved' => 'case_resolved',
    ];

    /** Check whether email is allowed for a given event key. */
    public function emailAllowed(string $eventKey): bool
    {
        if (! $this->email_enabled) {
            return false;
        }

        $suffix = self::EVENT_MAP[$eventKey] ?? null;
        if ($suffix === null) {
            return true; // unknown events default to allowed
        }

        $column = 'email_'.$suffix;

        return (bool) ($this->{$column} ?? true);
    }

    /** Check whether push is allowed for a given event key. */
    public function pushAllowed(string $eventKey): bool
    {
        if (! $this->push_enabled) {
            return false;
        }

        $suffix = self::EVENT_MAP[$eventKey] ?? null;
        if ($suffix === null) {
            return true;
        }

        $column = 'push_'.$suffix;

        return (bool) ($this->{$column} ?? true);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
