<?php

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class HomeTile extends Model
{
    protected $fillable = [
        'key',
        'icon',
        'target_route',
        'sort_order',
        'is_enabled',
        'visible_roles',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_enabled' => 'boolean',
        'visible_roles' => 'array',
    ];
}
