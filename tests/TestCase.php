<?php

namespace Roster\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Roster\RosterServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Optionnel : créer une migration pour la table de test
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    protected function getPackageProviders($app)
    {
        return [
            RosterServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configuration de la base de données de test
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
