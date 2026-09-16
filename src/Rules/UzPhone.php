<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class UzPhone implements ValidationRule
{
    /** Assigned national destination codes; see docs/validation-spec.md. */
    public const PREFIXES = [
        '20', '33', '50', '55', '61', '62', '65', '66', '67', '69',
        '70', '71', '72', '73', '74', '75', '76', '77', '78', '79',
        '88', '90', '91', '93', '94', '95', '97', '98', '99',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)
            || preg_match('/\A\+?998([0-9]{2})[0-9]{7}\z/', $value, $matches) !== 1
            || ! in_array($matches[1], self::PREFIXES, true)) {
            $fail('uz-validations::validation.uz_phone')->translate();
        }
    }
}
