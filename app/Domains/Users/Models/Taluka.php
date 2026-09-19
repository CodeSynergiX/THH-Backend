<?php

namespace App\Domains\Users\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $district_id
 * @property string $name_en
 * @property string $name_gu
 * @property string|null $code
 * @property bool $is_active
 * @property District|null $district
 */
class Taluka extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'name_en',
        'name_gu',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }
}
