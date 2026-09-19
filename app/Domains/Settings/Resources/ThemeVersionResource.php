<?php

namespace App\Domains\Settings\Resources;

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
            'light' => $this->light,
            'dark' => $this->dark,
            'meta' => $this->meta,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
