<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class UzPassport implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/\A[A-Z]{2}[0-9]{7}\z/', $value) !== 1) {
            $fail('uz-validations::validation.uz_passport')->translate();
        }
    }
}
