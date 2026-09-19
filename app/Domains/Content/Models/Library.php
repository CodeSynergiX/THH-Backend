<?php

namespace App\Domains\Content\Models;

use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Library extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district_id',
        'taluka_id',
        'village_id',
        'address',
        'contact_person',
        'phone',
        'total_books',
        'computers_count',
        'lat',
        'lng',
        'is_active',
    ];

    protected $casts = [
        'total_books' => 'integer',
        'computers_count' => 'integer',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function taluka(): BelongsTo
    {
        return $this->belongsTo(Taluka::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
