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

    public function getLightAttribute($value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : ($value ?? []);

        return self::normalizeTokenArray($decoded, false);
    }

    public function getDarkAttribute($value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : ($value ?? []);

        return self::normalizeTokenArray($decoded, true);
    }

    /**
     * Ensure inner content tokens (secondary bg, secondary border, etc.) are always present.
     *
     * @param  array<string, string>|null  $tokens
     * @return array<string, string>
     */
    public static function normalizeTokenArray(?array $tokens, bool $isDark): array
    {
        $tokens = $tokens ?? [];

        $defaultSecondaryBg = $isDark ? '#2d1e13' : '#f5ebe4';
        $defaultSecondarySurface = $isDark ? '#342217' : '#ffffff';
        $defaultSecondaryBorder = $isDark ? '#4d3424' : '#dfc7b8';

        $secondaryBg = $tokens['bg_secondary']
            ?? $tokens['secondary_bg']
            ?? $tokens['surface_subtle']
            ?? $defaultSecondaryBg;

        $secondarySurface = $tokens['surface_secondary']
            ?? $tokens['secondary_surface']
            ?? $tokens['surface_subtle']
            ?? $defaultSecondarySurface;

        $secondaryBorder = $tokens['border_secondary']
            ?? $tokens['secondary_border']
            ?? $tokens['border_subtle']
            ?? $defaultSecondaryBorder;

        // Standard snake_case and camelCase aliases
        $tokens['bg_secondary'] = $secondaryBg;
        $tokens['secondary_bg'] = $secondaryBg;
        $tokens['bgSecondary'] = $secondaryBg;
        $tokens['secondaryBg'] = $secondaryBg;

        $tokens['surface_secondary'] = $secondarySurface;
        $tokens['secondary_surface'] = $secondarySurface;
        $tokens['surfaceSecondary'] = $secondarySurface;
        $tokens['surface_subtle'] = $secondaryBg;
        $tokens['surfaceSubtle'] = $secondaryBg;

        $tokens['border_secondary'] = $secondaryBorder;
        $tokens['secondary_border'] = $secondaryBorder;
        $tokens['borderSecondary'] = $secondaryBorder;
        $tokens['secondaryBorder'] = $secondaryBorder;
        $tokens['border_subtle'] = $secondaryBorder;
        $tokens['borderSubtle'] = $secondaryBorder;

        return $tokens;
    }
}
