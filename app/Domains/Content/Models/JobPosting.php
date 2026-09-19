<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'company',
        'location',
        'salary_range',
        'requirements',
        'deadline_at',
        'is_active',
    ];

    protected $casts = [
        'requirements' => 'array',
        'deadline_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
