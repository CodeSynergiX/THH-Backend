<?php

namespace App\Domains\Users\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $name_en
 * @property string $name_gu
 * @property string|null $code
 * @property bool $is_active
 * @property Collection<int, Taluka> $talukas
 */
class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_gu',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<Taluka, $this>
     */
    public function talukas(): HasMany
    {
        return $this->hasMany(Taluka::class);
    }

    /**
     * @return HasManyThrough<Village, Taluka, $this>
     */
    public function villages(): HasManyThrough
    {
        return $this->hasManyThrough(Village::class, Taluka::class);
    }
}
