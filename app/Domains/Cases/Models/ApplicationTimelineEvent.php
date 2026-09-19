<?php

namespace App\Domains\Cases\Models;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $application_id
 * @property string $event_type
 * @property string|null $from_status
 * @property string|null $to_status
 * @property string|null $title_key
 * @property string|null $body
 * @property int|null $actor_id
 * @property string|null $actor_role
 * @property string $visibility
 * @property array<string, mixed>|null $meta
 * @property array<string, mixed>|null $notification_status
 * @property CarbonInterface|null $created_at
 * @property Application $application
 * @property User|null $actor
 */
class ApplicationTimelineEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'event_type',
        'from_status',
        'to_status',
        'title_key',
        'body',
        'actor_id',
        'actor_role',
        'visibility',
        'meta',
        'notification_status',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'notification_status' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Application, $this>
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
