<?php

namespace App\Domains\Users\Permissions;

class PermissionCatalog
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'dashboard.view',
            'cases.view',
            'cases.manage',
            'cases.assign',
            'people.view',
            'people.manage',
            'roles.manage',
            'content.view',
            'content.manage',
            'modules.manage',
            'cms.manage',
            'theme.manage',
            'settings.manage',
            'notifications.manage',
            'audit.view',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function roleGrants(): array
    {
        $all = self::all();

        return [
            'super_admin' => $all,
            'admin' => $all,
            'collector' => [
                'dashboard.view',
                'cases.view',
                'cases.manage',
                'cases.assign',
            ],
            'staff' => [
                'dashboard.view',
                'cases.view',
                'cases.manage',
                'cases.assign',
            ],
            'mentor' => [
                'cases.view',
                'cases.manage',
            ],
            'volunteer' => [
                'cases.view',
                'cases.manage',
            ],
            'partner' => [
                'cases.view',
            ],
            'citizen' => [],
        ];
    }
}
