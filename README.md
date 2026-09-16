# Laravel Uzbekistan Validations

[![Tests](https://github.com/TursunboyevJahongir/laravel-uz-validations/actions/workflows/tests.yml/badge.svg)](https://github.com/TursunboyevJahongir/laravel-uz-validations/actions/workflows/tests.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)](composer.json)
[![Laravel](https://img.shields.io/badge/Laravel-10%20%7C%2011-FF2D20)](composer.json)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Reusable Laravel validation rules for applications serving Uzbekistan. Validate phone numbers, national personal identifiers (PINFL/JSHSHIR), passport and ID-card numbers, ordinary vehicle plates, and tax identifier formats, with messages in **Uzbek, Russian, and English**.

- Uses Laravel's modern `ValidationRule` contract and standard FormRequests.
- Checks PINFL dates and the officially documented checksum.
- Works offline without sending personal data to an external service.
- Discovers its service provider automatically; translations can be published and customized.

## Requirements

PHP 8.2+ and Laravel 10.x or 11.x. CI exercises PHP 8.2 and 8.3 against both Laravel versions.

**Framework lifecycle:** Laravel 10 and 11 reached the end of upstream security support on February 4, 2025 and March 12, 2026, respectively. This package provides the requested compatibility, but does not make an unsupported application secure. See [Laravel's support policy](https://laravel.com/docs/11.x/releases#support-policy) and [SECURITY.md](SECURITY.md). Later Laravel majors are not yet declared supported by this release.

## Installation

Install from the public Git repository using Composer:

```bash
composer config repositories.uz-validations vcs https://github.com/TursunboyevJahongir/laravel-uz-validations
composer require tursunboyevjahongir/laravel-uz-validations:^1.0
```

The VCS configuration makes installation work independently of Packagist indexing. If the package is listed on Packagist, only the `composer require` command is needed. A GitHub release alone does not register a package on Packagist.

Laravel automatically loads `UzValidationsServiceProvider`. If you have disabled package discovery, register `TursunboyevJahongir\UzValidations\UzValidationsServiceProvider::class` manually in your application's providers list.

## FormRequest example

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use TursunboyevJahongir\UzValidations\Rules\UzCarNumber;
use TursunboyevJahongir\UzValidations\Rules\UzPassport;
use TursunboyevJahongir\UzValidations\Rules\UzPhone;
use TursunboyevJahongir\UzValidations\Rules\UzPinfl;
use TursunboyevJahongir\UzValidations\Rules\UzStir;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // Adapt to your application's policy.
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', new UzPhone],
            'pinfl' => ['required', new UzPinfl],
            'passport' => ['required', new UzPassport],
            'car_number' => ['nullable', new UzCarNumber],
            'stir' => ['nullable', new UzStir],
        ];
    }
}
```

Or use the validator directly:

```php
use Illuminate\Support\Facades\Validator;
use TursunboyevJahongir\UzValidations\Rules\UzPhone;

$validated = Validator::make(
    ['phone' => '+998901234567'],
    ['phone' => ['required', new UzPhone]],
)->validate();
```

## Rules and accepted formats

| Rule | Accepted examples | Checks |
| --- | --- | --- |
| `UzPhone` | `+998901234567`, `998901234567` | Country code, nine national digits, allocated two-digit prefix |
| `UzPinfl` | `31210932040247` (published reference example) | 14 digits, century index 1–6, valid birth date, no future date, 7–3–1 checksum |
| `UzPassport` | `AA1234567`, `FA1234567`, `AD1234567` | Two uppercase ASCII letters and seven digits |
| `UzCarNumber` | `01 A 123 AA`, `01A123AA`, `01 123 AAA`, `01123AAA` | Region 01–99, ordinary individual or legal-entity format |
| `UzStir` | `123456789` (illustrative) | Exactly nine ASCII digits |

All values must be **strings**, including numeric identifiers in JSON. Integers, floats, booleans, arrays and objects are rejected. Keep leading zeros intact. Rules do not trim, uppercase, or rewrite submitted values. Laravel middleware may normalize request input before validation.

These are ordinary, non-implicit rules: Laravel skips them for missing fields and empty strings. Add `required` when a value must exist, or `nullable` when null is permitted. Use rule objects; string aliases such as `uz_phone` are not registered.

### Phone numbers

Supported prefixes:

```text
20 33 50 55 61 62 65 66 67 69 70 71 72 73 74
75 76 77 78 79 88 90 91 93 94 95 97 98 99
```

This includes mobile, fixed-line and SIP allocations, not only mobile operators. Spaces, parentheses, hyphens and national-only numbers are rejected. Prefix validation does not establish a current operator or prove that the subscriber exists. See the dated [source notes](docs/validation-spec.md) when updating the list.

### PINFL / JSHSHIR

The first digit encodes the birth century: 1–2 for 1800–1899, 3–4 for 1900–1999, and 5–6 for 2000–2099. Digits 2–7 contain `DDMMYY`. The final digit must equal the sum of the first 13 digits multiplied by repeating weights `7, 3, 1`, modulo 10. Calendar validation includes leap years; a future birth date is rejected using the application's current date/timezone.

The location and sequence fields are not looked up in a registry. A valid checksum is not proof of identity, citizenship, assignment, or ownership.

### Passports, plates and STIR

`UzPassport` checks the shared series/number **shape** and does not maintain an issuing-series whitelist or verify issuance. `UzCarNumber` accepts only the two ordinary formats above, with either no spaces or one ASCII space between every group. Diplomatic, military, trailer, temporary and personalized formats are outside this release's scope.

`UzStir` is **format-only**: all nine-digit strings, including leading-zero and all-zero strings, satisfy it. It does not check a checksum, tax registration, or entity type. Applications needing registry assurance must use an authorized registry integration separately. These rules must not replace identity verification or access controls.

## Localization

Set the application locale through your normal Laravel configuration or middleware:

```php
app()->setLocale('uz'); // 'ru' and 'en' are also bundled.
```

Example Uzbek message: `phone maydoniga 998 mamlakat kodi bilan to‘g‘ri O‘zbekiston telefon raqamini kiriting.`

Publish customizable translation files:

```bash
php artisan vendor:publish --tag=uz-validations-translations
```

Edit `lang/vendor/uz-validations/{uz,ru,en}/validation.php` (or the custom language directory configured by your application). Keys are namespaced, for example `uz-validations::validation.uz_phone`. Laravel replaces `:attribute`, respects custom attribute names, and uses the configured fallback locale. Add another locale by publishing a file under its locale code.

## Development and contribution

```bash
git clone https://github.com/TursunboyevJahongir/laravel-uz-validations.git
cd laravel-uz-validations
composer install
composer test
composer format:check
```

If Composer blocks the end-of-life framework dependencies, follow the **isolated compatibility testing** instructions in [CONTRIBUTING.md](CONTRIBUTING.md). Do not disable security blocking in a production application's Composer configuration.

Tests cover reference identifiers, malformed input and PHP types, every supported phone prefix and plate region, century/leap-year boundaries, single-digit PINFL corruption, Laravel required/nullable behavior, translated errors, translation overrides, and HTTP FormRequest success/422 responses.

With Xdebug or PCOV enabled, run `composer test:coverage`. The GitHub Actions matrix records coverage, resolved dependency versions, and a separate dependency audit. A passing compatibility build does not mean a clean dependency security audit.

Contributions are welcome. Report reproducible bugs through [GitHub Issues](https://github.com/TursunboyevJahongir/laravel-uz-validations/issues), using synthetic data. Include an official source and regression tests when changing a national format or allocation. See [CONTRIBUTING.md](CONTRIBUTING.md).

## License and author

[MIT](LICENSE) © 2026 [Jahongir Tursunboyev](https://github.com/TursunboyevJahongir).
