<?php

namespace App\Enums;

enum ExtendPeriod: string
{
    case OneMonth = 'one_month';

    case ThreeMonths = 'three_months';

    case SixMonths = 'six_months';

    case OneYear = 'one_year';

    case Custom = 'custom';

    public static function getLabels(): array
    {
        return [
            ExtendPeriod::OneMonth->value => '1 month',
            ExtendPeriod::ThreeMonths->value => '3 months',
            ExtendPeriod::SixMonths->value => '6 months',
            ExtendPeriod::OneYear->value => '1 year',
            ExtendPeriod::Custom->value => 'Custom',
        ];
    }

    public static function getMonths(?string $option = null): null|array|int
    {
        $options = [
            ExtendPeriod::OneMonth->value => 1,
            ExtendPeriod::ThreeMonths->value => 3,
            ExtendPeriod::SixMonths->value => 6,
            ExtendPeriod::OneYear->value => 12,
        ];

        if (filled($option)) {
            return $options[$option] ?? null;
        }

        return $options;
    }
}
