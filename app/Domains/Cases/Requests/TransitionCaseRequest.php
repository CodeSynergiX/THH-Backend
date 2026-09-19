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
        ];
    }
}
