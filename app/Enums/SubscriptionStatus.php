<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle status of a customer subscription.
 */
enum SubscriptionStatus: string
{
    /**
     * Human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::EXPIRED => 'Expired',
        };
    }

    /**
     * Bootstrap badge colour.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACTIVE => 'success',
            self::SUSPENDED => 'danger',
            self::EXPIRED => 'secondary',
        };
    }
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';
}
