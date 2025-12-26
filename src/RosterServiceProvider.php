<?php

declare(strict_types=1);

namespace Roster;

use Roster\Commands\CacheRulesCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
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

class RosterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerResourcePublisher();
            $this->publishResources();
            $this->commands([InstallRosterCommand::class]);
        }

        Availability::observe(EnforceDomainMutationObserver::class);
        Schedule::observe(EnforceDomainMutationObserver::class);
        Impediment::observe(EnforceDomainMutationObserver::class);

        Model::automaticallyEagerLoadRelationships();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/roster.php', 'roster');

        $this->loadHelpers();
        $this->registerRepositories();
        $this->registerValidationSystem();
        $this->registerDomainServices();


        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheRulesCommand::class,
            ]);
        }
    }

    protected function loadHelpers(): void
    {
        $helpersFile = __DIR__ . '/helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }


    protected function registerRepositories(): void
    {
        $this->app->bind(AvailabilityRepositoryInterface::class, AvailabilityRepository::class);
        $this->app->bind(ImpedimentRepositoryInterface::class, ImpedimentRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
    }

    protected function registerValidationSystem(): void
    {
        $useFileCache = config('roster.cache.use_file_cache', true);

        $this->app->singleton(ValidatorInterface::class, function ($app) use ($useFileCache): Validator {
            $directories = array_merge(
                [__DIR__ . '/Validation/Rules'],
                config('roster.rule_directories', [])
            );

            $ruleScanner = new RuleScanner($directories, $useFileCache);

            return new Validator($ruleScanner);
        });


        $this->app->singleton(TemporalConflictService::class, function ($app): TemporalConflictService {
            return new TemporalConflictService(
                $app->make(AvailabilityRepositoryInterface::class),
                $app->make(ScheduleRepositoryInterface::class),
                $app->make(ImpedimentRepositoryInterface::class)
            );
        });

        $this->app->singleton(RuleScanner::class, function ($app) use ($useFileCache): RuleScanner {
            return new RuleScanner(
                array_merge([__DIR__ . '/Validation/Rules'], config('roster.rule_directories', [])),
                $useFileCache
            );
        });
    }

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

    private function registerResourcePublisher(): void {}

    private function publishResources(): void
    {
        // Configuration de validation
        $this->publishes([
            __DIR__ . '/../config/roster.php' => config_path('roster.php'),
        ], 'roster-config');

        // Migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'roster-migrations');
    }
}
