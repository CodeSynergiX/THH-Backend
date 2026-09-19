<?php

namespace App\Domains\Users\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $taluka_id
 * @property string $name_en
 * @property string $name_gu
 * @property string|null $pincode
 * @property string|null $lat
 * @property string|null $lng
 * @property bool $is_active
 * @property Taluka|null $taluka
 */
class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'taluka_id',
        'name_en',
        'name_gu',
        'pincode',
        'lat',
        'lng',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function taluka(): BelongsTo
    {
        return $this->belongsTo(Taluka::class);
    }
}
