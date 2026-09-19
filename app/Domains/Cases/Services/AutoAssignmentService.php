<?php

namespace App\Domains\Cases\Services;

use App\Domains\Cases\Models\Application;
use App\Models\User;

class AutoAssignmentService
{
    /**
     * Auto-assign application to the staff member with least workload in the case district.
     */
    public function assignBestStaff(Application $application): ?User
    {
        $districtId = $application->village?->taluka?->district_id;

        $query = User::role('staff')
            ->where('is_active', true)
            ->withCount(['assignedApplications' => function ($q) {
                $q->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED]);
            }]);

        if ($districtId) {
            $query->where('district_id', $districtId);
        }

        /** @var User|null $bestStaff */
        $bestStaff = $query->orderBy('assigned_applications_count', 'asc')->first();

        return $bestStaff;
    }
}
