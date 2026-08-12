<?php

namespace App\Enums;

enum BillingCycle: string
{
    case OneTime = 'one_time';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    /**
     * Get all of the values backing the enum.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the human readable label for the billing cycle.
     */
    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One Time',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Yearly => 'Yearly',
        };
    }

    /**
     * Get the number of billing occurrences within a single year.
     */
    public function occurrencesPerYear(): int
    {
        return match ($this) {
            self::OneTime => 1,
            self::Monthly => 12,
            self::Quarterly => 4,
            self::Yearly => 1,
        };
    }
}
