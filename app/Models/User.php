<?php

namespace App\Models;

use App\Domains\Cases\Models\Application;
use App\Domains\Users\Models\DeviceToken;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $gender
 * @property int|null $age
 * @property CarbonInterface|null $date_of_birth
 * @property string|null $blood_group
 * @property string|null $helper_status
 * @property bool $on_duty
 * @property string|null $occupation
 * @property string|null $education
 * @property string|null $income_category
 * @property string|null $community
 * @property string|null $locale
 * @property string|null $theme_preference
 * @property int|null $district_id
 * @property int|null $taluka_id
 * @property int|null $village_id
 * @property bool $is_active
 * @property CarbonInterface|null $email_verified_at
 * @property CarbonInterface|null $consent_at
 * @property CarbonInterface|null $last_login_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $deleted_at
 * @property District|null $district
 * @property Taluka|null $taluka
 * @property Village|null $village
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    public const HELPER_PENDING = 'pending';

    public const HELPER_APPROVED = 'approved';

    public const HELPER_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'gender',
        'age',
        'date_of_birth',
        'blood_group',
        'district_id',
        'taluka_id',
        'village_id',
        'address',
        'pincode',
        'ration_card_no',
        'avatar_url',
        'blood_donor_active',
        'sms_alerts_active',
        'occupation',
        'education',
        'income_category',
        'community',
        'locale',
        'theme_preference',
        'fcm_status',
        'is_active',
        'helper_status',
        'on_duty',
        'consent_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'consent_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'on_duty' => 'boolean',
            'blood_donor_active' => 'boolean',
            'sms_alerts_active' => 'boolean',
            'age' => 'integer',
            'date_of_birth' => 'date',
        ];
    }

    public function syncDisplayName(?string $firstName = null, ?string $lastName = null): void
    {
        $first = $firstName ?? $this->first_name;
        $last = $lastName ?? $this->last_name;
        $composed = trim(implode(' ', array_filter([$first, $last])));
        if ($composed !== '') {
            $this->name = $composed;
        }
    }

    public function isApprovedHelper(): bool
    {
        if ($this->hasRole(['super_admin', 'admin', 'staff', 'collector'])) {
            return true;
        }

        if (! $this->hasRole(['mentor', 'volunteer'])) {
            return false;
        }

        // Only explicitly pending or rejected helpers are unapproved
        if ($this->helper_status === self::HELPER_PENDING || $this->helper_status === self::HELPER_REJECTED) {
            return false;
        }

        return true;
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function taluka(): BelongsTo
    {
        return $this->belongsTo(Taluka::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function assignedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'current_assignee_id');
    }

    /**
     * Determine user's effective scope for data visibility.
     * Returns one of: all, district, village, assigned, own
     */
    public function getEffectiveScope(): string
    {
        if ($this->hasRole(['super_admin', 'admin', 'staff', 'collector'])) {
            return 'all';
        }

        if ($this->hasRole(['mentor', 'volunteer'])) {
            return 'assigned';
        }

        return 'own';
    }
}
