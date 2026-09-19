<?php

namespace App\Domains\Content\Models;

use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthCamp extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'organizer',
        'district_id',
        'village_id',
        'address',
        'scheduled_at',
        'doctors_specialties',
        'is_active',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'doctors_specialties' => 'array',
        'is_active' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
