<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Application user roles for the ISP billing system.
 *
 * Hierarchy (highest privilege first):
 *   - super_admin : full control, including operator/role management
 *   - admin       : operational management, below super admin
 *   - operator    : day-to-day helpdesk / voucher operations
 */
enum UserRole: string
{
    /**
     * Human-readable label for UI display.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::OPERATOR => 'Operator',
        };
    }

    /**
     * All defined role values.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case OPERATOR = 'operator';
}
