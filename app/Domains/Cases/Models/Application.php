<?php

namespace App\Domains\Cases\Models;

use App\Domains\Content\Models\Category;
use App\Domains\Content\Models\SubCategory;
use App\Domains\Users\Models\Village;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $case_no
 * @property int $user_id
 * @property int $category_id
 * @property int|null $sub_category_id
 * @property string $title
 * @property string $description
 * @property string $urgency
 * @property string $priority
 * @property string $status
 * @property int|null $village_id
 * @property string|null $lat
 * @property string|null $lng
 * @property int|null $current_assignee_id
 * @property CarbonInterface|null $sla_due_at
 * @property CarbonInterface|null $resolved_at
 * @property int|null $rating
 * @property string|null $feedback
 * @property string|null $idempotency_key
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $deleted_at
 * @property User|null $user
 * @property Category|null $category
 * @property SubCategory|null $subCategory
 * @property Village|null $village
 * @property User|null $currentAssignee
 * @property Collection<int, ApplicationDocument> $documents
 * @property Collection<int, ApplicationTimelineEvent> $timelineEvents
 * @property Collection<int, ApplicationTimelineEvent> $publicTimelineEvents
 * @property Collection<int, ApplicationAssignment> $assignments
 * @property Collection<int, ApplicationMessage> $messages
 * @property Collection<int, FollowUp> $followUps
 */
class Application extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_RECEIVED = 'received';

    public const STATUS_VERIFICATION = 'verification';

    public const STATUS_CATEGORISED = 'categorised';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_ASSISTANCE = 'assistance';

    public const STATUS_FOLLOW_UP = 'followUp';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_NEED_MORE_INFO = 'needMoreInfo';

    public const STATUS_ON_HOLD = 'onHold';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REOPENED = 'reopened';

    protected $fillable = [
        'case_no',
        'user_id',
        'category_id',
        'sub_category_id',
        'title',
        'description',
        'urgency',
        'priority',
        'status',
        'village_id',
        'lat',
        'lng',
        'current_assignee_id',
        'sla_due_at',
        'resolved_at',
        'rating',
        'feedback',
        'idempotency_key',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'rating' => 'integer',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<SubCategory, $this>
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * @return BelongsTo<Village, $this>
     */
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function currentAssignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_assignee_id');
    }

    /**
     * @return HasMany<ApplicationDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    /**
     * @return HasMany<ApplicationTimelineEvent, $this>
     */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(ApplicationTimelineEvent::class)->orderBy('created_at', 'asc');
    }

    /**
     * @return HasMany<ApplicationTimelineEvent, $this>
     */
    public function publicTimelineEvents(): HasMany
    {
        return $this->hasMany(ApplicationTimelineEvent::class)
            ->where('visibility', 'public')
            ->orderBy('created_at', 'asc');
    }

    /**
     * @return HasMany<ApplicationAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ApplicationAssignment::class);
    }

    /**
     * @return HasMany<ApplicationMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ApplicationMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * @return HasMany<FollowUp, $this>
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    /**
     * Generate the next atomic Case ID: THH-{YYYY}-{00001}
     */
    public static function generateCaseNo(): string
    {
        $year = date('Y');
        $prefix = "THH-{$year}-";

        $latestCase = self::withTrashed()
            ->where('case_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = 1;
        if ($latestCase && preg_match('/THH-\d{4}-(\d+)/', $latestCase->case_no, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return sprintf('%s%05d', $prefix, $nextNumber);
    }
}
