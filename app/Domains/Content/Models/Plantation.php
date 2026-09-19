<?php

namespace App\Domains\Content\Models;

use App\Domains\Users\Models\Village;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plantation extends Model
{
    use HasFactory;

    protected $fillable = [
        'village_id',
        'user_id',
        'tree_type',
        'photo_path',
        'lat',
        'lng',
        'status',
        'last_checked_at',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'last_checked_at' => 'datetime',
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
