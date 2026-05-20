<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeZone;

class TimezoneCatalog
{
    public static function preferred(): array 
    {
        return [
            'America/Mexico_City',
            'America/Tijuana',
            'America/Cancun',
            'America/Bogota',
            'America/Lima',
            'America/Santiago',
            'America/Argentina/Buenos_Aires',
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Los_Angeles',
            'Europe/Madrid',
            'Europe/London',
            'Europe/Paris',
            'UTC',
            'Asia/Tokyo',
            'Asia/Seoul',
            'Asia/Shanghai',
            'Asia/Dubai',
            'Australia/Sydney',
        ];
    }

    public static function identifiers(): array
    {
        $all = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        $preferred = self::preferred();
        $remaining = array_values(array_diff($all, $preferred));

        sort($remaining, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values(array_unique(array_merge($preferred, $remaining)));
    }

    public static function options(): array
    {
        $nowUtc = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $options = [];

        foreach (self::identifiers() as $timezone) {
            $zone = new DateTimeZone($timezone);
            $offsetSeconds = $zone->getOffset($nowUtc);

            $sign = $offsetSeconds < 0 ? '-' : '+';
            $absoluteOffset = abs($offsetSeconds);
            $hours = floor($absoluteOffset / 3600);
            $minutes = floor(($absoluteOffset % 3600) / 60);

            $options[] = [
                'value' => $timezone,
                'label' => sprintf(
                    '%s (UTC%s%02d:%02d)',
                    $timezone,
                    $sign,
                    $hours,
                    $minutes
                ),
            ];
        }
        
        return $options;
    }

    public static function isValid(?string $timezone): bool
    {
        if (!$timezone) {
            return false;
        }

        return in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL), true);
    }
}