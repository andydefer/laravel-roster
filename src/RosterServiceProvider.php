<?php

declare(strict_types=1);

// ==== src/RosterServiceProvider.php ====

namespace Roster;

use Illuminate\Support\ServiceProvider;
use Roster\Commands\InstallRosterCommand;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;

class RosterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publier la configuration
            $this->publishes([
                __DIR__.'/../config/roster.php' => config_path('roster.php'),
            ], 'roster-config');

            // Publier les migrations
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'roster-migrations');

            // Publier les vues
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/roster'),
            ], 'roster-views');

            // Enregistrer les commandes
            $this->commands([
                InstallRosterCommand::class,
            ]);
        }

        // Charger les routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Charger les vues
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'roster');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/roster.php',
            'roster'
        );

        // Enregistrer les services
        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService;
        });

        $this->app->singleton('roster.availability', function ($app): AvailabilityService {
            return new AvailabilityService;
        });

        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService;
        });

        // Enregistrer le manager
        $this->app->singleton('roster', function ($app): RosterManager {
            return new RosterManager(
                $app->make('roster.schedule'),
                $app->make('roster.availability'),
                $app->make('roster.impediment')
            );
        });
    }
}
