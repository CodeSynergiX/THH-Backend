<?php

namespace App\Domains\Users\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'age' => $this->age,
            'occupation' => $this->occupation,
            'education' => $this->education,
            'income_category' => $this->income_category,
            'community' => $this->community,
            'locale' => $this->locale ?? 'gu',
            'theme_preference' => $this->theme_preference ?? 'system',
            'district_id' => $this->district_id,
            'taluka_id' => $this->taluka_id,
            'village_id' => $this->village_id,
            'district' => $this->district ? [
                'id' => $this->district->id,
                'name_en' => $this->district->name_en,
                'name_gu' => $this->district->name_gu,
            ] : null,
            'village' => $this->village ? [
                'id' => $this->village->id,
                'name_en' => $this->village->name_en,
                'name_gu' => $this->village->name_gu,
            ] : null,
            'roles' => $this->getRoleNames(),
            'scope' => $this->getEffectiveScope(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
