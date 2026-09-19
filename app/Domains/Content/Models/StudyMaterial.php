<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'file_path',
        'external_url',
        'file_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
