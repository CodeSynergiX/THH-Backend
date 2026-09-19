<?php

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'category_slug',
        'question_key',
        'answer_key',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
