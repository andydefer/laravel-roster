<?php

declare(strict_types=1);

namespace Roster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Roster\Commands\CacheRulesCommand;
use Roster\Commands\InstallRosterCommand;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Observers\EnforceDomainMutationObserver;
use Roster\Repositories\AvailabilityRepository;
use Roster\Repositories\ImpedimentRepository;
use Roster\Repositories\ScheduleRepository;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Roster\Validation\RuleScanner;
use Roster\Validation\Validator;

/**
 * Service provider for the Roster package.
 *
 * Registers all package services, repositories, and configurations.
 * Handles bootstrapping and publishing of package resources.
 */
class RosterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap package services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallRosterCommand::class]);
            $this->publishResources();
        }

        $this->registerModelObservers();

        Model::automaticallyEagerLoadRelationships();
    }

    /**
     * Register package services and dependencies.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/roster.php',
            'roster'
        );

        $this->loadHelpers();
        $this->registerRepositories();
        $this->registerValidationSystem();
        $this->registerDomainServices();

        if ($this->app->runningInConsole()) {
            $this->commands([CacheRulesCommand::class]);
        }
    }

    /**
     * Register observers for domain models.
     *
     * @return void
     */
    protected function registerModelObservers(): void
    {
        Availability::observe(EnforceDomainMutationObserver::class);
        Schedule::observe(EnforceDomainMutationObserver::class);
        Impediment::observe(EnforceDomainMutationObserver::class);
    }

    /**
     * Load package helper functions.
     *
     * @return void
     */
    protected function loadHelpers(): void
    {
        $helpersPath = __DIR__ . '/helpers.php';

        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    /**
     * Register repository interfaces with their implementations.
     *
     * @return void
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
     * Register validation system components.
     *
     * @return void
     */
    protected function registerValidationSystem(): void
    {
        $useFileCache = config('roster.cache.use_file_cache', true);
        $ruleDirectories = config('roster.rule_directories', []);

        $this->app->singleton(
            ValidatorInterface::class,
            function ($app) use ($useFileCache, $ruleDirectories): Validator {
                $directories = array_merge(
                    [__DIR__ . '/Validation/Rules'],
                    $ruleDirectories
                );

                $ruleScanner = new RuleScanner(
                    directories: $directories,
                    useFileCache: $useFileCache
                );

                return new Validator(ruleScanner: $ruleScanner);
            }
        );

        $this->app->singleton(
            TemporalConflictService::class,
            function ($app): TemporalConflictService {
                return new TemporalConflictService(
                    availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                    scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                    impedimentRepository: $app->make(ImpedimentRepositoryInterface::class)
                );
            }
        );

        $this->app->singleton(
            RuleScanner::class,
            function ($app) use ($useFileCache, $ruleDirectories): RuleScanner {
                $directories = array_merge(
                    [__DIR__ . '/Validation/Rules'],
                    $ruleDirectories
                );

                return new RuleScanner(
                    directories: $directories,
                    useFileCache: $useFileCache
                );
            }
        );
    }

    /**
     * Register domain services with dependency injection container.
     *
     * @return void
     */
    protected function registerDomainServices(): void
    {
        $this->app->singleton('roster.availability', function ($app): AvailabilityService {
            return new AvailabilityService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                conflictService: $app->make(TemporalConflictService::class)
            );
        });

        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                conflictService: $app->make(TemporalConflictService::class)
            );
        });

        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                conflictService: $app->make(TemporalConflictService::class)
            );
        });

        $this->app->alias('roster.availability', AvailabilityService::class);
        $this->app->alias('roster.schedule', ScheduleService::class);
        $this->app->alias('roster.impediment', ImpedimentService::class);
    }

    /**
     * Publish package resources for user customization.
     *
     * @return void
     */
    private function publishResources(): void
    {
        $this->publishes([
            __DIR__ . '/../config/roster.php' => config_path('roster.php'),
        ], 'roster-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'roster-migrations');
    }
}
