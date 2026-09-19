<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'is_published',
        'eligibility_rules',
        'benefits',
        'required_documents',
        'process_steps',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'eligibility_rules' => 'array',
        'benefits' => 'array',
        'required_documents' => 'array',
        'process_steps' => 'array',
    ];

    /**
     * Match user profile against this scheme's eligibility rules
     */
    public function matchesUser(array $userProfile): bool
    {
        $rules = $this->eligibility_rules ?? [];

        if (isset($rules['min_age']) && ($userProfile['age'] ?? 0) < $rules['min_age']) {
            return false;
        }

        if (isset($rules['max_age']) && ($userProfile['age'] ?? 0) > $rules['max_age']) {
            return false;
        }

        if (isset($rules['gender']) && $rules['gender'] !== 'any' && ($userProfile['gender'] ?? '') !== $rules['gender']) {
            return false;
        }

        if (isset($rules['income_category']) && ! empty($rules['income_category'])) {
            $allowedIncome = (array) $rules['income_category'];
            if (! in_array($userProfile['income_category'] ?? '', $allowedIncome, true)) {
                return false;
            }
        }

        if (isset($rules['community']) && ! empty($rules['community'])) {
            $allowedCommunities = (array) $rules['community'];
            if (! in_array($userProfile['community'] ?? '', $allowedCommunities, true)) {
                return false;
            }
        }

        return true;
    }
}
