<?php

namespace App\Domains\Content\Models;

use App\Domains\Cases\Models\Application;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string|null $name_en
 * @property string|null $name_gu
 * @property string|null $icon
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_en',
        'name_gu',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
