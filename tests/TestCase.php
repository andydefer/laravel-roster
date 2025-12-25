<?php

declare(strict_types=1);

namespace Tests;

use Roster\Models\Availability;
use Roster\Observers\EnforceDomainMutationObserver;
use Roster\Models\Schedule;
use Roster\Models\Impediment;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Roster\RosterServiceProvider;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Availability::observe(EnforceDomainMutationObserver::class);
        Schedule::observe(EnforceDomainMutationObserver::class);
        Impediment::observe(EnforceDomainMutationObserver::class);

        // Charger les migrations du package
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Charger les migrations de test
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Utiliser le cache en mémoire pour les tests
        Config::set('cache.default', 'array');
    }

    /**
     * Enregistre le ServiceProvider du package
     */
    protected function getPackageProviders($app): array
    {
        return [
            RosterServiceProvider::class,
        ];
    }

    /**
     * Configuration de l’environnement de test
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
