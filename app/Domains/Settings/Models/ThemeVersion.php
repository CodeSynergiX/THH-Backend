<?php

namespace App\Domains\Settings\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeVersion extends Model
{
    protected $fillable = [
        'version',
        'light',
        'dark',
        'meta',
        'is_published',
        'published_by',
        'published_at',
        'notes',
    ];

    protected $casts = [
        'version' => 'integer',
        'light' => 'array',
        'dark' => 'array',
        'meta' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public static function currentPublished(): ?self
    {
        return self::where('is_published', true)->orderBy('version', 'desc')->first();
    }
}
