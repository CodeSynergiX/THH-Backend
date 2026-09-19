<?php

namespace App\Domains\Cases\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowTransition extends Model
{
    protected $fillable = [
        'from_status',
        'to_status',
        'allowed_roles',
        'requires_note',
        'requires_documents',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'requires_note' => 'boolean',
        'requires_documents' => 'boolean',
    ];

    public function allowsRole(string $role): bool
    {
        return in_array($role, $this->allowed_roles ?? [], true);
    }
}
