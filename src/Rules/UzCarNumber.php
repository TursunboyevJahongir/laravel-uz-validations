<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class UzCarNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Full regional range 01-99; either compact or consistently spaced groups.
        $pattern = '/\A(?:0[1-9]|[1-9][0-9])(?:[A-Z][0-9]{3}[A-Z]{2}| [A-Z] [0-9]{3} [A-Z]{2}|[0-9]{3}[A-Z]{3}| [0-9]{3} [A-Z]{3})\z/';

        if (! is_string($value) || preg_match($pattern, $value) !== 1) {
            $fail('uz-validations::validation.uz_car_number')->translate();
        }
    }
}
