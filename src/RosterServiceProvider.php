<?php

declare(strict_types=1);

namespace Roster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Roster\Commands\InstallRosterCommand;
use Roster\Contracts\Repository\AvailabilityRepositoryInterface;
use Roster\Contracts\Repository\ImpedimentRepositoryInterface;
use Roster\Contracts\Repository\ScheduleRepositoryInterface;
use Roster\Contracts\Services\AvailabilityCheckerInterface;
use Roster\Contracts\Services\SlotFinderInterface;
use Roster\Contracts\Validation\ValidatorInterface;
use Roster\Models\Availability;
use Roster\Models\Impediment;
use Roster\Models\Schedule;
use Roster\Observers\SchedulableObserver;
use Roster\Repositories\AvailabilityRepository;
use Roster\Repositories\ImpedimentRepository;
use Roster\Repositories\ScheduleRepository;
use Roster\Services\AvailabilityService;
use Roster\Services\Core\AvailabilityChecker;
use Roster\Services\Core\ResourcePublisherService;
use Roster\Services\Core\SlotFinderService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;
use Roster\Validation\RuleScanner;
use Roster\Validation\Validator;

class RosterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerResourcePublisher();
            $this->publishResources();
            $this->commands([InstallRosterCommand::class]);
        }

        $this->loadPublishedResources();

        Availability::observe(SchedulableObserver::class);
        Schedule::observe(SchedulableObserver::class);
        Impediment::observe(SchedulableObserver::class);

        Model::automaticallyEagerLoadRelationships();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/roster.php', 'roster');
        $this->mergeConfigFrom(__DIR__ . '/../config/roster-validation.php', 'roster-validation');

        // Charger les helpers
        $this->loadHelpers();

        $this->registerCoreServices();
        $this->registerRepositories();
        $this->registerValidationSystem();
        $this->registerDomainServices();
    }

    /**
     * Charger les fichiers helpers
     */
    protected function loadHelpers(): void
    {
        $helpersFile = __DIR__ . '/helpers.php';

        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }

    // Le reste du code reste inchangé...
    protected function registerCoreServices(): void
    {
        $this->app->bind(AvailabilityCheckerInterface::class, AvailabilityChecker::class);

        $this->app->bind(SlotFinderInterface::class, SlotFinderService::class);
    }


    protected function registerRepositories(): void
    {
        $this->app->bind(AvailabilityRepositoryInterface::class, AvailabilityRepository::class);
        $this->app->bind(ImpedimentRepositoryInterface::class, ImpedimentRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
    }

    protected function registerValidationSystem(): void
    {
        $withCache = config('roster-validation.with_cache', false);

        $this->app->singleton(ValidatorInterface::class, function ($app) use ($withCache): Validator {
            $directories = array_merge(
                [__DIR__ . '/Validation/Rules'],
                config('roster-validation.rule_directories', [])
            );

            $ruleScanner = new RuleScanner($directories, $withCache);

            return new Validator($ruleScanner);
        });

        $this->app->singleton(RuleScanner::class, function ($app) use ($withCache): RuleScanner {
            return new RuleScanner(
                array_merge([__DIR__ . '/Validation/Rules'], config('roster-validation.rule_directories', [])),
                $withCache
            );
        });
    }


    protected function registerDomainServices(): void
    {
        // AvailabilityService avec le nouveau système de validation
        $this->app->singleton('roster.availability', function ($app): AvailabilityService {
            return new AvailabilityService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),

            );
        });

        // ScheduleService
        $this->app->singleton('roster.schedule', function ($app): ScheduleService {
            return new ScheduleService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
            );
        });

        // ImpedimentService
        $this->app->singleton('roster.impediment', function ($app): ImpedimentService {
            return new ImpedimentService(
                validator: $app->make(ValidatorInterface::class),
                availabilityRepository: $app->make(AvailabilityRepositoryInterface::class),
                impedimentRepository: $app->make(ImpedimentRepositoryInterface::class),
                scheduleRepository: $app->make(ScheduleRepositoryInterface::class),
                slotFinder: $app->make(SlotFinderInterface::class)
            );
        });

        $this->app->alias('roster.availability', AvailabilityService::class);
        $this->app->alias('roster.schedule', ScheduleService::class);
        $this->app->alias('roster.impediment', ImpedimentService::class);
    }

    private function registerResourcePublisher(): void
    {
        $this->app->singleton(ResourcePublisherService::class, function ($app): ResourcePublisherService {
            return new ResourcePublisherService(
                application: $app,
                filesystem: new Filesystem()
            );
        });
    }

    private function publishResources(): void
    {
        // Configuration de validation
        $this->publishes([
            __DIR__ . '/../config/roster-validation.php' => config_path('roster-validation.php'),
        ], 'roster-validation-config');

        // Configuration principale
        $this->publishes([
            __DIR__ . '/../config/roster.php' => config_path('roster.php'),
        ], 'roster-config');

        // Migrations
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
