<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'amount',
        'eligibility_rules',
        'deadline_at',
        'is_published',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'eligibility_rules' => 'array',
        'deadline_at' => 'datetime',
        'is_published' => 'boolean',
    ];
}
