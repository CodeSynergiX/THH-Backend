<?php

namespace App\Domains\Users\Models;

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

    public function talukas(): HasMany
    {
        return $this->hasMany(Taluka::class);
    }

    public function villages(): HasManyThrough
    {
        return $this->hasManyThrough(Village::class, Taluka::class);
    }
}
