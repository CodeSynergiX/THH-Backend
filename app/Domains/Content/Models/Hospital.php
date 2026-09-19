<?php

namespace App\Domains\Content\Models;

use App\Domains\Users\Models\District;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hospital extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district_id',
        'address',
        'phone',
        'emergency_contact',
        'has_blood_bank',
        'lat',
        'lng',
        'is_active',
    ];

    protected $casts = [
        'has_blood_bank' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
