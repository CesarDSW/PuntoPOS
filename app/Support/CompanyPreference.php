<?php

namespace App\Support;

use App\Models\Company;
use App\Models\CompanySettings;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class CompanyPreference
{
    public static function settings(?int $companyId): ?CompanySettings
    {
        if (!$companyId){
            return null;
        }

        return CompanySettings::firstOrCreate(
            ['company_idfk' => $companyId],
            [
                'notify_low_stock' => true,
                'notify_sale_cancelled' => true,
                'notify_out_of_stock' => true,
                'timezone' => 'America/Mexico_City',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'auto_print' => true,
                'show_taxes' => true,
                'printer_width' => '80mm',
                'price_decimals' => '2',
            ]
        );
    }

    public static function company(?int $companyId): ?Company 
    {
       return $companyId ? Company::find($companyId) : null;
    }

    public static function timezone(?int $companyId): string 
    {
        return self::settings($companyId)?->timezone ?: 'America/Mexico_City';
    }

    public static function dateFormat(?int $companyId): string
    {
        return self::settings($companyId)?->date_format ?: 'd/m/Y';
    }

    public static function timeFormat(?int $companyId): string 
    {
        return self::settings($companyId)?->time_format ?: 'H:i';
    }

    public static function decimals(?int $companyId): int 
    {
        $value = (string) (self::settings($companyId)?->price_decimals ?? '2');
        return in_array($value, ['0', '2'], true) ? (int) $value : 2;
    }

    public static function currency(?int $companyId): string 
    {
        $value = strtoupper((string) (self::company($companyId)?->currency ?? 'MXN'));
        return in_array($value, ['MXN', 'USD'], true) ? $value : 'MXN';
    }

    public static function formatMoneyForCompany(?int $companyId, float|int|string|null $amount): string 
    {
        $number = (float) ($amount ?? 0);
        $currency = self::currency($companyId);
        $decimals = self::decimals($companyId);

        $symbol = $currency === 'USD' ? 'US$' : 'MX$';

        return $symbol . number_format($number, $decimals, '.', ',');
    }

    public static function formatDateForCompany(?int $companyId, CarbonInterface|string|null $value): ?string 
    {
        if(!$value) {
            return null;
        }    

        $date = $value instanceof CarbonInterface ? $value->copy() : Carbon::parse($value);

        return $date
            ->timezone(self::timezone($companyId))
            ->format(self::dateFormat($companyId));
    }

    public static function formatTimeForCompany(?int $companyId, CarbonInterface|string|null $value): ?string 
    {
        if(!$value) {
            return null;
        }

        $date = $value instanceof CarbonInterface ? $value->copy() : Carbon::parse($value);

        return $date
            ->timezone(self::timezone($companyId))
            ->format(self::timeFormat($companyId));
    }

    public static function formatDateTimeForCompany(?int $companyId, CarbonInterface|string|null $value): ?string 
    {
        if(!$value) {
            return null;
        }

        $date = $value instanceof CarbonInterface ? $value->copy() : Carbon::parse($value);

        return $date
            ->timezone(self::timezone($companyId))
            ->format(self::dateFormat($companyId). ' ' . self::timeFormat($companyId));
    }

    public static function all(?int $companyId): array 
    {
        return [
            'timezone' => self::timezone($companyId),
            'date_format' => self::dateFormat($companyId),
            'time_format' => self::timeFormat($companyId),
            'price_decimals' => self::decimals($companyId),
            'currency' => self::currency($companyId),
        ];
    }
}