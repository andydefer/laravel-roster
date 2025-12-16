<?php

namespace Roster;

use Illuminate\Support\ServiceProvider;
use Roster\Commands\InstallRosterCommand;
use Roster\Services\ScheduleService;
use Roster\Services\AvailabilityService;

class RosterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publier la configuration
            $this->publishes([
                __DIR__ . '/../config/roster.php' => config_path('roster.php'),
            ], 'roster-config');

            // Publier les migrations
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'roster-migrations');

            // Publier les vues
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/roster'),
            ], 'roster-views');

            // Enregistrer les commandes
            $this->commands([
                InstallRosterCommand::class,
            ]);
        }

        // Charger les routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Charger les vues
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'roster');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/roster.php',
            'roster'
        );

        // Enregistrer les services
        $this->app->singleton('roster.schedule', function ($app) {
            return new ScheduleService();
        });

        $this->app->singleton('roster.availability', function ($app) {
            return new AvailabilityService();
        });

        // Enregistrer le manager
        $this->app->singleton('roster', function ($app) {
            return new \Roster\RosterManager(
                $app->make('roster.schedule'),
                $app->make('roster.availability')
            );
        });
    }
}
