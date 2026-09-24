<?php

namespace App\Domains\Settings\Resources;

use App\Domains\Settings\Branding;
use App\Domains\Settings\Models\ThemeVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ThemeVersion
 */
class ThemeVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'version' => $this->version,
            'light' => $this->normalizeTokens($this->light, false),
            'dark' => $this->normalizeTokens($this->dark, true),
            'meta' => $this->meta,
            'branding' => Branding::payload($request->query('locale', 'gu')),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }

    /**
     * Ensure inner content tokens (secondary bg, secondary border, etc.) are always present.
     *
     * @param  array<string, string>|null  $tokens
     * @return array<string, string>
     */
    protected function normalizeTokens(?array $tokens, bool $isDark): array
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

        // Standard snake_case and camelCase aliases so mobile app can consume either convention
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
