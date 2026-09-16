# Contributing

Open an issue or pull request with a minimal reproduction and synthetic values. Do not post real passport numbers, PINFLs or other private identifiers.

## Setup and checks

Use PHP 8.2 or 8.3 and Composer 2. Install dependencies, run `composer test`, then `composer format:check`. Use `composer format` to apply Laravel Pint formatting. Run `composer validate --strict` before submitting changes.

### End-of-life compatibility dependencies

Laravel 10/11 are required compatibility targets, but upstream support has ended and current Composer advisory policy can block the test framework. In an isolated development checkout with no production data, use:

```bash
composer update --prefer-dist --no-interaction --no-security-blocking
composer test
composer audit
```

This command-level exception is for the test harness only. It is not saved in `composer.json` and does not alter global Composer settings. Audit findings remain visible. Never copy the exception into production deployment procedures.

To test a particular Laravel version:

```bash
composer update --with 'orchestra/testbench:^8.0' --no-security-blocking
composer test
# Laravel 11:
composer update --with 'orchestra/testbench:^9.0' --no-security-blocking
composer test
```

The library intentionally does not commit `composer.lock`: consumers resolve their own versions and CI tests the latest versions within each target. CI uploads its exact resolved lockfile with the test reports.

## Changes

- Keep PHP 8.2 compatibility and use the `ValidationRule` contract.
- Add focused tests for changed behavior, including invalid inputs.
- Cite primary sources when changing PINFL logic, phone prefixes, or registration formats.
- Keep translations in `en`, `uz` and `ru` synchronized.
- Describe breaking changes explicitly. The package follows semantic versioning.
- Use conventional commit subjects such as `fix: reject invalid PINFL dates`.

By submitting a contribution, you agree that your contribution is distributed under the project's MIT license.
