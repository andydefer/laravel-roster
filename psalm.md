# 📝 Psalm Analysis Report

INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L16\src/Commands/[1;31mInstallRosterCommand.php:16:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$laravel is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L16\src/Commands/[1;31mInstallRosterCommand.php:16:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$name is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L16\src/Commands/[1;31mInstallRosterCommand.php:16:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$components is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L16\src/Commands/[1;31mInstallRosterCommand.php:16:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$input is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L16\src/Commands/[1;31mInstallRosterCommand.php:16:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$output is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L16\src/Commands/[1;31mInstallRosterCommand.php:16:7[0m]8;;\ - Class Roster\Commands\InstallRosterCommand is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mInstallRosterCommand[0m extends Command


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L37\src/Commands/[1;31mInstallRosterCommand.php:37:21[0m]8;;\ - Cannot find any calls to method Roster\Commands\InstallRosterCommand::handle (see https://psalm.dev/087)
    public function [97;41mhandle[0m(): void


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Installation/InstallationExecutorInterface.php#L18\src/Contracts/Installation/[1;31mInstallationExecutorInterface.php:18:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Installation\InstallationExecutorInterface::executeSteps (see https://psalm.dev/087)
    public function [97;41mexecuteSteps[0m(array $context = []): array;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Installation/InstallationExecutorInterface.php#L26\src/Contracts/Installation/[1;31mInstallationExecutorInterface.php:26:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Installation\InstallationExecutorInterface::addStep (see https://psalm.dev/087)
    public function [97;41maddStep[0m(InstallationStepInterface $step): self;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Installation/InstallationExecutorInterface.php#L33\src/Contracts/Installation/[1;31mInstallationExecutorInterface.php:33:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Installation\InstallationExecutorInterface::getSteps (see https://psalm.dev/087)
    public function [97;41mgetSteps[0m(): array;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Installation/InstallationStepInterface.php#L17\src/Contracts/Installation/[1;31mInstallationStepInterface.php:17:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Installation\InstallationStepInterface::execute (see https://psalm.dev/087)
    public function [97;41mexecute[0m(): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Installation/InstallationStepInterface.php#L24\src/Contracts/Installation/[1;31mInstallationStepInterface.php:24:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Installation\InstallationStepInterface::getName (see https://psalm.dev/087)
    public function [97;41mgetName[0m(): string;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Installation/InstallationStepInterface.php#L31\src/Contracts/Installation/[1;31mInstallationStepInterface.php:31:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Installation\InstallationStepInterface::getDescription (see https://psalm.dev/087)
    public function [97;41mgetDescription[0m(): string;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Installation/InstallationStepInterface.php#L39\src/Contracts/Installation/[1;31mInstallationStepInterface.php:39:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Installation\InstallationStepInterface::shouldExecute (see https://psalm.dev/087)
    public function [97;41mshouldExecute[0m(array $context = []): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L78\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:78:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::getForDate (see https://psalm.dev/087)
    public function [97;41mgetForDate[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L92\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:92:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::getAllForSchedulable (see https://psalm.dev/087)
    public function [97;41mgetAllForSchedulable[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L133\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:133:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::timeRangesOverlap (see https://psalm.dev/087)
    public function [97;41mtimeRangesOverlap[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L149\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:149:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::dateRangesOverlap (see https://psalm.dev/087)
    public function [97;41mdateRangesOverlap[0m(


[0;31mERROR[0m: PossiblyUnusedReturnValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L176\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:176:16[0m]8;;\ - The return value for this method is never used (see https://psalm.dev/273)
     * @return [97;41mbool[0m True if all deletions successful, false otherwise


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L252\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:252:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::filterAvailabilitiesForDate (see https://psalm.dev/087)
    public function [97;41mfilterAvailabilitiesForDate[0m(Collection $availabilities, Carbon $date): Collection;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php#L61\src/Contracts/Repository/[1;31mScheduleRepositoryInterface.php:61:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\ScheduleRepositoryInterface::findForTimeSlot (see https://psalm.dev/087)
    public function [97;41mfindForTimeSlot[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php#L92\src/Contracts/Repository/[1;31mScheduleRepositoryInterface.php:92:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\ScheduleRepositoryInterface::findOverlappingSchedules (see https://psalm.dev/087)
    public function [97;41mfindOverlappingSchedules[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php#L107\src/Contracts/Repository/[1;31mScheduleRepositoryInterface.php:107:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\ScheduleRepositoryInterface::getAllForSchedulable (see https://psalm.dev/087)
    public function [97;41mgetAllForSchedulable[0m(


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/AvailabilityDependentServiceInterface.php#L9\src/Contracts/Services/[1;31mAvailabilityDependentServiceInterface.php:9:11[0m]8;;\ - Class Roster\Contracts\Services\AvailabilityDependentServiceInterface is never used (see https://psalm.dev/075)
interface [97;41mAvailabilityDependentServiceInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/AvailabilityValidatorInterface.php#L30\src/Contracts/Services/[1;31mAvailabilityValidatorInterface.php:30:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\AvailabilityValidatorInterface::hasOverlapping (see https://psalm.dev/087)
    public function [97;41mhasOverlapping[0m(Model $model, array $data, ?int $exceptId = null): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/AvailabilityValidatorInterface.php#L42\src/Contracts/Services/[1;31mAvailabilityValidatorInterface.php:42:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\AvailabilityValidatorInterface::overlaps (see https://psalm.dev/087)
    public function [97;41moverlaps[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/SchedulableServiceInterface.php#L22\src/Contracts/Services/[1;31mSchedulableServiceInterface.php:22:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\SchedulableServiceInterface::for (see https://psalm.dev/087)
    public function [97;41mfor[0m(Model $model): self;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/SchedulableServiceInterface.php#L27\src/Contracts/Services/[1;31mSchedulableServiceInterface.php:27:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\SchedulableServiceInterface::resetFilters (see https://psalm.dev/087)
    public function [97;41mresetFilters[0m(): self;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/SchedulableServiceInterface.php#L34\src/Contracts/Services/[1;31mSchedulableServiceInterface.php:34:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\SchedulableServiceInterface::whereType (see https://psalm.dev/087)
    public function [97;41mwhereType[0m(string $type): self;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/SchedulableServiceInterface.php#L41\src/Contracts/Services/[1;31mSchedulableServiceInterface.php:41:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\SchedulableServiceInterface::all (see https://psalm.dev/087)
    public function [97;41mall[0m(): Collection;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/SchedulableServiceInterface.php#L57\src/Contracts/Services/[1;31mSchedulableServiceInterface.php:57:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\SchedulableServiceInterface::update (see https://psalm.dev/087)
    public function [97;41mupdate[0m(int $id, array $data): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/SlotFinderInterface.php#L92\src/Contracts/Services/[1;31mSlotFinderInterface.php:92:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Services\SlotFinderInterface::hasAnyAvailabilityInPeriod (see https://psalm.dev/087)
    public function [97;41mhasAnyAvailabilityInPeriod[0m(


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Enums/ActivityType.php#L16\src/Enums/[1;31mActivityType.php:16:6[0m]8;;\ - Class Roster\Enums\ActivityType is never used (see https://psalm.dev/075)
enum [97;41mActivityType[0m: string


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Enums/DaysOfWeek.php#L15\src/Enums/[1;31mDaysOfWeek.php:15:6[0m]8;;\ - Class Roster\Enums\DaysOfWeek is never used (see https://psalm.dev/075)
enum [97;41mDaysOfWeek[0m: string


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/AvailabilityViolationException.php#L13\src/Exceptions/[1;31mAvailabilityViolationException.php:13:7[0m]8;;\ - Class Roster\Exceptions\AvailabilityViolationException is never used (see https://psalm.dev/075)
class [97;41mAvailabilityViolationException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/AvailabilityViolationException.php#L13\src/Exceptions/[1;31mAvailabilityViolationException.php:13:7[0m]8;;\ - Class Roster\Exceptions\AvailabilityViolationException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityViolationException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Messages/ErrorMessageFactory.php#L7\src/Exceptions/Messages/[1;31mErrorMessageFactory.php:7:7[0m]8;;\ - Class Roster\Exceptions\Messages\ErrorMessageFactory is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mErrorMessageFactory[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Messages/ErrorMessageFactory.php#L33\src/Exceptions/Messages/[1;31mErrorMessageFactory.php:33:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\Messages\ErrorMessageFactory::requiredField (see https://psalm.dev/087)
    public static function [97;41mrequiredField[0m(string $field): string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Messages/ErrorMessageFactory.php#L38\src/Exceptions/Messages/[1;31mErrorMessageFactory.php:38:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\Messages\ErrorMessageFactory::invalidTimezone (see https://psalm.dev/087)
    public static function [97;41minvalidTimezone[0m(string $timezone): string


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/MissingResourceException.php#L13\src/Exceptions/[1;31mMissingResourceException.php:13:7[0m]8;;\ - Class Roster\Exceptions\MissingResourceException is never used (see https://psalm.dev/075)
class [97;41mMissingResourceException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/MissingResourceException.php#L13\src/Exceptions/[1;31mMissingResourceException.php:13:7[0m]8;;\ - Class Roster\Exceptions\MissingResourceException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mMissingResourceException[0m extends RosterException


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NoMatchingAvailabilityException.php#L13\src/Exceptions/[1;31mNoMatchingAvailabilityException.php:13:7[0m]8;;\ - Class Roster\Exceptions\NoMatchingAvailabilityException is never used (see https://psalm.dev/075)
class [97;41mNoMatchingAvailabilityException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NoMatchingAvailabilityException.php#L13\src/Exceptions/[1;31mNoMatchingAvailabilityException.php:13:7[0m]8;;\ - Class Roster\Exceptions\NoMatchingAvailabilityException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mNoMatchingAvailabilityException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/OverlappingImpedimentException.php#L13\src/Exceptions/[1;31mOverlappingImpedimentException.php:13:7[0m]8;;\ - Class Roster\Exceptions\OverlappingImpedimentException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mOverlappingImpedimentException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/OverlappingScheduleException.php#L13\src/Exceptions/[1;31mOverlappingScheduleException.php:13:7[0m]8;;\ - Class Roster\Exceptions\OverlappingScheduleException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mOverlappingScheduleException[0m extends RosterException


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/RosterException.php#L42\src/Exceptions/[1;31mRosterException.php:42:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\RosterException::getType (see https://psalm.dev/087)
    public function [97;41mgetType[0m(): string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/RosterException.php#L52\src/Exceptions/[1;31mRosterException.php:52:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\RosterException::getContext (see https://psalm.dev/087)
    public function [97;41mgetContext[0m(): array


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/ScheduleImpedimentOverlapException.php#L16\src/Exceptions/[1;31mScheduleImpedimentOverlapException.php:16:7[0m]8;;\ - Class Roster\Exceptions\ScheduleImpedimentOverlapException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mScheduleImpedimentOverlapException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/TimeRangeValidationException.php#L15\src/Exceptions/[1;31mTimeRangeValidationException.php:15:7[0m]8;;\ - Class Roster\Exceptions\TimeRangeValidationException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mTimeRangeValidationException[0m extends RosterException


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/TimeSlotOverlapException.php#L16\src/Exceptions/[1;31mTimeSlotOverlapException.php:16:7[0m]8;;\ - Class Roster\Exceptions\TimeSlotOverlapException is never used (see https://psalm.dev/075)
class [97;41mTimeSlotOverlapException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/TimeSlotOverlapException.php#L16\src/Exceptions/[1;31mTimeSlotOverlapException.php:16:7[0m]8;;\ - Class Roster\Exceptions\TimeSlotOverlapException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mTimeSlotOverlapException[0m extends RosterException


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/ValidationException.php#L16\src/Exceptions/[1;31mValidationException.php:16:7[0m]8;;\ - Class Roster\Exceptions\ValidationException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mValidationException[0m extends RosterException


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Availability.php#L40\src/Facades/[1;31mAvailability.php:40:7[0m]8;;\ - Class Roster\Facades\Availability is never used (see https://psalm.dev/075)
class [97;41mAvailability[0m extends Facade


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Availability.php#L40\src/Facades/[1;31mAvailability.php:40:7[0m]8;;\ - Class Roster\Facades\Availability is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailability[0m extends Facade


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Availability.php#L45\src/Facades/[1;31mAvailability.php:45:5[0m]8;;\ - Method Roster\Facades\Availability::getfacadeaccessor should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get the service container binding for the facade.
     */
    [97;41mprotected static function getFacadeAccessor(): string[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Impediment.php#L30\src/Facades/[1;31mImpediment.php:30:7[0m]8;;\ - Class Roster\Facades\Impediment is never used (see https://psalm.dev/075)
class [97;41mImpediment[0m extends Facade


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Impediment.php#L30\src/Facades/[1;31mImpediment.php:30:7[0m]8;;\ - Class Roster\Facades\Impediment is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpediment[0m extends Facade


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Impediment.php#L35\src/Facades/[1;31mImpediment.php:35:5[0m]8;;\ - Method Roster\Facades\Impediment::getfacadeaccessor should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Retourne l'alias du container pour le service
     */
    [97;41mprotected static function getFacadeAccessor(): string[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Roster.php#L19\src/Facades/[1;31mRoster.php:19:7[0m]8;;\ - Class Roster\Facades\Roster is never used (see https://psalm.dev/075)
class [97;41mRoster[0m extends Facade


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Roster.php#L19\src/Facades/[1;31mRoster.php:19:7[0m]8;;\ - Class Roster\Facades\Roster is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mRoster[0m extends Facade


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Roster.php#L24\src/Facades/[1;31mRoster.php:24:5[0m]8;;\ - Method Roster\Facades\Roster::getfacadeaccessor should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Retourne l'alias du container pour le manager
     */
    [97;41mprotected static function getFacadeAccessor(): string[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Schedule.php#L32\src/Facades/[1;31mSchedule.php:32:7[0m]8;;\ - Class Roster\Facades\Schedule is never used (see https://psalm.dev/075)
class [97;41mSchedule[0m extends Facade


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Schedule.php#L32\src/Facades/[1;31mSchedule.php:32:7[0m]8;;\ - Class Roster\Facades\Schedule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSchedule[0m extends Facade


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Schedule.php#L37\src/Facades/[1;31mSchedule.php:37:5[0m]8;;\ - Method Roster\Facades\Schedule::getfacadeaccessor should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Retourne l'alias du container pour le service
     */
    [97;41mprotected static function getFacadeAccessor(): string[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L12\src/Models/[1;31mAvailability.php:12:7[0m]8;;\ - Class Roster\Models\Availability is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailability[0m extends Model


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L38\src/Models/[1;31mAvailability.php:38:21[0m]8;;\ - Cannot find explicit calls to method Roster\Models\Availability::schedulable (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mschedulable[0m(): MorphTo


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L46\src/Models/[1;31mAvailability.php:46:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::schedules (see https://psalm.dev/087)
    public function [97;41mschedules[0m(): HasMany


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L54\src/Models/[1;31mAvailability.php:54:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::impediments (see https://psalm.dev/087)
    public function [97;41mimpediments[0m(): HasMany


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L62\src/Models/[1;31mAvailability.php:62:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::isAvailableForSchedule (see https://psalm.dev/087)
    public function [97;41misAvailableForSchedule[0m(Carbon $start, Carbon $end): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L66\src/Models/[1;31mAvailability.php:66:35[0m]8;;\ - Instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/041)
        if (!in_array($dayOfWeek, [30;47m$this->days[0m)) {


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L75\src/Models/[1;31mAvailability.php:75:26[0m]8;;\ - Instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/041)
            $startTime < [30;47m$this->start_time[0m->format('H:i:s') ||


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L76\src/Models/[1;31mAvailability.php:76:24[0m]8;;\ - Instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/041)
            $endTime > [30;47m$this->end_time[0m->format('H:i:s')


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L82\src/Models/[1;31mAvailability.php:82:13[0m]8;;\ - Instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/041)
        if ([30;47m$this->start_date[0m && $start->lt($this->start_date)) {


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L86\src/Models/[1;31mAvailability.php:86:18[0m]8;;\ - Instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/041)
        return !([30;47m$this->end_date[0m && $end->gt($this->end_date));


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L11\src/Models/[1;31mImpediment.php:11:7[0m]8;;\ - Class Roster\Models\Impediment is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpediment[0m extends Model


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L34\src/Models/[1;31mImpediment.php:34:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::availability (see https://psalm.dev/087)
    public function [97;41mavailability[0m(): BelongsTo


INFO: MissingReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L42\src/Models/[1;31mImpediment.php:42:21[0m]8;;\ - Method Roster\Models\Impediment::schedulable does not have a return type (see https://psalm.dev/050)
    public function [30;47mschedulable[0m()


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L42\src/Models/[1;31mImpediment.php:42:21[0m]8;;\ - Cannot find explicit calls to method Roster\Models\Impediment::schedulable (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mschedulable[0m()


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L50\src/Models/[1;31mImpediment.php:50:21[0m]8;;\ - Cannot find explicit calls to method Roster\Models\Impediment::overlapsWith (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41moverlapsWith[0m(Carbon $start, Carbon $end): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L52\src/Models/[1;31mImpediment.php:52:16[0m]8;;\ - Instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->lt($end) && $this->end_datetime->gt($start);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L52\src/Models/[1;31mImpediment.php:52:51[0m]8;;\ - Instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/041)
        return $this->start_datetime->lt($end) && [30;47m$this->end_datetime[0m->gt($start);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L58\src/Models/[1;31mImpediment.php:58:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::getDurationMinutesAttribute (see https://psalm.dev/087)
    public function [97;41mgetDurationMinutesAttribute[0m(): float


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L60\src/Models/[1;31mImpediment.php:60:16[0m]8;;\ - Instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->diffInMinutes($this->end_datetime);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L60\src/Models/[1;31mImpediment.php:60:53[0m]8;;\ - Instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/041)
        return $this->start_datetime->diffInMinutes([30;47m$this->end_datetime[0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L66\src/Models/[1;31mImpediment.php:66:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::isActive (see https://psalm.dev/087)
    public function [97;41misActive[0m(): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L69\src/Models/[1;31mImpediment.php:69:16[0m]8;;\ - Instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->lte($now) && $this->end_datetime->gte($now);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L69\src/Models/[1;31mImpediment.php:69:52[0m]8;;\ - Instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/041)
        return $this->start_datetime->lte($now) && [30;47m$this->end_datetime[0m->gte($now);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L75\src/Models/[1;31mImpediment.php:75:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::isUpcoming (see https://psalm.dev/087)
    public function [97;41misUpcoming[0m(): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L77\src/Models/[1;31mImpediment.php:77:16[0m]8;;\ - Instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->gt(Carbon::now());


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L83\src/Models/[1;31mImpediment.php:83:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::isPast (see https://psalm.dev/087)
    public function [97;41misPast[0m(): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L85\src/Models/[1;31mImpediment.php:85:16[0m]8;;\ - Instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->end_datetime[0m->lt(Carbon::now());


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L12\src/Models/[1;31mSchedule.php:12:7[0m]8;;\ - Class Roster\Models\Schedule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSchedule[0m extends Model


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L36\src/Models/[1;31mSchedule.php:36:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::availability (see https://psalm.dev/087)
    public function [97;41mavailability[0m(): BelongsTo


INFO: MissingReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L44\src/Models/[1;31mSchedule.php:44:21[0m]8;;\ - Method Roster\Models\Schedule::schedulable does not have a return type (see https://psalm.dev/050)
    public function [30;47mschedulable[0m()


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L44\src/Models/[1;31mSchedule.php:44:21[0m]8;;\ - Cannot find explicit calls to method Roster\Models\Schedule::schedulable (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mschedulable[0m()


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L46\src/Models/[1;31mSchedule.php:46:16[0m]8;;\ - Instance property Roster\Models\Schedule::$availability is not defined (see https://psalm.dev/041)
        return [30;47m$this->availability[0m ? $this->availability->schedulable() : null;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L52\src/Models/[1;31mSchedule.php:52:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::getTypeAttribute (see https://psalm.dev/087)
    public function [97;41mgetTypeAttribute[0m(): string


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L54\src/Models/[1;31mSchedule.php:54:16[0m]8;;\ - Instance property Roster\Models\Schedule::$availability is not defined (see https://psalm.dev/041)
        return [30;47m$this->availability[0m->type;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L60\src/Models/[1;31mSchedule.php:60:21[0m]8;;\ - Cannot find explicit calls to method Roster\Models\Schedule::overlapsWith (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41moverlapsWith[0m(Carbon $start, Carbon $end): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L62\src/Models/[1;31mSchedule.php:62:16[0m]8;;\ - Instance property Roster\Models\Schedule::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->lt($end) && $this->end_datetime->gt($start);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L62\src/Models/[1;31mSchedule.php:62:51[0m]8;;\ - Instance property Roster\Models\Schedule::$end_datetime is not defined (see https://psalm.dev/041)
        return $this->start_datetime->lt($end) && [30;47m$this->end_datetime[0m->gt($start);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L68\src/Models/[1;31mSchedule.php:68:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::getDurationMinutesAttribute (see https://psalm.dev/087)
    public function [97;41mgetDurationMinutesAttribute[0m(): float


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L70\src/Models/[1;31mSchedule.php:70:16[0m]8;;\ - Instance property Roster\Models\Schedule::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->diffInMinutes($this->end_datetime);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L70\src/Models/[1;31mSchedule.php:70:53[0m]8;;\ - Instance property Roster\Models\Schedule::$end_datetime is not defined (see https://psalm.dev/041)
        return $this->start_datetime->diffInMinutes([30;47m$this->end_datetime[0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L76\src/Models/[1;31mSchedule.php:76:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::isActive (see https://psalm.dev/087)
    public function [97;41misActive[0m(): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L79\src/Models/[1;31mSchedule.php:79:16[0m]8;;\ - Instance property Roster\Models\Schedule::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->lte($now) && $this->end_datetime->gte($now);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L79\src/Models/[1;31mSchedule.php:79:52[0m]8;;\ - Instance property Roster\Models\Schedule::$end_datetime is not defined (see https://psalm.dev/041)
        return $this->start_datetime->lte($now) && [30;47m$this->end_datetime[0m->gte($now);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L85\src/Models/[1;31mSchedule.php:85:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::isUpcoming (see https://psalm.dev/087)
    public function [97;41misUpcoming[0m(): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L87\src/Models/[1;31mSchedule.php:87:16[0m]8;;\ - Instance property Roster\Models\Schedule::$start_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->start_datetime[0m->gt(Carbon::now());


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L93\src/Models/[1;31mSchedule.php:93:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::isPast (see https://psalm.dev/087)
    public function [97;41misPast[0m(): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L95\src/Models/[1;31mSchedule.php:95:16[0m]8;;\ - Instance property Roster\Models\Schedule::$end_datetime is not defined (see https://psalm.dev/041)
        return [30;47m$this->end_datetime[0m->lt(Carbon::now());


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L17\src/Repositories/[1;31mAbstractRepository.php:17:30[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AbstractRepository::create (see https://psalm.dev/087)
    abstract public function [97;41mcreate[0m(array $data): Model;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L24\src/Repositories/[1;31mAbstractRepository.php:24:30[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AbstractRepository::update (see https://psalm.dev/087)
    abstract public function [97;41mupdate[0m(int $id, array $data): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L29\src/Repositories/[1;31mAbstractRepository.php:29:30[0m]8;;\ - Cannot find explicit calls to method Roster\Repositories\AbstractRepository::delete (but did find some potential callers) (see https://psalm.dev/087)
    abstract public function [97;41mdelete[0m(int $id): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L41\src/Repositories/[1;31mAbstractRepository.php:41:30[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AbstractRepository::getAll (see https://psalm.dev/087)
    abstract public function [97;41mgetAll[0m(): Collection;


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L16\src/Repositories/[1;31mAvailabilityRepository.php:16:7[0m]8;;\ - Class Roster\Repositories\AvailabilityRepository is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityRepository[0m extends AbstractRepository implements AvailabilityRepositoryInterface


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L22\src/Repositories/[1;31mAvailabilityRepository.php:22:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AvailabilityRepository::__construct (see https://psalm.dev/087)
    public function [97;41m__construct[0m(ValidationServiceInterface $validationService)


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L30\src/Repositories/[1;31mAvailabilityRepository.php:30:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::create should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Create a new availability.
     */
    [97;41mpublic function create(array $data): Availability[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L32\src/Repositories/[1;31mAvailabilityRepository.php:32:16[0m]8;;\ - Magic method Roster\Models\Availability::create does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::create($data)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L38\src/Repositories/[1;31mAvailabilityRepository.php:38:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Update an existing availability.
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L49\src/Repositories/[1;31mAvailabilityRepository.php:49:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getfordaterange should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getForDateRange([0m


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L54\src/Repositories/[1;31mAvailabilityRepository.php:54:8[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getForDateRange is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
    ): [30;47mCollection[0m {


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L56\src/Repositories/[1;31mAvailabilityRepository.php:56:21[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
            ->where([30;47mfunction ($query) use ($end): void {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $end);
            }[0m)


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L57\src/Repositories/[1;31mAvailabilityRepository.php:57:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
                $query->[30;47mwhereNull[0m('start_date')


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L60\src/Repositories/[1;31mAvailabilityRepository.php:60:21[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
            ->where([30;47mfunction ($query) use ($start): void {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $start);
            }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L61\src/Repositories/[1;31mAvailabilityRepository.php:61:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
                $query->[30;47mwhereNull[0m('end_date')


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L65\src/Repositories/[1;31mAvailabilityRepository.php:65:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L69\src/Repositories/[1;31mAvailabilityRepository.php:69:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getForDateRange (see https://psalm.dev/129)
        return [30;47m$builder->get()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L72\src/Repositories/[1;31mAvailabilityRepository.php:72:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findfortimeslotwithpartialoverlaps should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function findForTimeSlotWithPartialOverlaps([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L80\src/Repositories/[1;31mAvailabilityRepository.php:80:16[0m]8;;\ - Magic method Roster\Models\Availability::where does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::where('schedulable_id', $model->id)[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L80\src/Repositories/[1;31mAvailabilityRepository.php:80:54[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        return Availability::where('schedulable_id', [30;47m$model->id[0m)


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L82\src/Repositories/[1;31mAvailabilityRepository.php:82:37[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            ->when($type, function ([30;47m$query[0m) use ($type): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L88\src/Repositories/[1;31mAvailabilityRepository.php:88:31[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
            ->where(function ([30;47m$q[0m) use ($start): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L92\src/Repositories/[1;31mAvailabilityRepository.php:92:31[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
            ->where(function ([30;47m$q[0m) use ($end): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L97\src/Repositories/[1;31mAvailabilityRepository.php:97:81[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            ->withExists(['schedules as has_overlapping_schedules' => function ([30;47m$query[0m) use ($start, $end): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L101\src/Repositories/[1;31mAvailabilityRepository.php:101:85[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            ->withExists(['impediments as has_overlapping_impediments' => function ([30;47m$query[0m) use ($start, $end): void {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L111\src/Repositories/[1;31mAvailabilityRepository.php:111:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::delete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Delete an availability.
     */
    [97;41mpublic function delete(int $id): bool[0m


INFO: InvalidNullableReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L111\src/Repositories/[1;31mAvailabilityRepository.php:111:38[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\AvailabilityRepository::delete is not nullable, but 'bool|null' contains null (see https://psalm.dev/144)
    public function delete(int $id): [30;47mbool[0m


INFO: NullableReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L119\src/Repositories/[1;31mAvailabilityRepository.php:119:16[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\AvailabilityRepository::delete is not nullable, but the function returns 'bool|null' (see https://psalm.dev/139)
        return [30;47m$availability->delete()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L125\src/Repositories/[1;31mAvailabilityRepository.php:125:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::deletemultiple should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Delete multiple availabilities by IDs.
     */
    [97;41mpublic function deleteMultiple(array $ids): bool[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L127\src/Repositories/[1;31mAvailabilityRepository.php:127:16[0m]8;;\ - Magic method Roster\Models\Availability::wherein does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::whereIn('id', $ids)[0m->delete() > 0;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L133\src/Repositories/[1;31mAvailabilityRepository.php:133:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findbyid should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find availability by ID.
     */
    [97;41mpublic function findById(int $id): ?Availability[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L135\src/Repositories/[1;31mAvailabilityRepository.php:135:16[0m]8;;\ - Magic method Roster\Models\Availability::find does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::find($id)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L143\src/Repositories/[1;31mAvailabilityRepository.php:143:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findfortimeslot should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find availability for a time slot.
     */
    [97;41mpublic function findForTimeSlot([0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L153\src/Repositories/[1;31mAvailabilityRepository.php:153:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L170\src/Repositories/[1;31mAvailabilityRepository.php:170:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getfordate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get availabilities for a specific date.
     *
     * @return Collection<int, Availability>
     */
    [97;41mpublic function getForDate([0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L178\src/Repositories/[1;31mAvailabilityRepository.php:178:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L195\src/Repositories/[1;31mAvailabilityRepository.php:195:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getall should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get all availabilities.
     *
     * @return Collection<int, Availability>
     */
    [97;41mpublic function getAll(): Collection[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L195\src/Repositories/[1;31mAvailabilityRepository.php:195:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AvailabilityRepository::getAll (see https://psalm.dev/087)
    public function [97;41mgetAll[0m(): Collection


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L207\src/Repositories/[1;31mAvailabilityRepository.php:207:16[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getAllForSchedulable is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
     * @return [30;47mCollection<int, Availability>[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L209\src/Repositories/[1;31mAvailabilityRepository.php:209:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getallforschedulable should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get all availabilities for a schedulable.
     *
     * @return Collection<int, Availability>
     */
    [97;41mpublic function getAllForSchedulable([0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L217\src/Repositories/[1;31mAvailabilityRepository.php:217:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L221\src/Repositories/[1;31mAvailabilityRepository.php:221:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$day[0m) {


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L225\src/Repositories/[1;31mAvailabilityRepository.php:225:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getAllForSchedulable (see https://psalm.dev/129)
        return [30;47m$builder->orderBy('start_time')->get()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L231\src/Repositories/[1;31mAvailabilityRepository.php:231:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::isavailableat should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if schedulable is available at specific datetime.
     */
    [97;41mpublic function isAvailableAt([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L254\src/Repositories/[1;31mAvailabilityRepository.php:254:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findoverlapping should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find overlapping availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    [97;41mpublic function findOverlapping([0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L256\src/Repositories/[1;31mAvailabilityRepository.php:256:15[0m]8;;\ - Argument 2 of Roster\Repositories\AvailabilityRepository::findOverlapping has wrong name $data, expecting $availabilityData as defined by Roster\Contracts\Repository\AvailabilityRepositoryInterface::findOverlapping (see https://psalm.dev/230)
        array [97;41m$data[0m,


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L273\src/Repositories/[1;31mAvailabilityRepository.php:273:14[0m]8;;\ - Operand of type array<never, never>|mixed contains type mixed, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if (![30;47mempty($days)[0m) {


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L274\src/Repositories/[1;31mAvailabilityRepository.php:274:29[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but impure-Closure(static):void provided (see https://psalm.dev/004)
            $builder->where([30;47mfunction ($query) use ($days): void {
                foreach ($days as $day) {
                    $query->orWhereJsonContains('days', $day);
                }
            }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L276\src/Repositories/[1;31mAvailabilityRepository.php:276:29[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::orWhereJsonContains does not exist (see https://psalm.dev/022)
                    $query->[30;47morWhereJsonContains[0m('days', $day);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L282\src/Repositories/[1;31mAvailabilityRepository.php:282:25[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but impure-Closure(static):void provided (see https://psalm.dev/004)
        $builder->where([30;47mfunction ($query) use ($startTime, $endTime): void {
            $query->where(function ($q) use ($startTime, $endTime): void {
                $q->where('start_time', '<', $endTime->format('H:i:s'))
                    ->where('end_time', '>', $startTime->format('H:i:s'));
            });
        }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L283\src/Repositories/[1;31mAvailabilityRepository.php:283:21[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::where does not exist (see https://psalm.dev/022)
            $query->[30;47mwhere[0m(function ($q) use ($startTime, $endTime): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L283\src/Repositories/[1;31mAvailabilityRepository.php:283:37[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
            $query->where(function ([30;47m$q[0m) use ($startTime, $endTime): void {


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L290\src/Repositories/[1;31mAvailabilityRepository.php:290:25[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
        $builder->where([30;47mfunction ($query) use ($startDate, $endDate): void {
            $query->where(function ($q) use ($startDate, $endDate): void {
                // Aucune date de fin pour l'existant ou la nouvelle
                $q->whereNull('start_date')
                    ->orWhereNull('end_date')
                    ->orWhere(function ($subQuery) use ($startDate, $endDate): void {
                        if ($startDate instanceof Carbon && $endDate instanceof Carbon) {
                            // Les deux ont des dates, vérifier le chevauchement
                            $subQuery->where('start_date', '<=', $endDate)
                                ->where('end_date', '>=', $startDate);
                        } elseif ($startDate instanceof Carbon) {
                            // Seule la nouvelle a une date de début
                            $subQuery->where('end_date', '>=', $startDate)
                                ->orWhereNull('end_date');
                        } elseif ($endDate instanceof Carbon) {
                            // Seule la nouvelle a une date de fin
                            $subQuery->where('start_date', '<=', $endDate)
                                ->orWhereNull('start_date');
                        }
                    });
            });
        }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L291\src/Repositories/[1;31mAvailabilityRepository.php:291:21[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::where does not exist (see https://psalm.dev/022)
            $query->[30;47mwhere[0m(function ($q) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L291\src/Repositories/[1;31mAvailabilityRepository.php:291:37[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
            $query->where(function ([30;47m$q[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L295\src/Repositories/[1;31mAvailabilityRepository.php:295:41[0m]8;;\ - Parameter $subQuery has no provided type (see https://psalm.dev/153)
                    ->orWhere(function ([30;47m$subQuery[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L315\src/Repositories/[1;31mAvailabilityRepository.php:315:38[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            'schedules' => function ([30;47m$query[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L318\src/Repositories/[1;31mAvailabilityRepository.php:318:45[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
                    $query->where(function ([30;47m$q[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L321\src/Repositories/[1;31mAvailabilityRepository.php:321:49[0m]8;;\ - Parameter $subQ has no provided type (see https://psalm.dev/153)
                            ->orWhere(function ([30;47m$subQ[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L330\src/Repositories/[1;31mAvailabilityRepository.php:330:40[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            'impediments' => function ([30;47m$query[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L333\src/Repositories/[1;31mAvailabilityRepository.php:333:45[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
                    $query->where(function ([30;47m$q[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L336\src/Repositories/[1;31mAvailabilityRepository.php:336:49[0m]8;;\ - Parameter $subQ has no provided type (see https://psalm.dev/153)
                            ->orWhere(function ([30;47m$subQ[0m) use ($startDate, $endDate): void {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L363\src/Repositories/[1;31mAvailabilityRepository.php:363:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::timerangesoverlap should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if time ranges overlap.
     */
    [97;41mpublic function timeRangesOverlap([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L378\src/Repositories/[1;31mAvailabilityRepository.php:378:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findadjacentavailabilities should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    [97;41mpublic function findAdjacentAvailabilities([0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L380\src/Repositories/[1;31mAvailabilityRepository.php:380:15[0m]8;;\ - Argument 2 of Roster\Repositories\AvailabilityRepository::findAdjacentAvailabilities has wrong name $data, expecting $availabilityData as defined by Roster\Contracts\Repository\AvailabilityRepositoryInterface::findAdjacentAvailabilities (see https://psalm.dev/230)
        array [97;41m$data[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L399\src/Repositories/[1;31mAvailabilityRepository.php:399:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::applyfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Apply filters to query.
     */
    [97;41mpublic function applyFilters([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L421\src/Repositories/[1;31mAvailabilityRepository.php:421:16[0m]8;;\ - Magic method Roster\Models\Availability::where does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::where('schedulable_id', $model->id)[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L421\src/Repositories/[1;31mAvailabilityRepository.php:421:54[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        return Availability::where('schedulable_id', [30;47m$model->id[0m)


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L450\src/Repositories/[1;31mAvailabilityRepository.php:450:25[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but impure-Closure(static):void provided (see https://psalm.dev/004)
        $builder->where([30;47mfunction ($q) use ($startDate): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $startDate->toDateString());
        }[0m)->where(function ($q) use ($endDate): void {


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L451\src/Repositories/[1;31mAvailabilityRepository.php:451:17[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
            $q->[30;47mwhereNull[0m('start_date')


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L453\src/Repositories/[1;31mAvailabilityRepository.php:453:19[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but impure-Closure(static):void provided (see https://psalm.dev/004)
        })->where([30;47mfunction ($q) use ($endDate): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $endDate->toDateString());
        }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L454\src/Repositories/[1;31mAvailabilityRepository.php:454:17[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
            $q->[30;47mwhereNull[0m('end_date')


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L468\src/Repositories/[1;31mAvailabilityRepository.php:468:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::isavailabilityvalidfordate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if an availability applies to a specific date.
     *
     * @param Availability $availability The availability to check
     * @param Carbon $date The date to check
     * @return bool True if the availability applies to the date
     */


    [97;41mpublic function isAvailabilityValidForDate(Availability $availability, Carbon $date): bool[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L471\src/Repositories/[1;31mAvailabilityRepository.php:471:35[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
        if (!in_array($dayOfWeek, [30;47m$availability->days[0m)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L475\src/Repositories/[1;31mAvailabilityRepository.php:475:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
        if ([30;47m$availability->start_date[0m !== null && $date->lt($availability->start_date)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L479\src/Repositories/[1;31mAvailabilityRepository.php:479:18[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
        return !([30;47m$availability->end_date[0m !== null && $date->gt($availability->end_date));


INFO: ImplementedReturnTypeMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L489\src/Repositories/[1;31mAvailabilityRepository.php:489:16[0m]8;;\ - The inherited return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Contracts\Repository\AvailabilityRepositoryInterface::getAvailabilitiesWithConflictInfo is different to the implemented return type for Roster\Repositories\AvailabilityRepository::getavailabilitieswithconflictinfo 'Illuminate\Support\Collection<Roster\Models\Availability>' (see https://psalm.dev/123)
     * @return [30;47mCollection<Availability>[0m Availabilities with conflicts loaded


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L489\src/Repositories/[1;31mAvailabilityRepository.php:489:16[0m]8;;\ - Illuminate\Support\Collection has missing template params, expecting 2 (see https://psalm.dev/182)
     * @return [97;41mCollection<Availability>[0m Availabilities with conflicts loaded


INFO: InvalidTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L489\src/Repositories/[1;31mAvailabilityRepository.php:489:16[0m]8;;\ - Extended template param TKey of Illuminate\Support\Collection<Roster\Models\Availability> expects type array-key, type Roster\Models\Availability given (see https://psalm.dev/183)
     * @return [30;47mCollection<Availability>[0m Availabilities with conflicts loaded


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L491\src/Repositories/[1;31mAvailabilityRepository.php:491:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getavailabilitieswithconflictinfo should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Load availabilities with pre-loaded schedule and impediment conflicts.
     *
     * @param object $schedulable The schedulable entity
     * @param Carbon $start Start of the date range
     * @param Carbon $end End of the date range
     * @param string|null $type Optional availability type filter
     * @return Collection<Availability> Availabilities with conflicts loaded
     */
    [97;41mpublic function getAvailabilitiesWithConflictInfo([0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L492\src/Repositories/[1;31mAvailabilityRepository.php:492:16[0m]8;;\ - Argument 1 of Roster\Repositories\AvailabilityRepository::getAvailabilitiesWithConflictInfo has wrong name $schedulable, expecting $model as defined by Roster\Contracts\Repository\AvailabilityRepositoryInterface::getAvailabilitiesWithConflictInfo (see https://psalm.dev/230)
        object [97;41m$schedulable[0m,


INFO: ArgumentTypeCoercion - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L497\src/Repositories/[1;31mAvailabilityRepository.php:497:50[0m]8;;\ - Argument 1 of Roster\Repositories\AvailabilityRepository::getForDateRange expects Illuminate\Database\Eloquent\Model, but parent type object provided (see https://psalm.dev/193)
        $availabilities = $this->getForDateRange([30;47m$schedulable[0m, $start, $end, $type);


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L498\src/Repositories/[1;31mAvailabilityRepository.php:498:33[0m]8;;\ - Magic method Illuminate\Support\Collection::load does not exist (see https://psalm.dev/219)
        return $availabilities->[30;47mload[0m(['schedules', 'impediments']);


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L504\src/Repositories/[1;31mAvailabilityRepository.php:504:15[0m]8;;\ - Illuminate\Support\Collection has missing template params, expecting 2 (see https://psalm.dev/182)
     * @param [97;41mCollection<Availability>[0m $availabilities Collection of availabilities


INFO: InvalidTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L504\src/Repositories/[1;31mAvailabilityRepository.php:504:15[0m]8;;\ - Extended template param TKey of Illuminate\Support\Collection<Roster\Models\Availability> expects type array-key, type Roster\Models\Availability given (see https://psalm.dev/183)
     * @param [30;47mCollection<Availability>[0m $availabilities Collection of availabilities


INFO: ImplementedReturnTypeMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L506\src/Repositories/[1;31mAvailabilityRepository.php:506:16[0m]8;;\ - The inherited return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Contracts\Repository\AvailabilityRepositoryInterface::filterAvailabilitiesForDate is different to the implemented return type for Roster\Repositories\AvailabilityRepository::filteravailabilitiesfordate 'Illuminate\Support\Collection<Roster\Models\Availability>' (see https://psalm.dev/123)
     * @return [30;47mCollection<Availability>[0m Filtered availabilities


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L506\src/Repositories/[1;31mAvailabilityRepository.php:506:16[0m]8;;\ - Illuminate\Support\Collection has missing template params, expecting 2 (see https://psalm.dev/182)
     * @return [97;41mCollection<Availability>[0m Filtered availabilities


INFO: InvalidTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L506\src/Repositories/[1;31mAvailabilityRepository.php:506:16[0m]8;;\ - Extended template param TKey of Illuminate\Support\Collection<Roster\Models\Availability> expects type array-key, type Roster\Models\Availability given (see https://psalm.dev/183)
     * @return [30;47mCollection<Availability>[0m Filtered availabilities


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L508\src/Repositories/[1;31mAvailabilityRepository.php:508:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::filteravailabilitiesfordate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Filter availabilities for a specific date.
     *
     * @param Collection<Availability> $availabilities Collection of availabilities
     * @param Carbon $date Date to filter for
     * @return Collection<Availability> Filtered availabilities
     */
    [97;41mpublic function filterAvailabilitiesForDate(Collection $availabilities, Carbon $date): Collection[0m


INFO: ImplementedParamTypeMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L508\src/Repositories/[1;31mAvailabilityRepository.php:508:60[0m]8;;\ - Argument 1 of Roster\Repositories\AvailabilityRepository::filterAvailabilitiesForDate has wrong type 'Illuminate\Support\Collection<Roster\Models\Availability>', expecting 'Illuminate\Support\Collection<int, Roster\Models\Availability>' as defined by Roster\Contracts\Repository\AvailabilityRepositoryInterface::filterAvailabilitiesForDate (see https://psalm.dev/199)
    public function filterAvailabilitiesForDate(Collection [30;47m$availabilities[0m, Carbon $date): Collection


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L13\src/Repositories/[1;31mImpedimentRepository.php:13:7[0m]8;;\ - Class Roster\Repositories\ImpedimentRepository is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpedimentRepository[0m extends AbstractRepository implements ImpedimentRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L19\src/Repositories/[1;31mImpedimentRepository.php:19:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::create should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Create a new impediment.
     */
    [97;41mpublic function create(array $data): Impediment[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L19\src/Repositories/[1;31mImpedimentRepository.php:19:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\ImpedimentRepository::create (see https://psalm.dev/087)
    public function [97;41mcreate[0m(array $data): Impediment


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L21\src/Repositories/[1;31mImpedimentRepository.php:21:16[0m]8;;\ - Magic method Roster\Models\Impediment::create does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::create($data)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L27\src/Repositories/[1;31mImpedimentRepository.php:27:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Update an existing impediment.
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L27\src/Repositories/[1;31mImpedimentRepository.php:27:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\ImpedimentRepository::update (see https://psalm.dev/087)
    public function [97;41mupdate[0m(int $id, array $data): bool


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L41\src/Repositories/[1;31mImpedimentRepository.php:41:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::delete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Delete an impediment.
     */
    [97;41mpublic function delete(int $id): bool[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L41\src/Repositories/[1;31mImpedimentRepository.php:41:21[0m]8;;\ - Cannot find explicit calls to method Roster\Repositories\ImpedimentRepository::delete (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mdelete[0m(int $id): bool


INFO: InvalidNullableReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L41\src/Repositories/[1;31mImpedimentRepository.php:41:38[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ImpedimentRepository::delete is not nullable, but 'bool|null' contains null (see https://psalm.dev/144)
    public function delete(int $id): [30;47mbool[0m


INFO: NullableReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L49\src/Repositories/[1;31mImpedimentRepository.php:49:16[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ImpedimentRepository::delete is not nullable, but the function returns 'bool|null' (see https://psalm.dev/139)
        return [30;47m$impediment->delete()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L55\src/Repositories/[1;31mImpedimentRepository.php:55:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::findbyid should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find impediment by ID.
     */
    [97;41mpublic function findById(int $id): ?Impediment[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L57\src/Repositories/[1;31mImpedimentRepository.php:57:16[0m]8;;\ - Magic method Roster\Models\Impediment::find does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::find($id)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L63\src/Repositories/[1;31mImpedimentRepository.php:63:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::getall should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get all impediments.
     */
    [97;41mpublic function getAll(): Collection[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L63\src/Repositories/[1;31mImpedimentRepository.php:63:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\ImpedimentRepository::getAll (see https://psalm.dev/087)
    public function [97;41mgetAll[0m(): Collection


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L74\src/Repositories/[1;31mImpedimentRepository.php:74:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::findfortimeslot should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find impediments for a time slot.
     */
    [97;41mpublic function findForTimeSlot([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L79\src/Repositories/[1;31mImpedimentRepository.php:79:16[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::where('availability_id', $availabilityId)[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L89\src/Repositories/[1;31mImpedimentRepository.php:89:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::hasoverlappingimpediments should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if a time slot has overlapping impediments.
     */
    [97;41mpublic function hasOverlappingImpediments([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L95\src/Repositories/[1;31mImpedimentRepository.php:95:18[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        $query = [30;47mImpediment::where('availability_id', $availabilityId)[0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L99\src/Repositories/[1;31mImpedimentRepository.php:99:13[0m]8;;\ - Operand of type int|null contains type int, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$excludeId[0m) {


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L109\src/Repositories/[1;31mImpedimentRepository.php:109:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\ImpedimentRepository::findOverlappingImpediments (see https://psalm.dev/087)
    public function [97;41mfindOverlappingImpediments[0m(


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L115\src/Repositories/[1;31mImpedimentRepository.php:115:18[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        $query = [30;47mImpediment::where('availability_id', $availabilityId)[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L13\src/Repositories/[1;31mScheduleRepository.php:13:7[0m]8;;\ - Class Roster\Repositories\ScheduleRepository is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mScheduleRepository[0m extends AbstractRepository implements ScheduleRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L18\src/Repositories/[1;31mScheduleRepository.php:18:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::create should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Create a new schedule.
     */
    [97;41mpublic function create(array $data): Schedule[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L20\src/Repositories/[1;31mScheduleRepository.php:20:16[0m]8;;\ - Magic method Roster\Models\Schedule::create does not exist (see https://psalm.dev/219)
        return [30;47mSchedule::create($data)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L26\src/Repositories/[1;31mScheduleRepository.php:26:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Update an existing schedule.
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L40\src/Repositories/[1;31mScheduleRepository.php:40:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::delete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Delete a schedule.
     */
    [97;41mpublic function delete(int $id): bool[0m


INFO: InvalidNullableReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L40\src/Repositories/[1;31mScheduleRepository.php:40:38[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ScheduleRepository::delete is not nullable, but 'bool|null' contains null (see https://psalm.dev/144)
    public function delete(int $id): [30;47mbool[0m


INFO: NullableReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L48\src/Repositories/[1;31mScheduleRepository.php:48:16[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ScheduleRepository::delete is not nullable, but the function returns 'bool|null' (see https://psalm.dev/139)
        return [30;47m$schedule->delete()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L54\src/Repositories/[1;31mScheduleRepository.php:54:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::findbyid should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find schedule by ID.
     */
    [97;41mpublic function findById(int $id): ?Schedule[0m


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L57\src/Repositories/[1;31mScheduleRepository.php:57:51[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            'availability.schedules' => function ([30;47m$query[0m): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L61\src/Repositories/[1;31mScheduleRepository.php:61:53[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            'availability.impediments' => function ([30;47m$query[0m): void {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L70\src/Repositories/[1;31mScheduleRepository.php:70:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::getall should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get all schedules.
     */
    [97;41mpublic function getAll(): Collection[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L70\src/Repositories/[1;31mScheduleRepository.php:70:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\ScheduleRepository::getAll (see https://psalm.dev/087)
    public function [97;41mgetAll[0m(): Collection


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L80\src/Repositories/[1;31mScheduleRepository.php:80:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::findfortimeslot should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find schedules for a time slot.
     */
    [97;41mpublic function findForTimeSlot([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L85\src/Repositories/[1;31mScheduleRepository.php:85:16[0m]8;;\ - Magic method Roster\Models\Schedule::where does not exist (see https://psalm.dev/219)
        return [30;47mSchedule::where('availability_id', $availabilityId)[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L95\src/Repositories/[1;31mScheduleRepository.php:95:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::hasoverlappingschedule should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if a time slot has overlapping schedules.
     */
    [97;41mpublic function hasOverlappingSchedule([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L101\src/Repositories/[1;31mScheduleRepository.php:101:18[0m]8;;\ - Magic method Roster\Models\Schedule::where does not exist (see https://psalm.dev/219)
        $query = [30;47mSchedule::where('availability_id', $availabilityId)[0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L105\src/Repositories/[1;31mScheduleRepository.php:105:13[0m]8;;\ - Operand of type int|null contains type int, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$excludeId[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L115\src/Repositories/[1;31mScheduleRepository.php:115:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::findoverlappingschedules should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find overlapping schedules with time range.
     */
    [97;41mpublic function findOverlappingSchedules([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L121\src/Repositories/[1;31mScheduleRepository.php:121:18[0m]8;;\ - Magic method Roster\Models\Schedule::where does not exist (see https://psalm.dev/219)
        $query = [30;47mSchedule::where('availability_id', $availabilityId)[0m


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L137\src/Repositories/[1;31mScheduleRepository.php:137:16[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getAllForSchedulable is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
     * @return [30;47mCollection<int, Schedule>[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L139\src/Repositories/[1;31mScheduleRepository.php:139:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::getallforschedulable should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get all schedules for a schedulable.
     *
     * @return Collection<int, Schedule>
     */
    [97;41mpublic function getAllForSchedulable([0m


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L149\src/Repositories/[1;31mScheduleRepository.php:149:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getAllForSchedulable (see https://psalm.dev/129)
        return [30;47m$builder->orderBy('start_datetime')->get()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L155\src/Repositories/[1;31mScheduleRepository.php:155:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::getfordaterange should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get schedules between dates.
     */
    [97;41mpublic function getForDateRange([0m


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L161\src/Repositories/[1;31mScheduleRepository.php:161:8[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getForDateRange is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
    ): [30;47mCollection[0m {


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L168\src/Repositories/[1;31mScheduleRepository.php:168:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getForDateRange (see https://psalm.dev/129)
        return [30;47m$builder->orderBy('start_datetime')->get()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L174\src/Repositories/[1;31mScheduleRepository.php:174:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::applyfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Apply filters to query.
     */
    [97;41mpublic function applyFilters([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L190\src/Repositories/[1;31mScheduleRepository.php:190:16[0m]8;;\ - Magic method Roster\Models\Schedule::wherehas does not exist (see https://psalm.dev/219)
        return [30;47mSchedule::whereHas('availability', function ($query) use ($schedulableId, $schedulableType): void {
            $query->where('schedulable_id', $schedulableId)
                ->where('schedulable_type', $schedulableType);
        })[0m;


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L190\src/Repositories/[1;31mScheduleRepository.php:190:61[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
        return Schedule::whereHas('availability', function ([30;47m$query[0m) use ($schedulableId, $schedulableType): void {


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L35\src/[1;31mRosterServiceProvider.php:35:7[0m]8;;\ - Class Roster\RosterServiceProvider is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mRosterServiceProvider[0m extends ServiceProvider


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L43\src/[1;31mRosterServiceProvider.php:43:21[0m]8;;\ - Cannot find any calls to method Roster\RosterServiceProvider::boot (see https://psalm.dev/087)
    public function [97;41mboot[0m(): void


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L65\src/[1;31mRosterServiceProvider.php:65:5[0m]8;;\ - Method Roster\RosterServiceProvider::register should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Register the service provider.
     *
     * Merges package configuration and registers all service bindings.
     */
    [97;41mpublic function register(): void[0m


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L107\src/[1;31mRosterServiceProvider.php:107:60[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton('roster.schedule', function ([30;47m$app[0m): ScheduleService {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L117\src/[1;31mRosterServiceProvider.php:117:64[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton('roster.availability', function ([30;47m$app[0m): AvailabilityService {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L128\src/[1;31mRosterServiceProvider.php:128:62[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton('roster.impediment', function ([30;47m$app[0m): ImpedimentService {


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L21\src/Services/[1;31mAvailabilityService.php:21:7[0m]8;;\ - Class Roster\Services\AvailabilityService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityService[0m extends AbstractSchedulableService


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L57\src/Services/[1;31mAvailabilityService.php:57:5[0m]8;;\ - Method Roster\Services\AvailabilityService::validatedurationhook should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateDurationHook([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L74\src/Services/[1;31mAvailabilityService.php:74:5[0m]8;;\ - Method Roster\Services\AvailabilityService::validatemaxdayshook should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateMaxDaysHook(string $operation, int $maxDays): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L88\src/Services/[1;31mAvailabilityService.php:88:5[0m]8;;\ - Method Roster\Services\AvailabilityService::getvalidationservice should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function getValidationService(): ValidationServiceInterface[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L93\src/Services/[1;31mAvailabilityService.php:93:5[0m]8;;\ - Method Roster\Services\AvailabilityService::validatebeforecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateBeforeCreate(): void[0m


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L98\src/Services/[1;31mAvailabilityService.php:98:56[0m]8;;\ - Argument 1 of Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping cannot be null, possibly null value provided (see https://psalm.dev/078)
        if ($this->availabilityChecker->hasOverlapping([30;47m$this->schedulable[0m, $this->data)) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L103\src/Services/[1;31mAvailabilityService.php:103:5[0m]8;;\ - Method Roster\Services\AvailabilityService::processbeforecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function processBeforeCreate(): void[0m


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L105\src/Services/[1;31mAvailabilityService.php:105:81[0m]8;;\ - Argument 2 of Roster\Contracts\Services\AvailabilityMergerInterface::mergeWithAdjacent cannot be null, possibly null value provided (see https://psalm.dev/078)
        $this->data = $this->availabilityMerger->mergeWithAdjacent($this->data, [30;47m$this->schedulable[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L106\src/Services/[1;31mAvailabilityService.php:106:41[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        $this->data['schedulable_id'] = [30;47m$this->schedulable->id[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L110\src/Services/[1;31mAvailabilityService.php:110:5[0m]8;;\ - Method Roster\Services\AvailabilityService::executecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function executeCreate(): Availability[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L115\src/Services/[1;31mAvailabilityService.php:115:5[0m]8;;\ - Method Roster\Services\AvailabilityService::validatebeforeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateBeforeUpdate(int $id): void[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L132\src/Services/[1;31mAvailabilityService.php:132:37[0m]8;;\ - Cannot get property on possibly null variable $this->currentAvailability of type Roster\Models\Availability|null (see https://psalm.dev/082)
                    'start_time' => [30;47m$this->currentAvailability->start_time[0m?->format('H:i:s'),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L132\src/Services/[1;31mAvailabilityService.php:132:37[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
                    'start_time' => [30;47m$this->currentAvailability->start_time[0m?->format('H:i:s'),


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L133\src/Services/[1;31mAvailabilityService.php:133:35[0m]8;;\ - Cannot get property on possibly null variable $this->currentAvailability of type Roster\Models\Availability|null (see https://psalm.dev/082)
                    'end_time' => [30;47m$this->currentAvailability->end_time[0m?->format('H:i:s'),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L133\src/Services/[1;31mAvailabilityService.php:133:35[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
                    'end_time' => [30;47m$this->currentAvailability->end_time[0m?->format('H:i:s'),


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L140\src/Services/[1;31mAvailabilityService.php:140:46[0m]8;;\ - Argument 1 of Roster\Services\AvailabilityService::prepareCheckData cannot be null, possibly null value provided (see https://psalm.dev/078)
        $checkData = $this->prepareCheckData([30;47m$this->currentAvailability[0m, $this->data);


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L142\src/Services/[1;31mAvailabilityService.php:142:56[0m]8;;\ - Argument 1 of Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping cannot be null, possibly null value provided (see https://psalm.dev/078)
        if ($this->availabilityChecker->hasOverlapping([30;47m$this->schedulable[0m, $checkData, $id)) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L147\src/Services/[1;31mAvailabilityService.php:147:5[0m]8;;\ - Method Roster\Services\AvailabilityService::processbeforeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function processBeforeUpdate(int $id): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L152\src/Services/[1;31mAvailabilityService.php:152:5[0m]8;;\ - Method Roster\Services\AvailabilityService::executeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function executeUpdate(int $id): bool[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L166\src/Services/[1;31mAvailabilityService.php:166:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::create (see https://psalm.dev/087)
    public function [97;41mcreate[0m(array $data): Availability


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L200\src/Services/[1;31mAvailabilityService.php:200:21[0m]8;;\ - Cannot find explicit calls to method Roster\Services\AvailabilityService::delete (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mdelete[0m(int $id): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L212\src/Services/[1;31mAvailabilityService.php:212:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::hasOverlapping (see https://psalm.dev/087)
    public function [97;41mhasOverlapping[0m(array $data, ?int $exceptId = null): bool


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L215\src/Services/[1;31mAvailabilityService.php:215:59[0m]8;;\ - Argument 1 of Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityChecker->hasOverlapping([30;47m$this->schedulable[0m, $data, $exceptId);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L218\src/Services/[1;31mAvailabilityService.php:218:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::findOverlapping (see https://psalm.dev/087)
    public function [97;41mfindOverlapping[0m(array $data, ?int $exceptId = null): Collection


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L222\src/Services/[1;31mAvailabilityService.php:222:63[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findOverlapping cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityRepository->findOverlapping([30;47m$this->schedulable[0m, $data, $exceptId);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L225\src/Services/[1;31mAvailabilityService.php:225:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::findAdjacentAvailabilities (see https://psalm.dev/087)
    public function [97;41mfindAdjacentAvailabilities[0m(array $data): Collection


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L228\src/Services/[1;31mAvailabilityService.php:228:77[0m]8;;\ - Argument 2 of Roster\Contracts\Services\AvailabilityMergerInterface::findAdjacentAvailabilities cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityMerger->findAdjacentAvailabilities($data, [30;47m$this->schedulable[0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L231\src/Services/[1;31mAvailabilityService.php:231:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::whereDay (see https://psalm.dev/087)
    public function [97;41mwhereDay[0m(string $day): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L237\src/Services/[1;31mAvailabilityService.php:237:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::isAvailableAt (see https://psalm.dev/087)
    public function [97;41misAvailableAt[0m(Carbon $datetime): bool


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L240\src/Services/[1;31mAvailabilityService.php:240:58[0m]8;;\ - Argument 1 of Roster\Contracts\Services\AvailabilityCheckerInterface::isAvailableAt cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityChecker->isAvailableAt([30;47m$this->schedulable[0m, $datetime);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L243\src/Services/[1;31mAvailabilityService.php:243:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::isAvailableForPeriod (see https://psalm.dev/087)
    public function [97;41misAvailableForPeriod[0m(Carbon $start, Carbon $end, ?string $type = null): bool


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L246\src/Services/[1;31mAvailabilityService.php:246:65[0m]8;;\ - Argument 1 of Roster\Contracts\Services\AvailabilityCheckerInterface::isAvailableForPeriod cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityChecker->isAvailableForPeriod([30;47m$this->schedulable[0m, $start, $end, $type);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L249\src/Services/[1;31mAvailabilityService.php:249:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::findSlotsInPeriod (see https://psalm.dev/087)
    public function [97;41mfindSlotsInPeriod[0m(


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L258\src/Services/[1;31mAvailabilityService.php:258:13[0m]8;;\ - Argument 1 of Roster\Contracts\Services\SlotFinderInterface::findSlotsInPeriod cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L270\src/Services/[1;31mAvailabilityService.php:270:23[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
            'type' => [30;47m$availability->type[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L271\src/Services/[1;31mAvailabilityService.php:271:23[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
            'days' => [30;47m$availability->days[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L272\src/Services/[1;31mAvailabilityService.php:272:29[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
            'start_date' => [30;47m$availability->start_date[0m?->format('Y-m-d'),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L273\src/Services/[1;31mAvailabilityService.php:273:27[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
            'end_date' => [30;47m$availability->end_date[0m?->format('Y-m-d'),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L276\src/Services/[1;31mAvailabilityService.php:276:49[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        if (!isset($checkData['start_time']) && [30;47m$availability->start_time[0m) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L280\src/Services/[1;31mAvailabilityService.php:280:47[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        if (!isset($checkData['end_time']) && [30;47m$availability->end_time[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L289\src/Services/[1;31mAvailabilityService.php:289:5[0m]8;;\ - Method Roster\Services\AvailabilityService::applyfilters should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function applyFilters(): Builder[0m


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L291\src/Services/[1;31mAvailabilityService.php:291:60[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::applyFilters cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityRepository->applyFilters([30;47m$this->schedulable[0m, $this->filters);


[0;31mERROR[0m: PossiblyUnusedProperty - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L43\src/Services/Core/[1;31mAbstractSchedulableService.php:43:21[0m]8;;\ - Cannot find any references to property Roster\Services\Core\AbstractSchedulableService::$originalData (see https://psalm.dev/149)
    protected array [97;41m$originalData[0m = [];


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L48\src/Services/Core/[1;31mAbstractSchedulableService.php:48:5[0m]8;;\ - Method Roster\Services\Core\AbstractSchedulableService::for should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Scope the service to a specific parent model.
     */
    [97;41mfinal public function for(Model $model): static[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L58\src/Services/Core/[1;31mAbstractSchedulableService.php:58:27[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractSchedulableService::getSchedulable (see https://psalm.dev/087)
    final public function [97;41mgetSchedulable[0m(): ?Model


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L66\src/Services/Core/[1;31mAbstractSchedulableService.php:66:5[0m]8;;\ - Method Roster\Services\Core\AbstractSchedulableService::resetfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Clear all applied filters.
     */
    [97;41mfinal public function resetFilters(): static[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L88\src/Services/Core/[1;31mAbstractSchedulableService.php:88:5[0m]8;;\ - Method Roster\Services\Core\AbstractSchedulableService::all should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Return all matching results.
     */
    [97;41mfinal public function all(): Collection[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L96\src/Services/Core/[1;31mAbstractSchedulableService.php:96:5[0m]8;;\ - Method Roster\Services\Core\AbstractSchedulableService::get should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Execute the query with the current filters.
     */
    [97;41mfinal public function get(): Collection[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L106\src/Services/Core/[1;31mAbstractSchedulableService.php:106:5[0m]8;;\ - Method Roster\Services\Core\AbstractSchedulableService::wheretype should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Filter results by type.
     */
    [97;41mfinal public function whereType(string $type): static[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L118\src/Services/Core/[1;31mAbstractSchedulableService.php:118:5[0m]8;;\ - Method Roster\Services\Core\AbstractSchedulableService::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * TEMPLATE METHOD: Update with configuration validation
     *
     * @param array<string, mixed> $data
     */
    [97;41mfinal public function update(int $id, array $data): bool[0m


[0;31mERROR[0m: UnusedParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L234\src/Services/Core/[1;31mAbstractSchedulableService.php:234:57[0m]8;;\ - Param #1 is never referenced in this method (see https://psalm.dev/135)
    final protected function validateFutureDates(string [97;41m$operation[0m, string $entityType, array $entityConfig): void


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L249\src/Services/Core/[1;31mAbstractSchedulableService.php:249:21[0m]8;;\ - Operand of type false|mixed contains type mixed, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
                if ([30;47m!$allowPast[0m) {


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L298\src/Services/Core/[1;31mAbstractSchedulableService.php:298:30[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractSchedulableService::getEntityDisplayName (see https://psalm.dev/087)
    final protected function [97;41mgetEntityDisplayName[0m(): string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractSchedulableService.php#L407\src/Services/Core/[1;31mAbstractSchedulableService.php:407:24[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractSchedulableService::validateRequiredFields (see https://psalm.dev/087)
    protected function [97;41mvalidateRequiredFields[0m(array $requiredFields = []): void


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L13\src/Services/Core/[1;31mAvailabilityChecker.php:13:7[0m]8;;\ - Class Roster\Services\Core\AvailabilityChecker is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityChecker[0m implements AvailabilityCheckerInterface


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L15\src/Services/Core/[1;31mAvailabilityChecker.php:15:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AvailabilityChecker::__construct (see https://psalm.dev/087)
    public function [97;41m__construct[0m(


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L23\src/Services/Core/[1;31mAvailabilityChecker.php:23:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityChecker::isavailableat should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if the schedulable is available at a given time.
     */
    [97;41mpublic function isAvailableAt(object $schedulable, Carbon $datetime): bool[0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L23\src/Services/Core/[1;31mAvailabilityChecker.php:23:42[0m]8;;\ - Argument 1 of Roster\Services\Core\AvailabilityChecker::isAvailableAt has wrong name $schedulable, expecting $model as defined by Roster\Contracts\Services\AvailabilityCheckerInterface::isAvailableAt (see https://psalm.dev/230)
    public function isAvailableAt(object [97;41m$schedulable[0m, Carbon $datetime): bool


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L31\src/Services/Core/[1;31mAvailabilityChecker.php:31:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityChecker::isavailableforperiod should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check availability for a time period.
     */
    [97;41mpublic function isAvailableForPeriod([0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L32\src/Services/Core/[1;31mAvailabilityChecker.php:32:16[0m]8;;\ - Argument 1 of Roster\Services\Core\AvailabilityChecker::isAvailableForPeriod has wrong name $schedulable, expecting $model as defined by Roster\Contracts\Services\AvailabilityCheckerInterface::isAvailableForPeriod (see https://psalm.dev/230)
        object [97;41m$schedulable[0m,


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L48\src/Services/Core/[1;31mAvailabilityChecker.php:48:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityChecker::hasoverlapping should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if there are overlaps.
     *
     * @param array<string, mixed> $data
     */
    [97;41mpublic function hasOverlapping([0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L49\src/Services/Core/[1;31mAvailabilityChecker.php:49:16[0m]8;;\ - Argument 1 of Roster\Services\Core\AvailabilityChecker::hasOverlapping has wrong name $schedulable, expecting $model as defined by Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping (see https://psalm.dev/230)
        object [97;41m$schedulable[0m,


INFO: ArgumentTypeCoercion - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L57\src/Services/Core/[1;31mAvailabilityChecker.php:57:63[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findOverlapping expects Illuminate\Database\Eloquent\Model, but parent type object provided (see https://psalm.dev/193)
        return $this->availabilityRepository->findOverlapping([30;47m$schedulable[0m, $data, $exceptId)->isNotEmpty();


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L16\src/Services/Core/[1;31mAvailabilityMerger.php:16:7[0m]8;;\ - Class Roster\Services\Core\AvailabilityMerger is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityMerger[0m implements AvailabilityMergerInterface


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L18\src/Services/Core/[1;31mAvailabilityMerger.php:18:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AvailabilityMerger::__construct (see https://psalm.dev/087)
    public function [97;41m__construct[0m(


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L30\src/Services/Core/[1;31mAvailabilityMerger.php:30:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityMerger::mergewithadjacent should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Merge new availability data with adjacent existing ones.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    [97;41mpublic function mergeWithAdjacent(array $data, object $schedulable): array[0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L30\src/Services/Core/[1;31mAvailabilityMerger.php:30:59[0m]8;;\ - Argument 2 of Roster\Services\Core\AvailabilityMerger::mergeWithAdjacent has wrong name $schedulable, expecting $model as defined by Roster\Contracts\Services\AvailabilityMergerInterface::mergeWithAdjacent (see https://psalm.dev/230)
    public function mergeWithAdjacent(array $data, object [97;41m$schedulable[0m): array


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L47\src/Services/Core/[1;31mAvailabilityMerger.php:47:38[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
                    $idsToDelete[] = [30;47m$adjacentAvailability->id[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L67\src/Services/Core/[1;31mAvailabilityMerger.php:67:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityMerger::findadjacentavailabilities should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find adjacent availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    [97;41mpublic function findAdjacentAvailabilities(array $data, object $schedulable): Collection[0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L67\src/Services/Core/[1;31mAvailabilityMerger.php:67:68[0m]8;;\ - Argument 2 of Roster\Services\Core\AvailabilityMerger::findAdjacentAvailabilities has wrong name $schedulable, expecting $model as defined by Roster\Contracts\Services\AvailabilityMergerInterface::findAdjacentAvailabilities (see https://psalm.dev/230)
    public function findAdjacentAvailabilities(array $data, object [97;41m$schedulable[0m): Collection


INFO: ArgumentTypeCoercion - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L69\src/Services/Core/[1;31mAvailabilityMerger.php:69:85[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findAdjacentAvailabilities expects Illuminate\Database\Eloquent\Model, but parent type object provided (see https://psalm.dev/193)
        $availabilities = $this->availabilityRepository->findAdjacentAvailabilities([30;47m$schedulable[0m, $data);


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L88\src/Services/Core/[1;31mAvailabilityMerger.php:88:9[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/217)
        [30;47m$availability->schedulable_id[0m = $schedulable->id;


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L89\src/Services/Core/[1;31mAvailabilityMerger.php:89:9[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/217)
        [30;47m$availability->schedulable_type[0m = get_class($schedulable);


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L90\src/Services/Core/[1;31mAvailabilityMerger.php:90:9[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/217)
        [30;47m$availability->start_time[0m = $startTime;


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L91\src/Services/Core/[1;31mAvailabilityMerger.php:91:9[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/217)
        [30;47m$availability->end_time[0m = $endTime;


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L92\src/Services/Core/[1;31mAvailabilityMerger.php:92:9[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/217)
        [30;47m$availability->days[0m = $data['days'] ?? [];


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L93\src/Services/Core/[1;31mAvailabilityMerger.php:93:9[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/217)
        [30;47m$availability->type[0m = $data['type'] ?? null;


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L96\src/Services/Core/[1;31mAvailabilityMerger.php:96:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/217)
            [30;47m$availability->start_date[0m = Carbon::parse($data['start_date']);


INFO: UndefinedMagicPropertyAssignment - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityMerger.php#L100\src/Services/Core/[1;31mAvailabilityMerger.php:100:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/217)
            [30;47m$availability->end_date[0m = Carbon::parse($data['end_date']);


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L14\src/Services/Core/[1;31mAvailabilityValidator.php:14:7[0m]8;;\ - Class Roster\Services\Core\AvailabilityValidator is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityValidator[0m implements AvailabilityValidatorInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L23\src/Services/Core/[1;31mAvailabilityValidator.php:23:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityValidator::validatebasicdata should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Valider les données de base d'une disponibilité
     *
     * @param  array<string, mixed>  $data
     */
    [97;41mpublic function validateBasicData(array $data): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L57\src/Services/Core/[1;31mAvailabilityValidator.php:57:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityValidator::hasoverlapping should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Vérifier s'il y a un chevauchement avec des disponibilités existantes
     * Vérifie toujours les chevauchements, quel que soit le type
     *
     * @param  array<string, mixed>  $data
     */
    [97;41mpublic function hasOverlapping([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L63\src/Services/Core/[1;31mAvailabilityValidator.php:63:18[0m]8;;\ - Magic method Roster\Models\Availability::where does not exist (see https://psalm.dev/219)
        $query = [30;47mAvailability::where('schedulable_id', $model->id)[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L63\src/Services/Core/[1;31mAvailabilityValidator.php:63:56[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        $query = Availability::where('schedulable_id', [30;47m$model->id[0m)


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L65\src/Services/Core/[1;31mAvailabilityValidator.php:65:31[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
            ->where(function ([30;47m$q[0m) use ($data): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L68\src/Services/Core/[1;31mAvailabilityValidator.php:68:41[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
                    $q->where(function ([30;47m$query[0m) use ($data): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L75\src/Services/Core/[1;31mAvailabilityValidator.php:75:31[0m]8;;\ - Parameter $q has no provided type (see https://psalm.dev/153)
            ->where(function ([30;47m$q[0m) use ($data): void {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L93\src/Services/Core/[1;31mAvailabilityValidator.php:93:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityValidator::overlaps should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Vérifier si deux plages se chevauchent
     */
    [97;41mpublic function overlaps([0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L101\src/Services/Core/[1;31mAvailabilityValidator.php:101:35[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        if (! $this->timeOverlaps([30;47m$availability->start_time[0m, $availability->end_time, $newStartTime, $newEndTime)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L101\src/Services/Core/[1;31mAvailabilityValidator.php:101:62[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        if (! $this->timeOverlaps($availability->start_time, [30;47m$availability->end_time[0m, $newStartTime, $newEndTime)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L107\src/Services/Core/[1;31mAvailabilityValidator.php:107:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
            [30;47m$availability->start_date[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L108\src/Services/Core/[1;31mAvailabilityValidator.php:108:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
            [30;47m$availability->end_date[0m,


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L117\src/Services/Core/[1;31mAvailabilityValidator.php:117:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityValidator::timeoverlaps should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Vérifier le chevauchement des plages horaires
     */
    [97;41mpublic function timeOverlaps([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L132\src/Services/Core/[1;31mAvailabilityValidator.php:132:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityValidator::areadjacent should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Valider si deux disponibilités sont adjacentes (se touchent)
     */
    [97;41mpublic function areAdjacent([0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L138\src/Services/Core/[1;31mAvailabilityValidator.php:138:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            [30;47m$first->schedulable_id[0m !== $second->schedulable_id ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L138\src/Services/Core/[1;31mAvailabilityValidator.php:138:40[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            $first->schedulable_id !== [30;47m$second->schedulable_id[0m ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L139\src/Services/Core/[1;31mAvailabilityValidator.php:139:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            [30;47m$first->schedulable_type[0m !== $second->schedulable_type


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L139\src/Services/Core/[1;31mAvailabilityValidator.php:139:42[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            $first->schedulable_type !== [30;47m$second->schedulable_type[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L145\src/Services/Core/[1;31mAvailabilityValidator.php:145:39[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
        $commonDays = array_intersect([30;47m$first->days[0m, $second->days);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L145\src/Services/Core/[1;31mAvailabilityValidator.php:145:53[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
        $commonDays = array_intersect($first->days, [30;47m$second->days[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L151\src/Services/Core/[1;31mAvailabilityValidator.php:151:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
        if ([30;47m$first->type[0m !== $second->type) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L151\src/Services/Core/[1;31mAvailabilityValidator.php:151:30[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
        if ($first->type !== [30;47m$second->type[0m) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L157\src/Services/Core/[1;31mAvailabilityValidator.php:157:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
            [30;47m$first->start_date[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L158\src/Services/Core/[1;31mAvailabilityValidator.php:158:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
            [30;47m$first->end_date[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L159\src/Services/Core/[1;31mAvailabilityValidator.php:159:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
            [30;47m$second->start_date[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L160\src/Services/Core/[1;31mAvailabilityValidator.php:160:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
            [30;47m$second->end_date[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L166\src/Services/Core/[1;31mAvailabilityValidator.php:166:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        if ([30;47m$first->end_time[0m->eq($second->start_time)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L166\src/Services/Core/[1;31mAvailabilityValidator.php:166:34[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        if ($first->end_time->eq([30;47m$second->start_time[0m)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L170\src/Services/Core/[1;31mAvailabilityValidator.php:170:23[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        return (bool) [30;47m$second->end_time[0m->eq($first->start_time);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L170\src/Services/Core/[1;31mAvailabilityValidator.php:170:45[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        return (bool) $second->end_time->eq([30;47m$first->start_time[0m);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L185\src/Services/Core/[1;31mAvailabilityValidator.php:185:5[0m]8;;\ - Method Roster\Services\Core\AvailabilityValidator::mergeadjacent should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Fusionner deux disponibilités adjacentes
     *
     * @return array{
     *     type: string,
     *     start_time: string,
     *     end_time: string,
     *     days: array<string>,
     *     start_date: string|null,
     *     end_date: string|null
     * }
     */
    [97;41mpublic function mergeAdjacent([0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L193\src/Services/Core/[1;31mAvailabilityValidator.php:193:26[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        $startTime = min([30;47m$first->start_time[0m->timestamp, $second->start_time->timestamp);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L193\src/Services/Core/[1;31mAvailabilityValidator.php:193:57[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        $startTime = min($first->start_time->timestamp, [30;47m$second->start_time[0m->timestamp);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L194\src/Services/Core/[1;31mAvailabilityValidator.php:194:24[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        $endTime = max([30;47m$first->end_time[0m->timestamp, $second->end_time->timestamp);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L194\src/Services/Core/[1;31mAvailabilityValidator.php:194:53[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        $endTime = max($first->end_time->timestamp, [30;47m$second->end_time[0m->timestamp);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L200\src/Services/Core/[1;31mAvailabilityValidator.php:200:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
        if ([30;47m$first->start_date[0m !== null || $second->start_date !== null) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L200\src/Services/Core/[1;31mAvailabilityValidator.php:200:44[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
        if ($first->start_date !== null || [30;47m$second->start_date[0m !== null) {


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L201\src/Services/Core/[1;31mAvailabilityValidator.php:201:27[0m]8;;\ - Operand of type mixed|null contains type mixed, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
            $firstStart = [30;47m$first->start_date[0m ? $first->start_date->timestamp : PHP_INT_MAX;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L202\src/Services/Core/[1;31mAvailabilityValidator.php:202:28[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_date is not defined (see https://psalm.dev/218)
            $secondStart = [30;47m$second->start_date[0m ? $second->start_date->timestamp : PHP_INT_MAX;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L206\src/Services/Core/[1;31mAvailabilityValidator.php:206:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
        if ([30;47m$first->end_date[0m !== null || $second->end_date !== null) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L206\src/Services/Core/[1;31mAvailabilityValidator.php:206:42[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
        if ($first->end_date !== null || [30;47m$second->end_date[0m !== null) {


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L207\src/Services/Core/[1;31mAvailabilityValidator.php:207:25[0m]8;;\ - Operand of type mixed|null contains type mixed, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
            $firstEnd = [30;47m$first->end_date[0m ? $first->end_date->timestamp : PHP_INT_MIN;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L208\src/Services/Core/[1;31mAvailabilityValidator.php:208:26[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_date is not defined (see https://psalm.dev/218)
            $secondEnd = [30;47m$second->end_date[0m ? $second->end_date->timestamp : PHP_INT_MIN;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L213\src/Services/Core/[1;31mAvailabilityValidator.php:213:23[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
            'type' => [30;47m$first->type[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L216\src/Services/Core/[1;31mAvailabilityValidator.php:216:61[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
            'days' => array_values(array_unique(array_merge([30;47m$first->days[0m, $second->days))),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityValidator.php#L216\src/Services/Core/[1;31mAvailabilityValidator.php:216:75[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
            'days' => array_values(array_unique(array_merge($first->days, [30;47m$second->days[0m))),


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ResourcePublisherService.php#L14\src/Services/Core/[1;31mResourcePublisherService.php:14:7[0m]8;;\ - Class Roster\Services\Core\ResourcePublisherService is never used (see https://psalm.dev/075)
class [97;41mResourcePublisherService[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ResourcePublisherService.php#L14\src/Services/Core/[1;31mResourcePublisherService.php:14:7[0m]8;;\ - Class Roster\Services\Core\ResourcePublisherService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mResourcePublisherService[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L21\src/Services/Core/[1;31mSlotFinderService.php:21:7[0m]8;;\ - Class Roster\Services\Core\SlotFinderService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSlotFinderService[0m implements SlotFinderInterface


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L26\src/Services/Core/[1;31mSlotFinderService.php:26:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\SlotFinderService::__construct (see https://psalm.dev/087)
    public function [97;41m__construct[0m(


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L40\src/Services/Core/[1;31mSlotFinderService.php:40:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::findnextslot should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find the next available slot for a schedulable entity.
     *
     * @param Model $model Schedulable model instance
     * @param int $durationMinutes Required slot duration in minutes
     * @param string|null $type Optional availability type filter
     * @param bool $returnStartOnly Return only the start time if true
     * @return array|Carbon|null Slot details array, start time, or null if none found
     */
    [97;41mpublic function findNextSlot([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L95\src/Services/Core/[1;31mSlotFinderService.php:95:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::findslotsinperiod should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find available slots in a given period.
     *
     * @param Model $model Schedulable model instance
     * @param Carbon $startDate Period start date
     * @param Carbon $endDate Period end date
     * @param int $durationMinutes Slot duration in minutes
     * @param int $intervalMinutes Interval between slot starts in minutes
     * @param string|null $type Optional availability type filter
     * @return array<array<string, mixed>> Array of available slots
     * @throws ValidationException If validation fails
     */
    [97;41mpublic function findSlotsInPeriod([0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L123\src/Services/Core/[1;31mSlotFinderService.php:123:64[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
                $slotStart = $currentDate->copy()->setTimeFrom([30;47m$dailyAvailability->start_time[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L124\src/Services/Core/[1;31mSlotFinderService.php:124:62[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
                $slotEnd = $currentDate->copy()->setTimeFrom([30;47m$dailyAvailability->end_time[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L138\src/Services/Core/[1;31mSlotFinderService.php:138:39[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
                            'type' => [30;47m$dailyAvailability->type[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L139\src/Services/Core/[1;31mSlotFinderService.php:139:50[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
                            'availability_id' => [30;47m$dailyAvailability->id[0m,


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L164\src/Services/Core/[1;31mSlotFinderService.php:164:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::findfirstavailableperiod should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find the first available continuous period of specified duration.
     *
     * @param Model $model Schedulable model instance
     * @param Carbon $startDate Search start date
     * @param Carbon $endDate Search end date
     * @param int $durationMinutes Required period duration in minutes
     * @param string|null $type Optional availability type filter
     * @return array|null Period details or null if none found
     */
    [97;41mpublic function findFirstAvailablePeriod([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L218\src/Services/Core/[1;31mSlotFinderService.php:218:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::isperiodavailable should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if an entire time period is available without interruptions.
     *
     * @param Model $model Schedulable model instance
     * @param Carbon $start Period start datetime
     * @param Carbon $end Period end datetime
     * @param string|null $type Optional availability type filter
     * @return bool True if the entire period is available
     */
    [97;41mpublic function isPeriodAvailable([0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L242\src/Services/Core/[1;31mSlotFinderService.php:242:43[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
                    if ($type !== null && [30;47m$availability->type[0m !== $type) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L247\src/Services/Core/[1;31mSlotFinderService.php:247:25[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
                        [30;47m$availability->start_time[0m->format('H:i') <= $current->format('H:i') &&


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L248\src/Services/Core/[1;31mSlotFinderService.php:248:25[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
                        [30;47m$availability->end_time[0m->format('H:i') >= $slotEnd->format('H:i');


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L272\src/Services/Core/[1;31mSlotFinderService.php:272:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::hasanyavailabilityinperiod should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if any availability exists within a time period.
     *
     * @param Model $model Schedulable model instance
     * @param Carbon $start Period start datetime
     * @param Carbon $end Period end datetime
     * @param string|null $type Optional availability type filter
     * @return bool True if any availability exists in the period
     * @throws ValidationException If time range validation fails
     */
    [97;41mpublic function hasAnyAvailabilityInPeriod([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L318\src/Services/Core/[1;31mSlotFinderService.php:318:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::getavailableslotsfromimpediments should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Calculate available time slots by removing impediments from a time range.
     *
     * @param Carbon $start Start of the time range
     * @param Carbon $end End of the time range
     * @param Collection $impediments Collection of impediments
     * @return Collection<int, array<string, mixed>> Available time slots
     */
    [97;41mpublic function getAvailableSlotsFromImpediments([0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L332\src/Services/Core/[1;31mSlotFinderService.php:332:25[0m]8;;\ - Magic instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/218)
            $impStart = [30;47m$impediment->start_datetime[0m;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L333\src/Services/Core/[1;31mSlotFinderService.php:333:23[0m]8;;\ - Magic instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/218)
            $impEnd = [30;47m$impediment->end_datetime[0m;


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L336\src/Services/Core/[1;31mSlotFinderService.php:336:42[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array{end: mixed, start: mixed} provided (see https://psalm.dev/004)
                $findSlotsInPeriod->push([30;47m[
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ][0m);


INFO: PossiblyInvalidMethodCall - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L337\src/Services/Core/[1;31mSlotFinderService.php:337:46[0m]8;;\ - Cannot call method on possible string variable $currentTime (see https://psalm.dev/113)
                    'start' => $currentTime->[30;47mcopy[0m(),


INFO: PossiblyUndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L337\src/Services/Core/[1;31mSlotFinderService.php:337:46[0m]8;;\ - Method DateTimeInterface::copy does not exist (see https://psalm.dev/108)
                    'start' => $currentTime->[30;47mcopy[0m(),


INFO: PossiblyInvalidMethodCall - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L342\src/Services/Core/[1;31mSlotFinderService.php:342:42[0m]8;;\ - Cannot call method on possible string variable $currentTime (see https://psalm.dev/113)
            $currentTime = $currentTime->[30;47mgt[0m($impEnd) ? $currentTime : $impEnd;


INFO: PossiblyUndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L342\src/Services/Core/[1;31mSlotFinderService.php:342:42[0m]8;;\ - Method DateTimeInterface::gt does not exist (see https://psalm.dev/108)
            $currentTime = $currentTime->[30;47mgt[0m($impEnd) ? $currentTime : $impEnd;


INFO: PossiblyInvalidMethodCall - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L345\src/Services/Core/[1;31mSlotFinderService.php:345:27[0m]8;;\ - Cannot call method on possible string variable $currentTime (see https://psalm.dev/113)
        if ($currentTime->[30;47mlt[0m($end)) {


INFO: PossiblyUndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L345\src/Services/Core/[1;31mSlotFinderService.php:345:27[0m]8;;\ - Method DateTimeInterface::lt does not exist (see https://psalm.dev/108)
        if ($currentTime->[30;47mlt[0m($end)) {


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L346\src/Services/Core/[1;31mSlotFinderService.php:346:38[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array{end: Illuminate\Support\Carbon&static, start: mixed} provided (see https://psalm.dev/004)
            $findSlotsInPeriod->push([30;47m[
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ][0m);


INFO: PossiblyInvalidMethodCall - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L347\src/Services/Core/[1;31mSlotFinderService.php:347:42[0m]8;;\ - Cannot call method on possible string variable $currentTime (see https://psalm.dev/113)
                'start' => $currentTime->[30;47mcopy[0m(),


INFO: PossiblyUndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L347\src/Services/Core/[1;31mSlotFinderService.php:347:42[0m]8;;\ - Method DateTimeInterface::copy does not exist (see https://psalm.dev/108)
                'start' => $currentTime->[30;47mcopy[0m(),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L386\src/Services/Core/[1;31mSlotFinderService.php:386:49[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        $slotStart = $date->copy()->setTimeFrom([30;47m$availability->start_time[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L387\src/Services/Core/[1;31mSlotFinderService.php:387:47[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        $slotEnd = $date->copy()->setTimeFrom([30;47m$availability->end_time[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L408\src/Services/Core/[1;31mSlotFinderService.php:408:42[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
                    'availability_id' => [30;47m$availability->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L409\src/Services/Core/[1;31mSlotFinderService.php:409:31[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
                    'type' => [30;47m$availability->type[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L433\src/Services/Core/[1;31mSlotFinderService.php:433:35[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedules is not defined (see https://psalm.dev/218)
        $hasOverlappingSchedule = [30;47m$availability->schedules[0m->contains(


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L434\src/Services/Core/[1;31mSlotFinderService.php:434:16[0m]8;;\ - Parameter $schedule has no provided type (see https://psalm.dev/153)
            fn([30;47m$schedule[0m): bool => $schedule->overlapsWith($start, $end)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L437\src/Services/Core/[1;31mSlotFinderService.php:437:38[0m]8;;\ - Magic instance property Roster\Models\Availability::$impediments is not defined (see https://psalm.dev/218)
        $hasOverlappingImpediments = [30;47m$availability->impediments[0m->contains(


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L438\src/Services/Core/[1;31mSlotFinderService.php:438:16[0m]8;;\ - Parameter $impediment has no provided type (see https://psalm.dev/153)
            fn([30;47m$impediment[0m): bool => $impediment->overlapsWith($start, $end)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L463\src/Services/Core/[1;31mSlotFinderService.php:463:56[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        $slotStart = $currentDate->copy()->setTimeFrom([30;47m$availability->start_time[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L464\src/Services/Core/[1;31mSlotFinderService.php:464:54[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        $slotEnd = $currentDate->copy()->setTimeFrom([30;47m$availability->end_time[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L482\src/Services/Core/[1;31mSlotFinderService.php:482:42[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
                    'availability_id' => [30;47m$availability->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L483\src/Services/Core/[1;31mSlotFinderService.php:483:31[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
                    'type' => [30;47m$availability->type[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L508\src/Services/Core/[1;31mSlotFinderService.php:508:56[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
        $slotStart = $currentDate->copy()->setTimeFrom([30;47m$availability->start_time[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L509\src/Services/Core/[1;31mSlotFinderService.php:509:54[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
        $slotEnd = $currentDate->copy()->setTimeFrom([30;47m$availability->end_time[0m);


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L13\src/Services/Core/[1;31mValidationService.php:13:7[0m]8;;\ - Class Roster\Services\Core\ValidationService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mValidationService[0m implements ValidationServiceInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L18\src/Services/Core/[1;31mValidationService.php:18:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::validatetimerange should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Validate time range with proper context.
     */
    [97;41mpublic function validateTimeRange([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L31\src/Services/Core/[1;31mValidationService.php:31:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::validatedurationandinterval should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validateDurationAndInterval(int $durationMinutes, int $intervalMinutes): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L48\src/Services/Core/[1;31mValidationService.php:48:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::validatefuturedate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Validate that a date is in the future.
     */
    [97;41mpublic function validateFutureDate(Carbon $date): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L58\src/Services/Core/[1;31mValidationService.php:58:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::validateminimumduration should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Validate minimum duration.
     */
    [97;41mpublic function validateMinimumDuration([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L90\src/Services/Core/[1;31mValidationService.php:90:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::validaterequiredfields should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Validate that required fields exist.
     *
     * @param array<string, mixed> $data
     * @param array<string> $requiredFields
     */
    [97;41mpublic function validateRequiredFields(array $data, array $requiredFields): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L108\src/Services/Core/[1;31mValidationService.php:108:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::parseandvalidatedatetimerange should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Parse and validate datetime from array.
     *
     * @param array<string, mixed> $data
     * @return array{start: Carbon, end: Carbon}
     */
    [97;41mpublic function parseAndValidateDateTimeRange([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L129\src/Services/Core/[1;31mValidationService.php:129:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::parseandvalidatetimerange should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Parse and validate time from array.
     *
     * @param array<string, mixed> $data
     * @return array{start: Carbon, end: Carbon}
     */
    [97;41mpublic function parseAndValidateTimeRange([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ValidationService.php#L147\src/Services/Core/[1;31mValidationService.php:147:5[0m]8;;\ - Method Roster\Services\Core\ValidationService::validatetimezone should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Validate timezone.
     */
    [97;41mpublic function validateTimezone(string $timezone): bool[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L28\src/Services/[1;31mImpedimentService.php:28:7[0m]8;;\ - Class Roster\Services\ImpedimentService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpedimentService[0m extends AbstractSchedulableService


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L52\src/Services/[1;31mImpedimentService.php:52:5[0m]8;;\ - Method Roster\Services\ImpedimentService::validatedurationhook should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateDurationHook([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L68\src/Services/[1;31mImpedimentService.php:68:5[0m]8;;\ - Method Roster\Services\ImpedimentService::validatemaxdayshook should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateMaxDaysHook(string $operation, int $maxDays): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L82\src/Services/[1;31mImpedimentService.php:82:5[0m]8;;\ - Method Roster\Services\ImpedimentService::getvalidationservice should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function getValidationService(): ValidationServiceInterface[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L87\src/Services/[1;31mImpedimentService.php:87:5[0m]8;;\ - Method Roster\Services\ImpedimentService::validatebeforecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateBeforeCreate(): void[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L105\src/Services/[1;31mImpedimentService.php:105:41[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
        $this->data['schedulable_id'] = [30;47m$this->schedulable->id[0m;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L105\src/Services/[1;31mImpedimentService.php:105:41[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        $this->data['schedulable_id'] = [30;47m$this->schedulable->id[0m;


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L106\src/Services/[1;31mImpedimentService.php:106:53[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
        $this->data['schedulable_type'] = get_class([30;47m$this->schedulable[0m);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L109\src/Services/[1;31mImpedimentService.php:109:5[0m]8;;\ - Method Roster\Services\ImpedimentService::processbeforecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function processBeforeCreate(): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L116\src/Services/[1;31mImpedimentService.php:116:5[0m]8;;\ - Method Roster\Services\ImpedimentService::executecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function executeCreate(): Impediment[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L118\src/Services/[1;31mImpedimentService.php:118:16[0m]8;;\ - Magic method Roster\Models\Impediment::create does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::create($this->data)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L121\src/Services/[1;31mImpedimentService.php:121:5[0m]8;;\ - Method Roster\Services\ImpedimentService::validatebeforeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateBeforeUpdate(int $id): void[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L133\src/Services/[1;31mImpedimentService.php:133:27[0m]8;;\ - Cannot get property on possibly null variable $this->currentImpediment of type Roster\Models\Impediment|null (see https://psalm.dev/082)
        $availabilityId = [30;47m$this->currentImpediment->availability_id[0m;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L133\src/Services/[1;31mImpedimentService.php:133:27[0m]8;;\ - Magic instance property Roster\Models\Impediment::$availability_id is not defined (see https://psalm.dev/218)
        $availabilityId = [30;47m$this->currentImpediment->availability_id[0m;


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L139\src/Services/[1;31mImpedimentService.php:139:41[0m]8;;\ - Cannot get property on possibly null variable $this->currentImpediment of type Roster\Models\Impediment|null (see https://psalm.dev/082)
                    'start_datetime' => [30;47m$this->currentImpediment->start_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L139\src/Services/[1;31mImpedimentService.php:139:41[0m]8;;\ - Magic instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/218)
                    'start_datetime' => [30;47m$this->currentImpediment->start_datetime[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L140\src/Services/[1;31mImpedimentService.php:140:39[0m]8;;\ - Cannot get property on possibly null variable $this->currentImpediment of type Roster\Models\Impediment|null (see https://psalm.dev/082)
                    'end_datetime' => [30;47m$this->currentImpediment->end_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L140\src/Services/[1;31mImpedimentService.php:140:39[0m]8;;\ - Magic instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/218)
                    'end_datetime' => [30;47m$this->currentImpediment->end_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L155\src/Services/[1;31mImpedimentService.php:155:31[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
            $availabilityId = [30;47m$newAvailability->id[0m;


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L159\src/Services/[1;31mImpedimentService.php:159:41[0m]8;;\ - Cannot get property on possibly null variable $this->currentImpediment of type Roster\Models\Impediment|null (see https://psalm.dev/082)
                    'start_datetime' => [30;47m$this->currentImpediment->start_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L159\src/Services/[1;31mImpedimentService.php:159:41[0m]8;;\ - Magic instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/218)
                    'start_datetime' => [30;47m$this->currentImpediment->start_datetime[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L160\src/Services/[1;31mImpedimentService.php:160:39[0m]8;;\ - Cannot get property on possibly null variable $this->currentImpediment of type Roster\Models\Impediment|null (see https://psalm.dev/082)
                    'end_datetime' => [30;47m$this->currentImpediment->end_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L160\src/Services/[1;31mImpedimentService.php:160:39[0m]8;;\ - Magic instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/218)
                    'end_datetime' => [30;47m$this->currentImpediment->end_datetime[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L173\src/Services/[1;31mImpedimentService.php:173:37[0m]8;;\ - Cannot get property on possibly null variable $this->currentImpediment of type Roster\Models\Impediment|null (see https://psalm.dev/082)
                'start_datetime' => [30;47m$this->currentImpediment->start_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L173\src/Services/[1;31mImpedimentService.php:173:37[0m]8;;\ - Magic instance property Roster\Models\Impediment::$start_datetime is not defined (see https://psalm.dev/218)
                'start_datetime' => [30;47m$this->currentImpediment->start_datetime[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L174\src/Services/[1;31mImpedimentService.php:174:35[0m]8;;\ - Cannot get property on possibly null variable $this->currentImpediment of type Roster\Models\Impediment|null (see https://psalm.dev/082)
                'end_datetime' => [30;47m$this->currentImpediment->end_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L174\src/Services/[1;31mImpedimentService.php:174:35[0m]8;;\ - Magic instance property Roster\Models\Impediment::$end_datetime is not defined (see https://psalm.dev/218)
                'end_datetime' => [30;47m$this->currentImpediment->end_datetime[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L180\src/Services/[1;31mImpedimentService.php:180:72[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments cannot be null, possibly null value provided (see https://psalm.dev/078)
            if ($this->impedimentRepository->hasOverlappingImpediments([30;47m$availabilityId[0m, $start, $end, $id)) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L186\src/Services/[1;31mImpedimentService.php:186:5[0m]8;;\ - Method Roster\Services\ImpedimentService::processbeforeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function processBeforeUpdate(int $id): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L191\src/Services/[1;31mImpedimentService.php:191:5[0m]8;;\ - Method Roster\Services\ImpedimentService::executeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function executeUpdate(int $id): bool[0m


INFO: PossiblyNullReference - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L193\src/Services/[1;31mImpedimentService.php:193:42[0m]8;;\ - Cannot call method update on possibly null value (see https://psalm.dev/083)
        return $this->currentImpediment->[30;47mupdate[0m($this->data);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L206\src/Services/[1;31mImpedimentService.php:206:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::create (see https://psalm.dev/087)
    public function [97;41mcreate[0m($availabilityOrData, ?array $data = null): Impediment


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L240\src/Services/[1;31mImpedimentService.php:240:42[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
        $this->data['availability_id'] = [30;47m$availability->id[0m;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L272\src/Services/[1;31mImpedimentService.php:272:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            [30;47m$availability->schedulable_id[0m !== $this->schedulable->id ||


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L272\src/Services/[1;31mImpedimentService.php:272:47[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            $availability->schedulable_id !== [30;47m$this->schedulable->id[0m ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L272\src/Services/[1;31mImpedimentService.php:272:47[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            $availability->schedulable_id !== [30;47m$this->schedulable->id[0m ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L273\src/Services/[1;31mImpedimentService.php:273:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            [30;47m$availability->schedulable_type[0m !== get_class($this->schedulable)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L273\src/Services/[1;31mImpedimentService.php:273:59[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            $availability->schedulable_type !== get_class([30;47m$this->schedulable[0m)


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L285\src/Services/[1;31mImpedimentService.php:285:16[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::where('schedulable_id', $this->schedulable->id)[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L285\src/Services/[1;31mImpedimentService.php:285:52[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
        return Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L285\src/Services/[1;31mImpedimentService.php:285:52[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        return Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L286\src/Services/[1;31mImpedimentService.php:286:51[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            ->where('schedulable_type', get_class([30;47m$this->schedulable[0m))


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L290\src/Services/[1;31mImpedimentService.php:290:21[0m]8;;\ - Cannot find explicit calls to method Roster\Services\ImpedimentService::delete (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mdelete[0m(int $id): bool


INFO: InvalidNullableReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L290\src/Services/[1;31mImpedimentService.php:290:38[0m]8;;\ - The declared return type 'bool' for Roster\Services\ImpedimentService::delete is not nullable, but 'bool|null' contains null (see https://psalm.dev/144)
    public function delete(int $id): [30;47mbool[0m


INFO: NullableReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L299\src/Services/[1;31mImpedimentService.php:299:16[0m]8;;\ - The declared return type 'bool' for Roster\Services\ImpedimentService::delete is not nullable, but the function returns 'bool|null' (see https://psalm.dev/139)
        return [30;47m$impediment->delete()[0m;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L302\src/Services/[1;31mImpedimentService.php:302:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::between (see https://psalm.dev/087)
    public function [97;41mbetween[0m(Carbon $start, Carbon $end): Collection


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L314\src/Services/[1;31mImpedimentService.php:314:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::isTimeSlotBlocked (see https://psalm.dev/087)
    public function [97;41misTimeSlotBlocked[0m(Carbon $start, Carbon $end, ?string $type = null): bool


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L319\src/Services/[1;31mImpedimentService.php:319:72[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
        $availability = $this->availabilityRepository->findForTimeSlot([30;47m$this->schedulable[0m, $start, $end, $type);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L325\src/Services/[1;31mImpedimentService.php:325:71[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
        return $this->impedimentRepository->hasOverlappingImpediments([30;47m$availability->id[0m, $start, $end);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L328\src/Services/[1;31mImpedimentService.php:328:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::getAvailableTimeSlots (see https://psalm.dev/087)
    public function [97;41mgetAvailableTimeSlots[0m(Carbon $start, Carbon $end, ?string $type = null): Collection


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L333\src/Services/[1;31mImpedimentService.php:333:72[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
        $availability = $this->availabilityRepository->findForTimeSlot([30;47m$this->schedulable[0m, $start, $end, $type);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L339\src/Services/[1;31mImpedimentService.php:339:69[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
        $impediments = $this->impedimentRepository->findForTimeSlot([30;47m$availability->id[0m, $start, $end);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L342\src/Services/[1;31mImpedimentService.php:342:83[0m]8;;\ - Argument 3 of Roster\Contracts\Services\SlotFinderInterface::getAvailableSlotsFromImpediments expects Illuminate\Support\Collection<array-key, mixed>, but Illuminate\Support\Collection<int, object> provided (see https://psalm.dev/004)
        return $slotFinderService->getAvailableSlotsFromImpediments($start, $end, [30;47m$impediments[0m);


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L359\src/Services/[1;31mImpedimentService.php:359:63[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityRepository->findForTimeSlot([30;47m$this->schedulable[0m, $start, $end);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L362\src/Services/[1;31mImpedimentService.php:362:5[0m]8;;\ - Method Roster\Services\ImpedimentService::applyfilters should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function applyFilters(): Builder[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L364\src/Services/[1;31mImpedimentService.php:364:18[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        $query = [30;47mImpediment::where('schedulable_id', $this->schedulable->id)[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L364\src/Services/[1;31mImpedimentService.php:364:54[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
        $query = Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L364\src/Services/[1;31mImpedimentService.php:364:54[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        $query = Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L365\src/Services/[1;31mImpedimentService.php:365:51[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            ->where('schedulable_type', get_class([30;47m$this->schedulable[0m));


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L27\src/Services/[1;31mScheduleService.php:27:7[0m]8;;\ - Class Roster\Services\ScheduleService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mScheduleService[0m extends AbstractSchedulableService


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L59\src/Services/[1;31mScheduleService.php:59:5[0m]8;;\ - Method Roster\Services\ScheduleService::validatedurationhook should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateDurationHook([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L75\src/Services/[1;31mScheduleService.php:75:5[0m]8;;\ - Method Roster\Services\ScheduleService::validatemaxdayshook should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateMaxDaysHook(string $operation, int $maxDays): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L80\src/Services/[1;31mScheduleService.php:80:5[0m]8;;\ - Method Roster\Services\ScheduleService::getvalidationservice should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function getValidationService(): ValidationServiceInterface[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L85\src/Services/[1;31mScheduleService.php:85:5[0m]8;;\ - Method Roster\Services\ScheduleService::validatebeforecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateBeforeCreate(): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L115\src/Services/[1;31mScheduleService.php:115:5[0m]8;;\ - Method Roster\Services\ScheduleService::processbeforecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function processBeforeCreate(): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L122\src/Services/[1;31mScheduleService.php:122:5[0m]8;;\ - Method Roster\Services\ScheduleService::executecreate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function executeCreate(): Schedule[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L127\src/Services/[1;31mScheduleService.php:127:5[0m]8;;\ - Method Roster\Services\ScheduleService::validatebeforeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function validateBeforeUpdate(int $id): void[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L143\src/Services/[1;31mScheduleService.php:143:41[0m]8;;\ - Cannot get property on possibly null variable $this->currentSchedule of type Roster\Models\Schedule|null (see https://psalm.dev/082)
                    'start_datetime' => [30;47m$this->currentSchedule->start_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L143\src/Services/[1;31mScheduleService.php:143:41[0m]8;;\ - Magic instance property Roster\Models\Schedule::$start_datetime is not defined (see https://psalm.dev/218)
                    'start_datetime' => [30;47m$this->currentSchedule->start_datetime[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L144\src/Services/[1;31mScheduleService.php:144:39[0m]8;;\ - Cannot get property on possibly null variable $this->currentSchedule of type Roster\Models\Schedule|null (see https://psalm.dev/082)
                    'end_datetime' => [30;47m$this->currentSchedule->end_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L144\src/Services/[1;31mScheduleService.php:144:39[0m]8;;\ - Magic instance property Roster\Models\Schedule::$end_datetime is not defined (see https://psalm.dev/218)
                    'end_datetime' => [30;47m$this->currentSchedule->end_datetime[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L161\src/Services/[1;31mScheduleService.php:161:41[0m]8;;\ - Cannot get property on possibly null variable $this->currentSchedule of type Roster\Models\Schedule|null (see https://psalm.dev/082)
                    'start_datetime' => [30;47m$this->currentSchedule->start_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L161\src/Services/[1;31mScheduleService.php:161:41[0m]8;;\ - Magic instance property Roster\Models\Schedule::$start_datetime is not defined (see https://psalm.dev/218)
                    'start_datetime' => [30;47m$this->currentSchedule->start_datetime[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L162\src/Services/[1;31mScheduleService.php:162:39[0m]8;;\ - Cannot get property on possibly null variable $this->currentSchedule of type Roster\Models\Schedule|null (see https://psalm.dev/082)
                    'end_datetime' => [30;47m$this->currentSchedule->end_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L162\src/Services/[1;31mScheduleService.php:162:39[0m]8;;\ - Magic instance property Roster\Models\Schedule::$end_datetime is not defined (see https://psalm.dev/218)
                    'end_datetime' => [30;47m$this->currentSchedule->end_datetime[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L165\src/Services/[1;31mScheduleService.php:165:67[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
            if ($this->scheduleRepository->hasOverlappingSchedule([30;47m$newAvailability->id[0m, $start, $end, $id)) {


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L173\src/Services/[1;31mScheduleService.php:173:42[0m]8;;\ - Cannot get property on possibly null variable $this->currentSchedule of type Roster\Models\Schedule|null (see https://psalm.dev/082)
            if ($newAvailability->id !== [30;47m$this->currentSchedule->availability_id[0m) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L173\src/Services/[1;31mScheduleService.php:173:42[0m]8;;\ - Magic instance property Roster\Models\Schedule::$availability_id is not defined (see https://psalm.dev/218)
            if ($newAvailability->id !== [30;47m$this->currentSchedule->availability_id[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L179\src/Services/[1;31mScheduleService.php:179:5[0m]8;;\ - Method Roster\Services\ScheduleService::processbeforeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function processBeforeUpdate(int $id): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L184\src/Services/[1;31mScheduleService.php:184:5[0m]8;;\ - Method Roster\Services\ScheduleService::executeupdate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function executeUpdate(int $id): bool[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L199\src/Services/[1;31mScheduleService.php:199:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::create (see https://psalm.dev/087)
    public function [97;41mcreate[0m($availabilityOrData, ?array $data = null): Schedule


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L233\src/Services/[1;31mScheduleService.php:233:42[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
        $this->data['availability_id'] = [30;47m$availability->id[0m;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L265\src/Services/[1;31mScheduleService.php:265:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            [30;47m$availability->schedulable_id[0m !== $this->schedulable->id ||


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L265\src/Services/[1;31mScheduleService.php:265:47[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            $availability->schedulable_id !== [30;47m$this->schedulable->id[0m ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L265\src/Services/[1;31mScheduleService.php:265:47[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            $availability->schedulable_id !== [30;47m$this->schedulable->id[0m ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L266\src/Services/[1;31mScheduleService.php:266:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            [30;47m$availability->schedulable_type[0m !== get_class($this->schedulable)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L266\src/Services/[1;31mScheduleService.php:266:59[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            $availability->schedulable_type !== get_class([30;47m$this->schedulable[0m)


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L280\src/Services/[1;31mScheduleService.php:280:21[0m]8;;\ - Cannot find explicit calls to method Roster\Services\ScheduleService::delete (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mdelete[0m(int $id): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L292\src/Services/[1;31mScheduleService.php:292:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::between (see https://psalm.dev/087)
    public function [97;41mbetween[0m(Carbon $start, Carbon $end): Collection


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L298\src/Services/[1;31mScheduleService.php:298:13[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            [30;47m$this->schedulable->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L298\src/Services/[1;31mScheduleService.php:298:13[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L298\src/Services/[1;31mScheduleService.php:298:13[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\ScheduleRepositoryInterface::getForDateRange cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L299\src/Services/[1;31mScheduleService.php:299:23[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            get_class([30;47m$this->schedulable[0m),


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L306\src/Services/[1;31mScheduleService.php:306:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::isTimeSlotAvailable (see https://psalm.dev/087)
    public function [97;41misTimeSlotAvailable[0m(Carbon $start, Carbon $end, ?string $type = null): bool


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L312\src/Services/[1;31mScheduleService.php:312:13[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlotWithPartialOverlaps cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L319\src/Services/[1;31mScheduleService.php:319:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$has_overlapping_schedules is not defined (see https://psalm.dev/218)
            && ![30;47m$availability->has_overlapping_schedules[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L320\src/Services/[1;31mScheduleService.php:320:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$has_overlapping_impediments is not defined (see https://psalm.dev/218)
            && ![30;47m$availability->has_overlapping_impediments[0m;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L323\src/Services/[1;31mScheduleService.php:323:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::isPeriodAvailable (see https://psalm.dev/087)
    public function [97;41misPeriodAvailable[0m(Carbon $start, Carbon $end, ?string $type = null): bool


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L328\src/Services/[1;31mScheduleService.php:328:53[0m]8;;\ - Argument 1 of Roster\Contracts\Services\SlotFinderInterface::isPeriodAvailable cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->slotFinder->isPeriodAvailable([30;47m$this->schedulable[0m, $start, $end, $type);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L331\src/Services/[1;31mScheduleService.php:331:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::findFirstAvailablePeriod (see https://psalm.dev/087)
    public function [97;41mfindFirstAvailablePeriod[0m(


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L348\src/Services/[1;31mScheduleService.php:348:13[0m]8;;\ - Argument 1 of Roster\Contracts\Services\SlotFinderInterface::findFirstAvailablePeriod cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable[0m,


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L356\src/Services/[1;31mScheduleService.php:356:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::findNextAvailableSlot (see https://psalm.dev/087)
    public function [97;41mfindNextAvailableSlot[0m(int $durationMinutes, ?string $type = null): ?array


INFO: InvalidReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L367\src/Services/[1;31mScheduleService.php:367:16[0m]8;;\ - The inferred type 'Illuminate\Support\Carbon|array<array-key, mixed>|null' does not match the declared return type 'array<array-key, mixed>|null' for Roster\Services\ScheduleService::findNextAvailableSlot (see https://psalm.dev/128)
        return [30;47m$this->slotFinder->findNextSlot($this->schedulable, $durationMinutes, $type)[0m;


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L367\src/Services/[1;31mScheduleService.php:367:48[0m]8;;\ - Argument 1 of Roster\Contracts\Services\SlotFinderInterface::findNextSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->slotFinder->findNextSlot([30;47m$this->schedulable[0m, $durationMinutes, $type);


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L382\src/Services/[1;31mScheduleService.php:382:63[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityRepository->findForTimeSlot([30;47m$this->schedulable[0m, $start, $end, $type);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L385\src/Services/[1;31mScheduleService.php:385:5[0m]8;;\ - Method Roster\Services\ScheduleService::applyfilters should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function applyFilters(): Builder[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L388\src/Services/[1;31mScheduleService.php:388:13[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            [30;47m$this->schedulable->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L388\src/Services/[1;31mScheduleService.php:388:13[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L388\src/Services/[1;31mScheduleService.php:388:13[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\ScheduleRepositoryInterface::applyFilters cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L389\src/Services/[1;31mScheduleService.php:389:23[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            get_class([30;47m$this->schedulable[0m),


INFO: PossiblyNullReference - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L33\src/Traits/[1;31mDateRangeOverlapTrait.php:33:36[0m]8;;\ - Cannot call method lte on possibly null value (see https://psalm.dev/083)
        return $effectiveNewStart->[30;47mlte[0m($effectiveExistingEnd) &&


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L33\src/Traits/[1;31mDateRangeOverlapTrait.php:33:40[0m]8;;\ - Argument 1 of Illuminate\Support\Carbon::lte cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $effectiveNewStart->lte([30;47m$effectiveExistingEnd[0m) &&


INFO: PossiblyNullReference - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L34\src/Traits/[1;31mDateRangeOverlapTrait.php:34:31[0m]8;;\ - Cannot call method gte on possibly null value (see https://psalm.dev/083)
            $effectiveNewEnd->[30;47mgte[0m($effectiveExistingStart);


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L34\src/Traits/[1;31mDateRangeOverlapTrait.php:34:35[0m]8;;\ - Argument 1 of Illuminate\Support\Carbon::gte cannot be null, possibly null value provided (see https://psalm.dev/078)
            $effectiveNewEnd->gte([30;47m$effectiveExistingStart[0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/EnumValues.php#L17\src/Traits/[1;31mEnumValues.php:17:28[0m]8;;\ - Cannot find any calls to method Roster\Enums\ScheduleStatus::values (see https://psalm.dev/087)
    public static function [97;41mvalues[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L21\src/Traits/[1;31mFilterableTrait.php:21:24[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::applyDateFilters (see https://psalm.dev/087)
    protected function [97;41mapplyDateFilters[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L21\src/Traits/[1;31mFilterableTrait.php:21:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::applyDateFilters (see https://psalm.dev/087)
    protected function [97;41mapplyDateFilters[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L40\src/Traits/[1;31mFilterableTrait.php:40:24[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::applyTypeFilter (see https://psalm.dev/087)
    protected function [97;41mapplyTypeFilter[0m(Builder $builder, string $relation = ''): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L40\src/Traits/[1;31mFilterableTrait.php:40:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::applyTypeFilter (see https://psalm.dev/087)
    protected function [97;41mapplyTypeFilter[0m(Builder $builder, string $relation = ''): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L60\src/Traits/[1;31mFilterableTrait.php:60:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::applyDayFilter (see https://psalm.dev/087)
    protected function [97;41mapplyDayFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L60\src/Traits/[1;31mFilterableTrait.php:60:24[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::applyDayFilter (see https://psalm.dev/087)
    protected function [97;41mapplyDayFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L60\src/Traits/[1;31mFilterableTrait.php:60:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::applyDayFilter (see https://psalm.dev/087)
    protected function [97;41mapplyDayFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L72\src/Traits/[1;31mFilterableTrait.php:72:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::applyStatusFilter (see https://psalm.dev/087)
    protected function [97;41mapplyStatusFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L72\src/Traits/[1;31mFilterableTrait.php:72:24[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::applyStatusFilter (see https://psalm.dev/087)
    protected function [97;41mapplyStatusFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L72\src/Traits/[1;31mFilterableTrait.php:72:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::applyStatusFilter (see https://psalm.dev/087)
    protected function [97;41mapplyStatusFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L84\src/Traits/[1;31mFilterableTrait.php:84:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::applyReasonFilter (see https://psalm.dev/087)
    protected function [97;41mapplyReasonFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L84\src/Traits/[1;31mFilterableTrait.php:84:24[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::applyReasonFilter (see https://psalm.dev/087)
    protected function [97;41mapplyReasonFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L84\src/Traits/[1;31mFilterableTrait.php:84:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::applyReasonFilter (see https://psalm.dev/087)
    protected function [97;41mapplyReasonFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L96\src/Traits/[1;31mFilterableTrait.php:96:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::applyAvailabilityIdFilter (see https://psalm.dev/087)
    protected function [97;41mapplyAvailabilityIdFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L96\src/Traits/[1;31mFilterableTrait.php:96:24[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::applyAvailabilityIdFilter (see https://psalm.dev/087)
    protected function [97;41mapplyAvailabilityIdFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L96\src/Traits/[1;31mFilterableTrait.php:96:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::applyAvailabilityIdFilter (see https://psalm.dev/087)
    protected function [97;41mapplyAvailabilityIdFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L108\src/Traits/[1;31mFilterableTrait.php:108:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::applySchedulableFilter (see https://psalm.dev/087)
    protected function [97;41mapplySchedulableFilter[0m(Builder $builder, ?Model $model = null): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L108\src/Traits/[1;31mFilterableTrait.php:108:24[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::applySchedulableFilter (see https://psalm.dev/087)
    protected function [97;41mapplySchedulableFilter[0m(Builder $builder, ?Model $model = null): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L108\src/Traits/[1;31mFilterableTrait.php:108:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::applySchedulableFilter (see https://psalm.dev/087)
    protected function [97;41mapplySchedulableFilter[0m(Builder $builder, ?Model $model = null): Builder


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L111\src/Traits/[1;31mFilterableTrait.php:111:47[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            $builder->where('schedulable_id', [30;47m$model->id[0m)


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L121\src/Traits/[1;31mFilterableTrait.php:121:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::whereStartDate (see https://psalm.dev/087)
    public function [97;41mwhereStartDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L121\src/Traits/[1;31mFilterableTrait.php:121:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::whereStartDate (see https://psalm.dev/087)
    public function [97;41mwhereStartDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L121\src/Traits/[1;31mFilterableTrait.php:121:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::whereStartDate (see https://psalm.dev/087)
    public function [97;41mwhereStartDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L130\src/Traits/[1;31mFilterableTrait.php:130:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::whereEndDate (see https://psalm.dev/087)
    public function [97;41mwhereEndDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L130\src/Traits/[1;31mFilterableTrait.php:130:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::whereEndDate (see https://psalm.dev/087)
    public function [97;41mwhereEndDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L130\src/Traits/[1;31mFilterableTrait.php:130:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::whereEndDate (see https://psalm.dev/087)
    public function [97;41mwhereEndDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L139\src/Traits/[1;31mFilterableTrait.php:139:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::whereStatus (see https://psalm.dev/087)
    public function [97;41mwhereStatus[0m(string $status): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L139\src/Traits/[1;31mFilterableTrait.php:139:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::whereStatus (see https://psalm.dev/087)
    public function [97;41mwhereStatus[0m(string $status): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L139\src/Traits/[1;31mFilterableTrait.php:139:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::whereStatus (see https://psalm.dev/087)
    public function [97;41mwhereStatus[0m(string $status): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L148\src/Traits/[1;31mFilterableTrait.php:148:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::whereReason (see https://psalm.dev/087)
    public function [97;41mwhereReason[0m(string $reason): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L148\src/Traits/[1;31mFilterableTrait.php:148:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::whereReason (see https://psalm.dev/087)
    public function [97;41mwhereReason[0m(string $reason): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L148\src/Traits/[1;31mFilterableTrait.php:148:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::whereReason (see https://psalm.dev/087)
    public function [97;41mwhereReason[0m(string $reason): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L157\src/Traits/[1;31mFilterableTrait.php:157:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::whereAvailabilityId (see https://psalm.dev/087)
    public function [97;41mwhereAvailabilityId[0m(int $availabilityId): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L157\src/Traits/[1;31mFilterableTrait.php:157:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::whereAvailabilityId (see https://psalm.dev/087)
    public function [97;41mwhereAvailabilityId[0m(int $availabilityId): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L157\src/Traits/[1;31mFilterableTrait.php:157:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::whereAvailabilityId (see https://psalm.dev/087)
    public function [97;41mwhereAvailabilityId[0m(int $availabilityId): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L166\src/Traits/[1;31mFilterableTrait.php:166:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::clearFilters (see https://psalm.dev/087)
    public function [97;41mclearFilters[0m(): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L166\src/Traits/[1;31mFilterableTrait.php:166:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::clearFilters (see https://psalm.dev/087)
    public function [97;41mclearFilters[0m(): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L166\src/Traits/[1;31mFilterableTrait.php:166:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::clearFilters (see https://psalm.dev/087)
    public function [97;41mclearFilters[0m(): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L177\src/Traits/[1;31mFilterableTrait.php:177:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::getFilters (see https://psalm.dev/087)
    public function [97;41mgetFilters[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L177\src/Traits/[1;31mFilterableTrait.php:177:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::getFilters (see https://psalm.dev/087)
    public function [97;41mgetFilters[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L177\src/Traits/[1;31mFilterableTrait.php:177:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::getFilters (see https://psalm.dev/087)
    public function [97;41mgetFilters[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L185\src/Traits/[1;31mFilterableTrait.php:185:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::hasFilter (see https://psalm.dev/087)
    public function [97;41mhasFilter[0m(string $key): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L185\src/Traits/[1;31mFilterableTrait.php:185:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::hasFilter (see https://psalm.dev/087)
    public function [97;41mhasFilter[0m(string $key): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L185\src/Traits/[1;31mFilterableTrait.php:185:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::hasFilter (see https://psalm.dev/087)
    public function [97;41mhasFilter[0m(string $key): bool


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L12\src/[1;31mhelpers.php:12:16[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        return [30;47m$path[0m ? 'config/' . $path : 'config';


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L25\src/[1;31mhelpers.php:25:16[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        return [30;47m$path[0m ? 'database/' . $path : 'database';


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L39\src/[1;31mhelpers.php:39:16[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        return [30;47m$path[0m ? $base . $path : $base;


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L53\src/[1;31mhelpers.php:53:16[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        return [30;47m$path[0m ? $base . '/' . $path : $base;


------------------------------
[0;31m260 errors[0m found
------------------------------
337 other issues found.
------------------------------
Psalm can automatically fix 216 issues.
Run Psalm again with 
[30;48;5;195m--alter --issues=MissingOverrideAttribute,InvalidNullableReturnType,PossiblyUnusedMethod,ClassMustBeFinal,MissingParamType --dry-run[0m
to see what it can fix.
------------------------------

Checks took 2.90 seconds and used 402.281MB of memory
Psalm was able to infer types for 87.9713% of the codebase
