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
            $this->publishes([
                __DIR__ . '/../config/roster.php' => config_path('roster.php'),
            ], 'roster-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'roster-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/roster'),
            ], 'roster-views');

            $this->commands([
                InstallRosterCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'roster');

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
     * Register reusable core services.
     */
    protected function registerCoreServices(): void
    {
        $this->app->singleton(ValidationService::class);
        $this->app->singleton(AvailabilityRepository::class);
        $this->app->singleton(SlotFinderService::class);
        $this->app->singleton(AvailabilityValidator::class);
    }

    /**
     * Register main services with their dependencies.
     */
    protected function registerMainServices(): void
    {
        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService(
                $app->make(ValidationService::class),
                $app->make(AvailabilityRepository::class)
            );
        });

        $this->app->singleton('roster.availability', function ($app): AvailabilityService {
            return new AvailabilityService(
                $app->make(AvailabilityValidator::class),
                $app->make(ValidationService::class),
                $app->make(AvailabilityRepository::class)
            );
        });

        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService(
                $app->make(ValidationService::class),
                $app->make(AvailabilityRepository::class)
            );
        });

        $this->app->singleton(SlotFinderService::class, function ($app): SlotFinderService {
            return new SlotFinderService(
                $app->make(ValidationService::class)
            );
        });

        $this->app->alias('roster.schedule', ScheduleService::class);
        $this->app->alias('roster.availability', AvailabilityService::class);
        $this->app->alias('roster.impediment', ImpedimentService::class);
    }

    /**
     * Register the main manager.
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

        $this->app->alias('roster', RosterManager::class);
    }
}
