<?php

declare(strict_types=1);

namespace Roster;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Roster\Commands\InstallRosterCommand;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\AvailabilityCheckerInterface;
use Roster\Contracts\Services\AvailabilityMergerInterface;
use Roster\Contracts\Services\AvailabilityValidatorInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Services\ValidationServiceInterface;
use Roster\Repositories\AvailabilityRepository;
use Roster\Repositories\ImpedimentRepository;
use Roster\Repositories\ScheduleRepository;
use Roster\Services\AvailabilityService;
use Roster\Services\Core\AvailabilityChecker;
use Roster\Services\Core\AvailabilityMerger;
use Roster\Services\Core\AvailabilityValidator;
use Roster\Services\Core\ResourcePublisherService;
use Roster\Services\Core\SlotFinderService;
use Roster\Services\Core\ValidationService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;

/**
 * Service provider for the Roster package.
 *
 * Handles registration and bootstrapping of all package components,
 * including configuration, migrations, views, routes, and service bindings.
 */
class RosterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * Publishes configuration, migrations, and views when running in console mode.
     * Does NOT load migrations automatically - user must publish them.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerResourcePublisher();
            $this->publishResources();
            $this->commands([InstallRosterCommand::class]);
        }

        $this->loadPublishedResources();
    }

    /**
     * Register the service provider.
     *
     * Merges package configuration and registers all service bindings.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/roster.php', 'roster');

        $this->registerCoreServices();
        $this->registerRepositories();
        $this->registerDomainServices();
    }

    /**
     * Register reusable core services.
     *
     * Binds interfaces to their concrete implementations for core service components.
     */
    protected function registerCoreServices(): void
    {
        $this->app->bind(AvailabilityValidatorInterface::class, AvailabilityValidator::class);
        $this->app->bind(AvailabilityCheckerInterface::class, AvailabilityChecker::class);
        $this->app->bind(AvailabilityMergerInterface::class, AvailabilityMerger::class);
        $this->app->bind(SlotFinderInterface::class, SlotFinderService::class);
        $this->app->bind(ValidationServiceInterface::class, ValidationService::class);
    }

    /**
     * Register repository implementations.
     *
     * Binds repository interfaces to their concrete implementations.
     */
    protected function registerRepositories(): void
    {
        $this->app->bind(AvailabilityRepositoryInterface::class, AvailabilityRepository::class);
        $this->app->bind(ImpedimentRepositoryInterface::class, ImpedimentRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
    }

    /**
     * Register main domain services with their dependencies.
     *
     * Creates singleton instances of the main business services and aliases them.
     */
    protected function registerDomainServices(): void
    {
        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService(
                validationService: $app->make(ValidationServiceInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                slotFinder: $app->make(SlotFinderInterface::class),
            );
        });

        $this->app->singleton('roster.availability', function ($app): AvailabilityService {
            return new AvailabilityService(
                availabilityValidator: $app->make(AvailabilityValidatorInterface::class),
                validationService: $app->make(ValidationServiceInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                availabilityMerger: $app->make(AvailabilityMergerInterface::class),
                slotFinder: $app->make(SlotFinderInterface::class),
                availabilityChecker: $app->make(AvailabilityCheckerInterface::class),
            );
        });

        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService(
                validationService: $app->make(ValidationServiceInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
            );
        });

        $this->app->alias('roster.schedule', ScheduleService::class);
        $this->app->alias('roster.availability', AvailabilityService::class);
        $this->app->alias('roster.impediment', ImpedimentService::class);
    }

    /**
     * Register the resource publisher service.
     */
    private function registerResourcePublisher(): void
    {
        $this->app->singleton(ResourcePublisherService::class, function ($app): ResourcePublisherService {
            return new ResourcePublisherService(
                application: $app,
                filesystem: new Filesystem
            );
        });
    }

    /**
     * Publish package resources for console usage.
     *
     * Publishes configuration files, migrations, and views to the application.
     * User MUST publish these resources to use the package.
     */
    private function publishResources(): void
    {
        // Configuration
        $this->publishes([
            __DIR__ . '/../config/roster.php' => config_path('roster.php'),
        ], 'roster-config');

        // Migrations - préfixées avec roster_
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'roster-migrations');

        // Views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/roster'),
        ], 'roster-views');

        // Routes
        $this->publishes([
            __DIR__ . '/../routes/web.php' => base_path('routes/roster.php'),
        ], 'roster-routes');
    }

    /**
     * Load published resources if they exist.
     */
    private function loadPublishedResources(): void
    {
        $routesPath = base_path('routes/roster.php');
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        $viewsPath = resource_path('views/vendor/roster');
        if (file_exists($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'roster');
        }
    }
}
