<?php

declare(strict_types=1);

namespace Roster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Roster\Commands\CacheRulesCommand;
use Roster\Commands\DebugRulesCommand;
use Roster\Commands\InstallRosterCommand;
use Roster\Commands\ListRulesCommand;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Domain\Helpers\TimezoneHelper;
use Roster\Domain\Services\TemporalConflictService;
use Roster\Http\Middleware\SetUserTimezone;
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
 * Handles registration of all package services, repositories, configurations,
 * and bootstrapping for the scheduling system.
 */
class RosterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap package services.
     * Registers observers, initializes systems, and publishes resources.
     */
    public function boot(): void
    {
        $this->registerModelObservers();
        if (config('roster.allow_middleware')) {

            $this->initializeTimezoneSystem();
        }

        Model::automaticallyEagerLoadRelationships();

        if ($this->app->runningInConsole()) {
            $this->commands([InstallRosterCommand::class]);
            $this->publishResources();
        }
    }

    /**
     * Register package services and dependencies.
     * Binds interfaces to implementations and registers all required services.
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
            $this->commands([
                InstallRosterCommand::class,
                CacheRulesCommand::class,
                DebugRulesCommand::class,
                ListRulesCommand::class
            ]);
        }
    }

    /**
     * Initialize the timezone system for the roster package.
     * Registers middleware for web requests and ensures timezone helper is ready.
     */
    protected function initializeTimezoneSystem(): void
    {
        TimezoneHelper::initialize();

        if ($this->app->runningInConsole() === false) {
            $this->app['router']->aliasMiddleware(
                'roster.timezone',
                SetUserTimezone::class
            );
        }
    }

    /**
     * Register observers for domain models to enforce business rules.
     * Ensures data integrity and domain constraints during model operations.
     */
    protected function registerModelObservers(): void
    {
        Availability::observe(EnforceDomainMutationObserver::class);
        Schedule::observe(EnforceDomainMutationObserver::class);
        Impediment::observe(EnforceDomainMutationObserver::class);
    }

    /**
     * Load package helper functions.
     * Includes global helper functions if the helper file exists.
     */
    protected function loadHelpers(): void
    {
        $helpersPath = __DIR__ . '/helpers.php';

        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    /**
     * Register repository interfaces with their concrete implementations.
     * Provides data access layer abstraction for domain services.
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
     * Sets up rule scanning and validation services with configurable caching.
     */
    protected function registerValidationSystem(): void
    {
        $useFileCache = config('roster.cache.use_file_cache', true);
        $ruleDirectories = config('roster.rule_directories', []);
        $defaultRuleDirectory = __DIR__ . '/Validation/Rules';

        $this->registerValidator($useFileCache, $ruleDirectories, $defaultRuleDirectory);
        $this->registerTemporalConflictService();
        $this->registerRuleScanner($useFileCache, $ruleDirectories, $defaultRuleDirectory);
    }

    /**
     * Register the Validator service with rule scanning capabilities.
     *
     * @param bool $useFileCache Whether to use file-based caching for rules
     * @param array $ruleDirectories Additional directories containing validation rules
     * @param string $defaultRuleDirectory Default package rule directory
     */
    private function registerValidator(bool $useFileCache, array $ruleDirectories, string $defaultRuleDirectory): void
    {
        $this->app->singleton(
            ValidatorInterface::class,
            function ($app) use ($useFileCache, $ruleDirectories, $defaultRuleDirectory): Validator {
                $directories = array_merge([$defaultRuleDirectory], $ruleDirectories);

                $ruleScanner = new RuleScanner(
                    directories: $directories,
                    useFileCache: $useFileCache
                );

                return new Validator(ruleScanner: $ruleScanner);
            }
        );
    }

    /**
     * Register the TemporalConflictService for detecting scheduling conflicts.
     */
    private function registerTemporalConflictService(): void
    {
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
    }

    /**
     * Register the RuleScanner for discovering validation rules.
     *
     * @param bool $useFileCache Whether to use file-based caching for rules
     * @param array $ruleDirectories Additional directories containing validation rules
     * @param string $defaultRuleDirectory Default package rule directory
     */
    private function registerRuleScanner(bool $useFileCache, array $ruleDirectories, string $defaultRuleDirectory): void
    {
        $this->app->singleton(
            RuleScanner::class,
            function ($app) use ($useFileCache, $ruleDirectories, $defaultRuleDirectory): RuleScanner {
                $directories = array_merge([$defaultRuleDirectory], $ruleDirectories);

                return new RuleScanner(
                    directories: $directories,
                    useFileCache: $useFileCache
                );
            }
        );
    }

    /**
     * Register domain services with the dependency injection container.
     * Each service is registered as a singleton with all required dependencies.
     */
    protected function registerDomainServices(): void
    {
        $this->registerAvailabilityService();
        $this->registerScheduleService();
        $this->registerImpedimentService();

        $this->createServiceAliases();
    }

    /**
     * Register the AvailabilityService for managing availability records.
     */
    private function registerAvailabilityService(): void
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
    }

    /**
     * Register the ScheduleService for managing schedule records.
     */
    private function registerScheduleService(): void
    {
        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                conflictService: $app->make(TemporalConflictService::class)
            );
        });
    }

    /**
     * Register the ImpedimentService for managing impediment records.
     */
    private function registerImpedimentService(): void
    {
        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                conflictService: $app->make(TemporalConflictService::class)
            );
        });
    }

    /**
     * Create convenient aliases for the domain services.
     */
    private function createServiceAliases(): void
    {
        $this->app->alias('roster.availability', AvailabilityService::class);
        $this->app->alias('roster.schedule', ScheduleService::class);
        $this->app->alias('roster.impediment', ImpedimentService::class);
    }

    /**
     * Publish package resources for user customization.
     * Makes configuration files and migrations available for modification.
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
