<?php

namespace App\Domains\Cases\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransitionCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'note' => ['nullable', 'string'],
            'visibility' => ['nullable', 'in:public,internal'],
            'proof_photo' => ['nullable', 'string', 'max:500'],
            'urgency' => ['nullable', 'in:low,medium,urgent,normal,critical,emergency'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
