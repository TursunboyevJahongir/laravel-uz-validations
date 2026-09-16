<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

final class UzPinfl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/\A[1-6][0-9]{13}\z/', $value) !== 1) {
            $fail('uz-validations::validation.uz_pinfl')->translate();

            return;
        }

        $day = (int) substr($value, 1, 2);
        $month = (int) substr($value, 3, 2);
        $year = 1800 + intdiv((int) $value[0] - 1, 2) * 100 + (int) substr($value, 5, 2);

        if (! checkdate($month, $day, $year)
            || sprintf('%04d-%02d-%02d', $year, $month, $day) > Carbon::today()->format('Y-m-d')) {
            $fail('uz-validations::validation.uz_pinfl')->translate();

            return;
        }

        // Cabinet Resolution No. 177 (12 April 2022): repeating 7, 3, 1 weights.
        $sum = 0;
        $weights = [7, 3, 1];

        for ($index = 0; $index < 13; $index++) {
            $sum += (int) $value[$index] * $weights[$index % 3];
        }

        if ($sum % 10 !== (int) $value[13]) {
            $fail('uz-validations::validation.uz_pinfl')->translate();
        }
    }
}
