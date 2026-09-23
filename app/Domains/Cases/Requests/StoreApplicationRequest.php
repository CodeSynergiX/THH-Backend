<?php

namespace App\Domains\Cases\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required_without:module', 'nullable', 'exists:categories,id'],
            'module' => ['required_without:category_id', 'nullable', 'string', 'max:80'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'urgency' => ['nullable', 'in:low,medium,urgent,normal,critical,emergency'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_helper_mode' => ['nullable', 'boolean'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'beneficiary_phone' => ['nullable', 'string', 'max:20'],
            'email' => [$this->user() ? 'nullable' : 'required', 'email', 'max:255'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'taluka_id' => ['nullable', 'exists:talukas,id'],
            'documents' => ['nullable', 'array'],
            'documents.*.type' => ['nullable', 'string', 'max:100'],
            'documents.*.path' => ['nullable', 'string', 'max:500'],
            'documents.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
