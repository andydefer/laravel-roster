<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Roster\RosterServiceProvider;

/**
 * Base test case for Roster package testing.
 *
 * Provides common setup for all package tests including:
 * - Database migrations loading
 * - Service provider registration
 * - Test environment configuration
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();



        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Get the package service providers.
     *
     * @param mixed $app Laravel application instance
     * @return array<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            RosterServiceProvider::class,
        ];
    }

    /**
     * Define environment setup for testing.
     *
     * @param mixed $app Laravel application instance
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Define database migrations for testing.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
