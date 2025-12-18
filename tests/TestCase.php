<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Roster\RosterServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing']);

        // Register package service provider for testing
        $this->app->register(RosterServiceProvider::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            RosterServiceProvider::class,
        ];
    }
}
