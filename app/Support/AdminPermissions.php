<?php

namespace App\Support;

class AdminPermissions
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        $all = self::allPermissions();

        return [
            'ADMIN' => $all,
            'MANAGER' => [
                'dashboard.view',
                'reports.view',
                'catalog.manage',
                'showtimes.manage',
                'bookings.manage',
                'payments.manage',
                'refunds.manage',
                'tickets.checkin',
                'fnb.manage',
                'marketing.manage',
                'customers.manage',
                'staff.manage',
                'operations.manage',
            ],
            'TICKET_COUNTER' => [
                'dashboard.view',
                'bookings.manage',
                'payments.manage',
                'refunds.manage',
                'customers.manage',
            ],
            'TICKET_CHECKIN' => [
                'dashboard.view',
                'tickets.checkin',
            ],
            'FNB' => [
                'dashboard.view',
                'fnb.manage',
            ],
            'TECHNICIAN' => [
                'dashboard.view',
                'operations.manage',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function allPermissions(): array
    {
        return [
            'dashboard.view',
            'reports.view',
            'catalog.manage',
            'showtimes.manage',
            'bookings.manage',
            'payments.manage',
            'refunds.manage',
            'tickets.checkin',
            'fnb.manage',
            'marketing.manage',
            'customers.manage',
            'staff.manage',
            'operations.manage',
            'admin_users.manage',
        ];
    }

    /**
     * @param  array<int, string>  $roleCodes
     * @return array<int, string>
     */
    public static function permissionsForRoles(array $roleCodes): array
    {
        $granted = [];

        foreach ($roleCodes as $roleCode) {
            foreach (self::rolePermissions()[$roleCode] ?? [] as $permission) {
                $granted[$permission] = $permission;
            }
        }

        return array_values($granted);
    }

    /**
     * @param  array<int, string>  $roleCodes
     */
    public static function hasPermission(array $roleCodes, string $permission): bool
    {
        if (in_array('ADMIN', $roleCodes, true)) {
            return true;
        }

        return in_array($permission, self::permissionsForRoles($roleCodes), true);
    }
}
