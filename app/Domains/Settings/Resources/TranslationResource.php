<?php

namespace App\Domains\Settings\Resources;

use App\Domains\Settings\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Translation
 */
class TranslationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group' => $this->group,
            'key' => $this->key,
            'locale' => $this->locale,
            'value' => $this->value,
            'needs_review' => $this->needs_review,
        ];
    }
}
