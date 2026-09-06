<?php

namespace App\Support;

use App\Models\MUser;

class RoleAccess
{
    public const ROLE_HEAD = 'Head';
    public const ROLE_SUPERVISOR = 'Supervisor';
    public const ROLE_EMPLOYEE = 'Employee';
    public const ROLE_SUPERADMIN = 'Superadmin';

    /**
     * @return array<int, string>
     */
    public static function roles(): array
    {
        return [
            self::ROLE_HEAD,
            self::ROLE_SUPERVISOR,
            self::ROLE_EMPLOYEE,
            self::ROLE_SUPERADMIN,
        ];
    }

    public static function isHead(MUser $user): bool
    {
        return $user->txtRole === self::ROLE_HEAD;
    }

    public static function isSupervisor(MUser $user): bool
    {
        return $user->txtRole === self::ROLE_SUPERVISOR;
    }

    public static function isEmployee(MUser $user): bool
    {
        return $user->txtRole === self::ROLE_EMPLOYEE;
    }

    public static function isSuperadmin(MUser $user): bool
    {
        return $user->txtRole === self::ROLE_SUPERADMIN;
    }

    public static function can(MUser $user, string $ability): bool
    {
        if (self::isSuperadmin($user)) {
            return true;
        }

        return match ($ability) {
            'dashboard' => true,
            'projects' => true,
            'crud-projects' => true, // Employees can CRUD projects they handle
            'adhocs' => true,
            'adhoc' => true,
            'exposure' => true,
            'reports' => true,
            'daily-tasks' => true,
            'daily-plans' => true,
            'monthly-report' => true,
            'master-data' => self::isSuperadmin($user) || self::isHead($user),
            'wa-scheduler' => self::isSuperadmin($user),
            default => false,
        };
    }
}
