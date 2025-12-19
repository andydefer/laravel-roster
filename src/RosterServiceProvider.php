<?php

declare(strict_types=1);

namespace Roster;

use Illuminate\Support\ServiceProvider;
use Roster\Commands\InstallRosterCommand;
use Roster\Repositories\AvailabilityRepository;
use Roster\Repositories\ImpedimentRepository;
use Roster\Repositories\ScheduleRepository;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\AvailabilityValidatorInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Contracts\Services\AvailabilityMergerInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\AvailabilityCheckerInterface;
use Roster\Services\AvailabilityService;
use Roster\Services\AvailabilityValidator;
use Roster\Services\Core\ScheduleSlotFinder;
use Roster\Services\Core\ValidationService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Roster\Services\AvailabilityMerger;
use Roster\Services\SlotFinder;
use Roster\Services\AvailabilityChecker;

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
        $this->registerRepositories();
        $this->registerMainServices();
        $this->registerManager();
    }

    /**
     * Register reusable core services.
     */
    protected function registerCoreServices(): void
    {
        // Bind interfaces to concrete implementations
        $this->app->bind(ValidationServiceInterface::class, ValidationService::class);
        $this->app->bind(AvailabilityValidatorInterface::class, AvailabilityValidator::class);

        // Singletons for stateless services
        $this->app->singleton(ScheduleSlotFinder::class);

        // New refactored services (bind to interfaces)
        $this->app->bind(AvailabilityMergerInterface::class, AvailabilityMerger::class);
        $this->app->bind(SlotFinderInterface::class, SlotFinder::class);
        $this->app->bind(AvailabilityCheckerInterface::class, AvailabilityChecker::class);
    }

    /**
     * Register repository implementations.
     */
    protected function registerRepositories(): void
    {
        $this->app->bind(
            AvailabilityRepositoryInterface::class,
            AvailabilityRepository::class
        );

        $this->app->bind(
            ImpedimentRepositoryInterface::class,
            ImpedimentRepository::class
        );

        $this->app->bind(
            ScheduleRepositoryInterface::class,
            ScheduleRepository::class
        );
    }

    /**
     * Register main services with their dependencies.
     */
    protected function registerMainServices(): void
    {
        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService(
                $app->make(ValidationServiceInterface::class),
                $app->make(AvailabilityRepositoryInterface::class),
                $app->make(ImpedimentRepositoryInterface::class),
                $app->make(ScheduleRepositoryInterface::class),
                $app->make(ScheduleSlotFinder::class)
            );
        });

        $this->app->singleton('roster.availability', function ($app): AvailabilityService {
            return new AvailabilityService(
                $app->make(AvailabilityValidatorInterface::class),
                $app->make(ValidationServiceInterface::class),
                $app->make(AvailabilityRepositoryInterface::class),
                $app->make(AvailabilityMergerInterface::class),
                $app->make(SlotFinderInterface::class),
                $app->make(AvailabilityCheckerInterface::class)
            );
        });

        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService(
                $app->make(ValidationServiceInterface::class),
                $app->make(AvailabilityRepositoryInterface::class),
                $app->make(ImpedimentRepositoryInterface::class)
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
