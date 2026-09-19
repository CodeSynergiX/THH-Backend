<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessIdea extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'investment_range',
        'description',
        'market_potential',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
