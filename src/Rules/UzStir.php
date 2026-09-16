<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class UzStir implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Format validation only: no undocumented INN checksum or registry claims.
        if (! is_string($value) || preg_match('/\A[0-9]{9}\z/', $value) !== 1) {
            $fail('uz-validations::validation.uz_stir')->translate();
        }
    }
}
