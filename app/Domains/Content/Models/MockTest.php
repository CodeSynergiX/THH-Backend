<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'duration_minutes',
        'total_marks',
        'is_active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'total_marks' => 'integer',
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(MockTestQuestion::class);
    }
}
