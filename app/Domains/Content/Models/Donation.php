<?php

namespace App\Domains\Content\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'donor_id',
        'amount',
        'gateway_ref',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(FamilySupportCase::class, 'case_id');
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }
}
