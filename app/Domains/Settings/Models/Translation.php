<?php

namespace App\Domains\Settings\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends Model
{
    protected $fillable = [
        'group',
        'key',
        'locale',
        'value',
        'needs_review',
        'updated_by',
    ];

    protected $casts = [
        'needs_review' => 'boolean',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a flat array of translations for a locale: ['group.key' => 'value', ...]
     */
    public static function getFlatDictionary(string $locale): array
    {
        return self::where('locale', $locale)
            ->get()
            ->mapWithKeys(function ($item) {
                return ["{$item->group}.{$item->key}" => $item->value];
            })
            ->all();
    }
}
