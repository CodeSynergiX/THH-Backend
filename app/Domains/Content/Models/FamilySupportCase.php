<?php

namespace App\Domains\Content\Models;

use App\Domains\Cases\Models\Application;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilySupportCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'title',
        'beneficiary_name',
        'description',
        'amount_required',
        'amount_funded',
        'status',
    ];

    protected $casts = [
        'amount_required' => 'decimal:2',
        'amount_funded' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'case_id');
    }
}
