<?php

namespace App\Services\Fonnte;

class PhoneNormalizer
{
    public function normalize(string $number, ?string $countryCode = null): string
    {
        $countryCode = $countryCode ?? config('fonnte.default_country_code', '62');
        $digits = preg_replace('/\D/', '', $number) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return $countryCode.substr($digits, 1);
        }

        if (str_starts_with($digits, $countryCode)) {
            return $digits;
        }

        return $countryCode.$digits;
    }
}
