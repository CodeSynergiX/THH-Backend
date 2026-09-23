<?php

namespace App\Domains\Cases;

class Urgency
{
    public const LOW = 'low';

    public const MEDIUM = 'medium';

    public const URGENT = 'urgent';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [self::LOW, self::MEDIUM, self::URGENT];
    }

    public static function normalize(?string $value): string
    {
        return match ($value) {
            'low' => self::LOW,
            'medium', 'normal' => self::MEDIUM,
            'urgent', 'critical', 'emergency', 'high' => self::URGENT,
            default => self::MEDIUM,
        };
    }

    public static function toPriority(string $urgency): string
    {
        return match (self::normalize($urgency)) {
            self::URGENT => 'high',
            self::LOW => 'low',
            default => 'medium',
        };
    }
}
