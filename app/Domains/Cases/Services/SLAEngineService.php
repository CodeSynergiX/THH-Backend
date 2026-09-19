<?php

namespace App\Domains\Cases\Services;

use App\Domains\Cases\Models\Application;
use Carbon\CarbonImmutable;

class SLAEngineService
{
    /**
     * Calculate SLA due date based on urgency and priority.
     */
    public function calculateDueDate(string $urgency, string $priority = 'medium'): CarbonImmutable
    {
        $now = CarbonImmutable::now();

        return match ($urgency) {
            'critical' => $now->addHours(24),
            'urgent' => $now->addHours(48),
            'low' => $now->addDays(10),
            default => match ($priority) {
                'critical' => $now->addHours(36),
                'high' => $now->addDays(3),
                'low' => $now->addDays(7),
                default => $now->addDays(5),
            },
        };
    }

    /**
     * Check if an application has breached SLA.
     */
    public function isBreached(Application $application): bool
    {
        if ($application->status === Application::STATUS_RESOLVED || $application->status === Application::STATUS_REJECTED) {
            return false;
        }

        return $application->sla_due_at !== null && $application->sla_due_at->isPast();
    }
}
