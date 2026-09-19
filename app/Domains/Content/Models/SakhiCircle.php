<?php

namespace App\Domains\Content\Models;

use App\Domains\Users\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SakhiCircle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'village_id',
        'leader_name',
        'leader_phone',
        'members_count',
        'activities',
        'is_active',
    ];

    protected $casts = [
        'members_count' => 'integer',
        'activities' => 'array',
        'is_active' => 'boolean',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
