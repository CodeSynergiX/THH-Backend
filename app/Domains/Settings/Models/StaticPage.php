<?php

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    protected $fillable = [
        'slug',
        'title_key',
        'content_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
