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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'age' => $this->age,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'blood_group' => $this->blood_group,
            'address' => $this->address,
            'pincode' => $this->pincode,
            'ration_card_no' => $this->ration_card_no,
            'avatar_url' => $this->avatar_url,
            'blood_donor_active' => (bool) ($this->blood_donor_active ?? true),
            'sms_alerts_active' => (bool) ($this->sms_alerts_active ?? true),
            'occupation' => $this->occupation,
            'education' => $this->education,
            'income_category' => $this->income_category,
            'community' => $this->community,
            'locale' => $this->locale ?? 'gu',
            'theme_preference' => $this->theme_preference ?? 'system',
            'is_active' => (bool) $this->is_active,
            'helper_status' => $this->helper_status,
            'on_duty' => (bool) $this->on_duty,
            'district_id' => $this->district_id,
            'taluka_id' => $this->taluka_id,
            'village_id' => $this->village_id,
            'district' => $this->district ? [
                'id' => $this->district->id,
                'name_en' => $this->district->name_en,
                'name_gu' => $this->district->name_gu,
            ] : null,
            'taluka' => $this->taluka ? [
                'id' => $this->taluka->id,
                'name_en' => $this->taluka->name_en,
                'name_gu' => $this->taluka->name_gu,
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
