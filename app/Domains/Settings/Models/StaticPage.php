<?php

namespace App\Domains\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    protected $fillable = [
        'slug',
        'title_key',
        'content_key',
        'title_en',
        'title_gu',
        'body_en',
        'body_gu',
        'is_active',
    ];

    public function localizedTitle(string $locale = 'en'): string
    {
        $title = $locale === 'gu' ? ($this->title_gu ?: $this->title_en) : $this->title_en;

        return $title ?: $this->title_key ?: $this->slug;
    }

    public function localizedBody(string $locale = 'en'): string
    {
        $body = $locale === 'gu' ? ($this->body_gu ?: $this->body_en) : $this->body_en;

        return $body ?: $this->content_key ?: '';
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
