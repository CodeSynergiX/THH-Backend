<?php

namespace App\Models;

use App\Domains\Cases\Models\Application;
use App\Domains\Users\Models\DeviceToken;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
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
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $gender
 * @property int|null $age
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
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'gender',
        'age',
        'district_id',
        'taluka_id',
        'village_id',
        'occupation',
        'education',
        'income_category',
        'community',
        'locale',
        'theme_preference',
        'fcm_status',
        'is_active',
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
            'age' => 'integer',
        ];
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
        if ($this->hasRole(['super_admin', 'admin'])) {
            return 'all';
        }

        if ($this->hasRole('staff')) {
            return $this->village_id ? 'village' : ($this->district_id ? 'district' : 'all');
        }

        if ($this->hasRole(['mentor', 'volunteer'])) {
            return 'assigned';
        }

        return 'own';
    }
}
