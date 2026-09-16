<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations;

use Illuminate\Support\ServiceProvider;

final class UzValidationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'uz-validations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/uz-validations'),
            ], 'uz-validations-translations');
        }
    }
}
