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

    public const STATUS_AWAITING_CONFIRMATION = 'awaiting_confirmation';

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

    /**
     * Get the dynamic sequence of workflow stages configured for an application.
     */
    public static function getWorkflowStagesFor(?string $currentStatus = null): array
    {
        $baseStages = [
            [
                'key' => self::STATUS_RECEIVED,
                'name_en' => 'Received',
                'name_gu' => 'નવી અરજી',
                'headline_en' => 'Application Received',
                'headline_gu' => 'અરજી સફળતાપૂર્વક નોંધાઈ',
                'desc_en' => 'Application registered on portal and queued for desk review.',
                'desc_gu' => 'અરજી પોર્ટલ પર નોંધાઈ છે અને સમીક્ષા માટે કતારમાં છે.',
                'icon' => 'document-text-outline',
            ],
            [
                'key' => self::STATUS_VERIFICATION,
                'name_en' => 'Verification',
                'name_gu' => 'ચકાસણી',
                'headline_en' => 'In Verification & Inspection',
                'headline_gu' => 'ચકાસણી અને સ્થળ તપાસ',
                'desc_en' => 'Document verification and eligibility check underway.',
                'desc_gu' => 'દસ્તાવેજો અને પાત્રતાની વિગતો ચકાસવામાં આવી રહી છે.',
                'icon' => 'shield-checkmark-outline',
            ],
            [
                'key' => self::STATUS_CATEGORISED,
                'name_en' => 'Categorised',
                'name_gu' => 'વર્ગીકૃત',
                'headline_en' => 'Desk Categorised',
                'headline_gu' => 'વિભાગીય વર્ગીકરણ',
                'desc_en' => 'Application categorised and routed to the welfare department.',
                'desc_gu' => 'અરજી સંબંધિત કલ્યાણ વિભાગમાં વર્ગીકૃત કરવામાં આવી છે.',
                'icon' => 'grid-outline',
            ],
            [
                'key' => self::STATUS_ASSIGNED,
                'name_en' => 'Mentor Assigned',
                'name_gu' => 'સેવક ફાળવણી',
                'headline_en' => 'Assigned to Field Mentor',
                'headline_gu' => 'ક્ષેત્ર સેવકને સોંપેલ',
                'desc_en' => 'Assigned to local field mentor for on-ground seva.',
                'desc_gu' => 'સ્થાનિક ક્ષેત્ર સેવકને સ્થળ સહાય માટે સોંપવામાં આવેલ છે.',
                'icon' => 'person-outline',
            ],
            [
                'key' => self::STATUS_ASSISTANCE,
                'name_en' => 'Assistance',
                'name_gu' => 'સહાય કામગીરી',
                'headline_en' => 'Assistance Active',
                'headline_gu' => 'સહાય પૂરી પાડવાની કામગીરી',
                'desc_en' => 'Relief support and welfare scheme delivery underway.',
                'desc_gu' => 'સ્થળ પર સહાય અને કલ્યાણકારી લાભ પહોંચાડવાની કામગીરી ચાલુ છે.',
                'icon' => 'hand-left-outline',
            ],
            [
                'key' => self::STATUS_FOLLOW_UP,
                'name_en' => 'Follow-Up',
                'name_gu' => 'ફોલો-અપ',
                'headline_en' => 'Follow-Up Scheduled',
                'headline_gu' => 'ફોલો-અપ શેડ્યૂલ',
                'desc_en' => 'Follow-up review and progress consultation scheduled.',
                'desc_gu' => 'સ્થિતિ ચકાસણી માટે ફોલો-અપ શેડ્યૂલ કરેલ છે.',
                'icon' => 'calendar-outline',
            ],
            [
                'key' => self::STATUS_AWAITING_CONFIRMATION,
                'name_en' => 'Confirmation',
                'name_gu' => 'અરજદાર પુષ્ટિ',
                'headline_en' => 'Awaiting Confirmation',
                'headline_gu' => 'અરજદાર દ્વારા પુષ્ટિ બાકી',
                'desc_en' => 'Assistance delivered. Please review and confirm resolution.',
                'desc_gu' => 'સહાય પહોંચાડાઈ છે. કૃપા કરીને તપાસીને પુષ્ટિ કરો.',
                'icon' => 'checkmark-circle-outline',
            ],
            [
                'key' => self::STATUS_RESOLVED,
                'name_en' => 'Resolved',
                'name_gu' => 'સફળતાપૂર્વક પૂર્ણ',
                'headline_en' => 'Case Resolved & Closed',
                'headline_gu' => 'કેસ સફળતાપૂર્વક પૂર્ણ થયો',
                'desc_en' => 'Application has been successfully resolved and sanctioned.',
                'desc_gu' => 'અરજી સફળતાપૂર્વક પૂર્ણ કરવામાં આવી છે.',
                'icon' => 'checkmark-done-circle-outline',
            ],
        ];

        if ($currentStatus === self::STATUS_NEED_MORE_INFO) {
            array_splice($baseStages, 2, 0, [[
                'key' => self::STATUS_NEED_MORE_INFO,
                'name_en' => 'Need More Info',
                'name_gu' => 'વિગત જરૂરી',
                'headline_en' => 'Additional Details Needed',
                'headline_gu' => 'વધારાની વિગત જરૂરી',
                'desc_en' => 'Additional documents or details requested from applicant.',
                'desc_gu' => 'અરજી આગળ વધારવા માટે વધારાના પુરાવા જરૂરી છે.',
                'icon' => 'help-circle-outline',
            ]]);
        } elseif ($currentStatus === self::STATUS_ON_HOLD) {
            array_splice($baseStages, 2, 0, [[
                'key' => self::STATUS_ON_HOLD,
                'name_en' => 'On Hold',
                'name_gu' => 'મોકૂફ રાખેલ',
                'headline_en' => 'Case On Hold',
                'headline_gu' => 'અરજી મોકૂફ રાખવામાં આવી છે',
                'desc_en' => 'Application temporarily on hold pending departmental decision.',
                'desc_gu' => 'વિભાગીય નિર્ણય પેન્ડિંગ હોવાથી અરજી હાલ મોકૂફ છે.',
                'icon' => 'pause-circle-outline',
            ]]);
        } elseif ($currentStatus === self::STATUS_REJECTED) {
            $baseStages[] = [
                'key' => self::STATUS_REJECTED,
                'name_en' => 'Rejected',
                'name_gu' => 'અસ્વીકાર',
                'headline_en' => 'Application Rejected',
                'headline_gu' => 'અરજી અસ્વીકાર કરવામાં આવી',
                'desc_en' => 'Application does not meet required eligibility norms.',
                'desc_gu' => 'નિયમો અનુસાર અરજી મંજૂર થઈ શકી નથી.',
                'icon' => 'close-circle-outline',
            ];
        }

        return $baseStages;
    }
}
