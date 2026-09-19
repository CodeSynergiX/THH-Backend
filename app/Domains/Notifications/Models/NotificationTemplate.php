<?php

namespace App\Domains\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'event_key',
        'channel',
        'title_template',
        'body_template',
        'is_enabled',
    ];

    protected $casts = [
        'title_template' => 'array',
        'body_template' => 'array',
        'is_enabled' => 'boolean',
    ];

    public function render(string $locale, array $variables = []): array
    {
        $title = $this->title_template[$locale] ?? $this->title_template['en'] ?? '';
        $body = $this->body_template[$locale] ?? $this->body_template['en'] ?? '';

        foreach ($variables as $key => $value) {
            $title = str_replace('{'.$key.'}', (string) $value, $title);
            $body = str_replace('{'.$key.'}', (string) $value, $body);
        }

        return ['title' => $title, 'body' => $body];
    }
}
