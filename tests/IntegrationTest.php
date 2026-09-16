<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Tests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use TursunboyevJahongir\UzValidations\Rules\UzCarNumber;
use TursunboyevJahongir\UzValidations\Rules\UzPassport;
use TursunboyevJahongir\UzValidations\Rules\UzPhone;
use TursunboyevJahongir\UzValidations\Rules\UzPinfl;
use TursunboyevJahongir\UzValidations\Rules\UzStir;
use TursunboyevJahongir\UzValidations\UzValidationsServiceProvider;

final class IntegrationTest extends TestCase
{
    public function test_all_rules_translate_and_replace_attribute_names_in_all_locales(): void
    {
        $rules = ['uz_phone' => UzPhone::class, 'uz_pinfl' => UzPinfl::class, 'uz_passport' => UzPassport::class, 'uz_car_number' => UzCarNumber::class, 'uz_stir' => UzStir::class];
        foreach (['en', 'uz', 'ru'] as $locale) {
            $this->app->setLocale($locale);
            $messages = require __DIR__.'/../resources/lang/'.$locale.'/validation.php';
            foreach ($rules as $key => $class) {
                $validator = Validator::make(['value' => 'invalid'], ['value' => [new $class]], [], ['value' => 'Test attribute']);
                $this->assertSame(str_replace(':attribute', 'Test attribute', $messages[$key]), $validator->errors()->first('value'));
            }
        }
    }

    public function test_unsupported_locale_falls_back_to_english(): void
    {
        $this->app->setLocale('de');
        $validator = Validator::make(['phone' => 'invalid'], ['phone' => [new UzPhone]]);
        $this->assertSame('The phone must be a valid Uzbekistan phone number with country code 998.', $validator->errors()->first('phone'));
    }

    public function test_published_translation_can_override_package_message(): void
    {
        $path = $this->app->langPath('vendor/uz-validations/en');
        $this->app['files']->ensureDirectoryExists($path);
        $file = $path.'/validation.php';
        $original = is_file($file) ? file_get_contents($file) : null;
        try {
            file_put_contents($file, "<?php return ['uz_phone' => 'Custom message for :attribute.'];");
            $this->assertSame('Custom message for phone.', Validator::make(['phone' => 'invalid'], ['phone' => [new UzPhone]])->errors()->first('phone'));
        } finally {
            $original === null ? unlink($file) : file_put_contents($file, $original);
        }
    }

    public function test_provider_registers_translation_publishing_and_discovery_metadata(): void
    {
        $paths = ServiceProvider::pathsToPublish(UzValidationsServiceProvider::class, 'uz-validations-translations');
        $this->assertContains($this->app->langPath('vendor/uz-validations'), $paths);
        $this->assertDirectoryExists(array_key_first($paths));
        $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([UzValidationsServiceProvider::class], $composer['extra']['laravel']['providers']);
    }

    public function test_form_request_returns_success_for_valid_data(): void
    {
        Route::post('/test-profile', fn (ProfileRequest $request) => response()->json($request->validated()));
        $data = ['phone' => '+998901234567', 'pinfl' => '31210932040247', 'passport' => 'FA1234567', 'car_number' => '01 A 123 AA', 'stir' => '123456789'];
        $this->postJson('/test-profile', $data)->assertOk()->assertExactJson($data);
    }

    public function test_form_request_returns_localized_422_for_all_invalid_fields(): void
    {
        $this->app->setLocale('uz');
        Route::post('/test-profile', fn (ProfileRequest $request) => response()->json($request->validated()));
        $data = array_fill_keys(['phone', 'pinfl', 'passport', 'car_number', 'stir'], 'invalid');
        $this->postJson('/test-profile', $data)->assertUnprocessable()->assertJsonValidationErrors(array_keys($data))
            ->assertJsonPath('errors.pinfl.0', 'pinfl maydoniga to‘g‘ri 14 xonali JSHSHIR kiriting.');
    }
}

final class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', new UzPhone],
            'pinfl' => ['required', new UzPinfl],
            'passport' => ['required', new UzPassport],
            'car_number' => ['required', new UzCarNumber],
            'stir' => ['required', new UzStir],
        ];
    }
}
