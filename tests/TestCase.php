<?php

declare(strict_types=1);

namespace TursunboyevJahongir\UzValidations\Tests;

use Illuminate\Support\Carbon;
use Orchestra\Testbench\TestCase as Orchestra;
use TursunboyevJahongir\UzValidations\UzValidationsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [UzValidationsServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 9, 16, 12));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
