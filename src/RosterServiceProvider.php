<?php

declare(strict_types=1);

namespace Roster;

use Illuminate\Support\ServiceProvider;
use Roster\Commands\InstallRosterCommand;
use Roster\Repositories\AvailabilityRepository;
use Roster\Services\AvailabilityService;
use Roster\Services\AvailabilityValidator;
use Roster\Services\Core\SlotFinderService;
use Roster\Services\Core\ValidationService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;

class RosterServiceProvider extends ServiceProvider
{
    public function boot(): void
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

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/roster.php',
            'roster'
        );

        $this->registerCoreServices();
        $this->registerMainServices();
        $this->registerManager();
    }

    /**
     * Enregistrer les services de base réutilisables.
     */
    protected function registerCoreServices(): void
    {
        $this->app->singleton(ValidationService::class);
        $this->app->singleton(AvailabilityRepository::class);
        $this->app->singleton(SlotFinderService::class);
        $this->app->singleton(AvailabilityValidator::class);
    }

    /**
     * Enregistrer les services principaux avec leurs dépendances.
     */
    protected function registerMainServices(): void
    {
        // Schedule Service - PLUS de dépendance à SlotFinderService
        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService(
                $app->make(ValidationService::class),
                $app->make(AvailabilityRepository::class)
            );
        });

        // Availability Service
        $this->app->singleton('roster.availability', function ($app): AvailabilityService {
            return new AvailabilityService(
                $app->make(AvailabilityValidator::class),
                $app->make(ValidationService::class),
                $app->make(AvailabilityRepository::class)
            );
        });

        // Impediment Service
        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService(
                $app->make(ValidationService::class),
                $app->make(AvailabilityRepository::class)
            );
        });

        // SlotFinder Service - SIMPLIFIÉ
        $this->app->singleton(SlotFinderService::class, function ($app): SlotFinderService {
            return new SlotFinderService($app->make(ValidationService::class));
        });

        // Bindings de compatibilité pour l'injection de type
        $this->app->alias('roster.schedule', ScheduleService::class);
        $this->app->alias('roster.availability', AvailabilityService::class);
        $this->app->alias('roster.impediment', ImpedimentService::class);
    }

    /**
     * Enregistrer le manager principal.
     */
    protected function registerManager(): void
    {
        $this->app->singleton('roster', function ($app): RosterManager {
            return new RosterManager(
                $app->make('roster.schedule'),
                $app->make('roster.availability'),
                $app->make('roster.impediment')
            );
        });

        // Alias pour compatibilité
        $this->app->alias('roster', RosterManager::class);
    }
}
