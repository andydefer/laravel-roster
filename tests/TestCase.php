<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Observers\EnforceDomainMutationObserver;
use Roster\RosterServiceProvider;

/**
 * Base test case for Roster package tests.
 *
 * Provides common setup for all package tests including:
 * - Observer registration for domain models
 * - Database migrations loading
 * - Test environment configuration
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerDomainObservers();
        $this->loadPackageMigrations();
        $this->loadTestMigrations();
        $this->configureMemoryCache();
    }

    /**
     * Register observers for domain models.
     *
     * @return void
     */
    private function registerDomainObservers(): void
    {
        Availability::observe(EnforceDomainMutationObserver::class);
        Schedule::observe(EnforceDomainMutationObserver::class);
        Impediment::observe(EnforceDomainMutationObserver::class);
    }

    /**
     * Load package migrations.
     *
     * @return void
     */
    private function loadPackageMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Load test-specific migrations.
     *
     * @return void
     */
    private function loadTestMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * Configure in-memory cache for tests.
     *
     * @return void
     */
    private function configureMemoryCache(): void
    {
        Config::set('cache.default', 'array');
    }

    /**
     * Get package service providers.
     *
     * @param mixed $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            RosterServiceProvider::class,
        ];
    }

    /**
     * Define the test environment configuration.
     *
     * @param mixed $app
     * @return void
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
}
