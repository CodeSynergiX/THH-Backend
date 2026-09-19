<?php

namespace App\Domains\Content\Models;

use App\Domains\Users\Models\Village;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillageReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'user_id',
        'title',
        'category',
        'description',
        'photos',
        'status',
        'lat',
        'lng',
    ];

    protected $casts = [
        'photos' => 'array',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
