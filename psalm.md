# Psalm Static Analysis Report
*Generated: mer. 24 déc. 2025 05:30:20 WAT*


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L12\src/Commands/[1;31mCacheRulesCommand.php:12:7[0m]8;;\ - Property Roster\Commands\CacheRulesCommand::$laravel is not defined in constructor of Roster\Commands\CacheRulesCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mCacheRulesCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L12\src/Commands/[1;31mCacheRulesCommand.php:12:7[0m]8;;\ - Property Roster\Commands\CacheRulesCommand::$name is not defined in constructor of Roster\Commands\CacheRulesCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mCacheRulesCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L12\src/Commands/[1;31mCacheRulesCommand.php:12:7[0m]8;;\ - Property Roster\Commands\CacheRulesCommand::$components is not defined in constructor of Roster\Commands\CacheRulesCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mCacheRulesCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L12\src/Commands/[1;31mCacheRulesCommand.php:12:7[0m]8;;\ - Property Roster\Commands\CacheRulesCommand::$input is not defined in constructor of Roster\Commands\CacheRulesCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mCacheRulesCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L12\src/Commands/[1;31mCacheRulesCommand.php:12:7[0m]8;;\ - Property Roster\Commands\CacheRulesCommand::$output is not defined in constructor of Roster\Commands\CacheRulesCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mCacheRulesCommand[0m extends Command


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L12\src/Commands/[1;31mCacheRulesCommand.php:12:7[0m]8;;\ - Class Roster\Commands\CacheRulesCommand is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mCacheRulesCommand[0m extends Command


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L21\src/Commands/[1;31mCacheRulesCommand.php:21:21[0m]8;;\ - Cannot find any calls to method Roster\Commands\CacheRulesCommand::handle (see https://psalm.dev/087)
    public function [97;41mhandle[0m(RuleScanner $scanner): int


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L25\src/Commands/[1;31mCacheRulesCommand.php:25:13[0m]8;;\ - Operand of type array<array-key, mixed>|bool|null|string contains types array<array-key, mixed>|string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$this->option('clear')[0m) {


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L29\src/Commands/[1;31mCacheRulesCommand.php:29:13[0m]8;;\ - Operand of type array<array-key, mixed>|bool|null|string contains types array<array-key, mixed>|string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$this->option('show')[0m) {


INFO: InvalidOperand - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L43\src/Commands/[1;31mCacheRulesCommand.php:43:31[0m]8;;\ - Cannot process ints and floats in strict binary operands mode, please cast explicitly (see https://psalm.dev/058)
            $duration = round([30;47m(microtime(true) - $start) * 1000[0m, 2);


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L63\src/Commands/[1;31mCacheRulesCommand.php:63:17[0m]8;;\ - Operand of type array<array-key, mixed>|bool|null|string contains types array<array-key, mixed>|string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
            if ([30;47m$this->option('force')[0m) {


INFO: PossiblyFalseArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L122\src/Commands/[1;31mCacheRulesCommand.php:122:58[0m]8;;\ - Argument 1 of Roster\Commands\CacheRulesCommand::formatBytes cannot be false, possibly int value expected (see https://psalm.dev/104)
            $this->line("   Size: " . $this->formatBytes([30;47m$size[0m));


INFO: InvalidOperand - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L134\src/Commands/[1;31mCacheRulesCommand.php:134:13[0m]8;;\ - Cannot process ints and floats in strict binary operands mode, please cast explicitly (see https://psalm.dev/058)
            [30;47m$bytes /= 1024[0m;


INFO: InvalidOperand - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L138\src/Commands/[1;31mCacheRulesCommand.php:138:16[0m]8;;\ - Cannot concatenate with a float (see https://psalm.dev/058)
        return [30;47mround($bytes, 2)[0m . ' ' . $units[$i];


INFO: InvalidArrayOffset - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/CacheRulesCommand.php#L138\src/Commands/[1;31mCacheRulesCommand.php:138:41[0m]8;;\ - Cannot access value on variable $units using a int<0, max> offset, expecting int<0, 3> (see https://psalm.dev/115)
        return round($bytes, 2) . ' ' . [30;47m$units[$i][0m;


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L17\src/Commands/[1;31mInstallRosterCommand.php:17:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$laravel is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L17\src/Commands/[1;31mInstallRosterCommand.php:17:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$name is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L17\src/Commands/[1;31mInstallRosterCommand.php:17:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$components is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L17\src/Commands/[1;31mInstallRosterCommand.php:17:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$input is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L17\src/Commands/[1;31mInstallRosterCommand.php:17:7[0m]8;;\ - Property Roster\Commands\InstallRosterCommand::$output is not defined in constructor of Roster\Commands\InstallRosterCommand or in any methods called in the constructor (see https://psalm.dev/074)
class [30;47mInstallRosterCommand[0m extends Command


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L17\src/Commands/[1;31mInstallRosterCommand.php:17:7[0m]8;;\ - Class Roster\Commands\InstallRosterCommand is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mInstallRosterCommand[0m extends Command


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Commands/InstallRosterCommand.php#L37\src/Commands/[1;31mInstallRosterCommand.php:37:21[0m]8;;\ - Cannot find any calls to method Roster\Commands\InstallRosterCommand::handle (see https://psalm.dev/087)
    public function [97;41mhandle[0m(): int


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L101\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:101:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::getAllForSchedulable (see https://psalm.dev/087)
    public function [97;41mgetAllForSchedulable[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L142\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:142:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::doTimeRangesOverlap (see https://psalm.dev/087)
    public function [97;41mdoTimeRangesOverlap[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L158\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:158:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::dateRangesOverlap (see https://psalm.dev/087)
    public function [97;41mdateRangesOverlap[0m(


[0;31mERROR[0m: PossiblyUnusedReturnValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L185\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:185:16[0m]8;;\ - The return value for this method is never used (see https://psalm.dev/273)
     * @return [97;41mbool[0m True if all deletions successful, false otherwise


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php#L261\src/Contracts/Repository/[1;31mAvailabilityRepositoryInterface.php:261:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\AvailabilityRepositoryInterface::filterAvailabilitiesForDate (see https://psalm.dev/087)
    public function [97;41mfilterAvailabilitiesForDate[0m(Collection $availabilities, Carbon $date): Collection;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ImpedimentRepositoryInterface.php#L45\src/Contracts/Repository/[1;31mImpedimentRepositoryInterface.php:45:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\ImpedimentRepositoryInterface::getAll (see https://psalm.dev/087)
    public function [97;41mgetAll[0m(): Collection;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ImpedimentRepositoryInterface.php#L82\src/Contracts/Repository/[1;31mImpedimentRepositoryInterface.php:82:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\ImpedimentRepositoryInterface::findOverlappingImpediments (see https://psalm.dev/087)
    public function [97;41mfindOverlappingImpediments[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php#L61\src/Contracts/Repository/[1;31mScheduleRepositoryInterface.php:61:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\ScheduleRepositoryInterface::findForTimeSlot (see https://psalm.dev/087)
    public function [97;41mfindForTimeSlot[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php#L107\src/Contracts/Repository/[1;31mScheduleRepositoryInterface.php:107:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Repository\ScheduleRepositoryInterface::getAllForSchedulable (see https://psalm.dev/087)
    public function [97;41mgetAllForSchedulable[0m(


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/AvailabilityDependentServiceInterface.php#L9\src/Contracts/Services/[1;31mAvailabilityDependentServiceInterface.php:9:11[0m]8;;\ - Class Roster\Contracts\Services\AvailabilityDependentServiceInterface is never used (see https://psalm.dev/075)
interface [97;41mAvailabilityDependentServiceInterface[0m


[0;31mERROR[0m: UndefinedDocblockClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/AvailabilityValidatorInterface.php#L18\src/Contracts/Services/[1;31mAvailabilityValidatorInterface.php:18:16[0m]8;;\ - Docblock-defined class, interface or enum named Roster\Exceptions\ValidationException does not exist (see https://psalm.dev/200)
     * @throws [97;41mValidationException[0m When validation fails


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Services/ConfigurableInterface.php#L11\src/Contracts/Services/[1;31mConfigurableInterface.php:11:11[0m]8;;\ - Class Roster\Contracts\Services\ConfigurableInterface is never used (see https://psalm.dev/075)
interface [97;41mConfigurableInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L57\src/Contracts/Validation/[1;31mValidationContextInterface.php:57:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::rawGet (see https://psalm.dev/087)
    public function [97;41mrawGet[0m(string $key, mixed $default = null): mixed;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L62\src/Contracts/Validation/[1;31mValidationContextInterface.php:62:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::rawHas (see https://psalm.dev/087)
    public function [97;41mrawHas[0m(string $key): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L69\src/Contracts/Validation/[1;31mValidationContextInterface.php:69:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::getData (see https://psalm.dev/087)
    public function [97;41mgetData[0m(): array;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L74\src/Contracts/Validation/[1;31mValidationContextInterface.php:74:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::rawData (see https://psalm.dev/087)
    public function [97;41mrawData[0m(): array;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L81\src/Contracts/Validation/[1;31mValidationContextInterface.php:81:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::set (see https://psalm.dev/087)
    public function [97;41mset[0m(string $key, mixed $value): void;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L88\src/Contracts/Validation/[1;31mValidationContextInterface.php:88:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::all (see https://psalm.dev/087)
    public function [97;41mall[0m(): array;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L97\src/Contracts/Validation/[1;31mValidationContextInterface.php:97:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::addViolation (see https://psalm.dev/087)
    public function [97;41maddViolation[0m(string $field, string $message): void;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L111\src/Contracts/Validation/[1;31mValidationContextInterface.php:111:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::setFlag (see https://psalm.dev/087)
    public function [97;41msetFlag[0m(string $flag, mixed $value = true): void;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L116\src/Contracts/Validation/[1;31mValidationContextInterface.php:116:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::hasFlag (see https://psalm.dev/087)
    public function [97;41mhasFlag[0m(string $flag): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidationContextInterface.php#L121\src/Contracts/Validation/[1;31mValidationContextInterface.php:121:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidationContextInterface::getFlag (see https://psalm.dev/087)
    public function [97;41mgetFlag[0m(string $flag, mixed $default = false): mixed;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Validation/ValidatorInterface.php#L25\src/Contracts/Validation/[1;31mValidatorInterface.php:25:21[0m]8;;\ - Cannot find any calls to method Roster\Contracts\Validation\ValidatorInterface::hasRulesFor (see https://psalm.dev/087)
    public function [97;41mhasRulesFor[0m(OperationType $operationType, EntityType $entityType): bool;


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L11\src/DTOs/[1;31mAvailabilityData.php:11:7[0m]8;;\ - Class Roster\DTOs\AvailabilityData is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityData[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L43\src/DTOs/[1;31mAvailabilityData.php:43:28[0m]8;;\ - Cannot find any calls to method Roster\DTOs\AvailabilityData::fromModel (see https://psalm.dev/087)
    public static function [97;41mfromModel[0m(Availability $availability): self


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L46\src/DTOs/[1;31mAvailabilityData.php:46:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
            id: [30;47m$availability->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L47\src/DTOs/[1;31mAvailabilityData.php:47:19[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
            type: [30;47m$availability->type[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L48\src/DTOs/[1;31mAvailabilityData.php:48:19[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
            days: [30;47m$availability->days[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L49\src/DTOs/[1;31mAvailabilityData.php:49:28[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/218)
            validityStart: [30;47m$availability->validity_start[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L50\src/DTOs/[1;31mAvailabilityData.php:50:26[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/218)
            validityEnd: [30;47m$availability->validity_end[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L51\src/DTOs/[1;31mAvailabilityData.php:51:25[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_start is not defined (see https://psalm.dev/218)
            dailyStart: [30;47m$availability->daily_start[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L52\src/DTOs/[1;31mAvailabilityData.php:52:23[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_end is not defined (see https://psalm.dev/218)
            dailyEnd: [30;47m$availability->daily_end[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L53\src/DTOs/[1;31mAvailabilityData.php:53:28[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            schedulableId: [30;47m$availability->schedulable_id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L54\src/DTOs/[1;31mAvailabilityData.php:54:30[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            schedulableType: [30;47m$availability->schedulable_type[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L107\src/DTOs/[1;31mAvailabilityData.php:107:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\AvailabilityData::withAvailabilityId (see https://psalm.dev/087)
    public function [97;41mwithAvailabilityId[0m(?int $availabilityId): self


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L156\src/DTOs/[1;31mAvailabilityData.php:156:25[0m]8;;\ - Function Roster\DTOs\roster_get_valid_days_in_period does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        $filteredDays = [97;41mroster_get_valid_days_in_period($existingDays, $newValidityStart, $newValidityEnd)[0m;


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L177\src/DTOs/[1;31mAvailabilityData.php:177:14[0m]8;;\ - Function Roster\DTOs\roster_should_auto_adjust_days does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        if (![97;41mroster_should_auto_adjust_days($this->validityStart, $this->validityEnd)[0m) {


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L182\src/DTOs/[1;31mAvailabilityData.php:182:16[0m]8;;\ - Function Roster\DTOs\roster_days_in_period does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        return [97;41mroster_days_in_period($this->validityStart, $this->validityEnd)[0m;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L188\src/DTOs/[1;31mAvailabilityData.php:188:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\AvailabilityData::filterDaysByCurrentPeriod (see https://psalm.dev/087)
    public function [97;41mfilterDaysByCurrentPeriod[0m(?array $existingDays = null): array


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L197\src/DTOs/[1;31mAvailabilityData.php:197:16[0m]8;;\ - Function Roster\DTOs\roster_get_valid_days_in_period does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        return [97;41mroster_get_valid_days_in_period($daysToFilter, $this->validityStart, $this->validityEnd)[0m;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L203\src/DTOs/[1;31mAvailabilityData.php:203:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\AvailabilityData::hasValidDays (see https://psalm.dev/087)
    public function [97;41mhasValidDays[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L222\src/DTOs/[1;31mAvailabilityData.php:222:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\AvailabilityData::getDaysOrDefault (see https://psalm.dev/087)
    public function [97;41mgetDaysOrDefault[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L234\src/DTOs/[1;31mAvailabilityData.php:234:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\AvailabilityData::isDayInPeriod (see https://psalm.dev/087)
    public function [97;41misDayInPeriod[0m(string $day): bool


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L240\src/DTOs/[1;31mAvailabilityData.php:240:16[0m]8;;\ - Function Roster\DTOs\roster_is_day_in_period does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        return [97;41mroster_is_day_in_period($day, $this->validityStart, $this->validityEnd)[0m;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L246\src/DTOs/[1;31mAvailabilityData.php:246:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\AvailabilityData::getPeriodDurationInDays (see https://psalm.dev/087)
    public function [97;41mgetPeriodDurationInDays[0m(): ?int


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/AvailabilityData.php#L252\src/DTOs/[1;31mAvailabilityData.php:252:16[0m]8;;\ - Function Roster\DTOs\roster_period_duration_in_days does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        return [97;41mroster_period_duration_in_days($this->validityStart, $this->validityEnd)[0m;


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L10\src/DTOs/[1;31mImpedimentData.php:10:7[0m]8;;\ - Class Roster\DTOs\ImpedimentData is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpedimentData[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L47\src/DTOs/[1;31mImpedimentData.php:47:28[0m]8;;\ - Cannot find any calls to method Roster\DTOs\ImpedimentData::fromModel (see https://psalm.dev/087)
    public static function [97;41mfromModel[0m(Impediment $impediment): self


INFO: RedundantConditionGivenDocblockType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L52\src/DTOs/[1;31mImpedimentData.php:52:28[0m]8;;\ - Operand of type Illuminate\Support\Carbon is always truthy (see https://psalm.dev/156)
            startDatetime: [30;47m$impediment->start_datetime[0m ? Carbon::parse($impediment->start_datetime) : null,


INFO: DocblockTypeContradiction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L52\src/DTOs/[1;31mImpedimentData.php:52:103[0m]8;;\ - Docblock-defined type Illuminate\Support\Carbon for $impediment->start_datetime is never falsy (see https://psalm.dev/155)
            startDatetime: $impediment->start_datetime ? Carbon::parse($impediment->start_datetime) : [30;47mnull[0m,


INFO: RedundantConditionGivenDocblockType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L53\src/DTOs/[1;31mImpedimentData.php:53:26[0m]8;;\ - Operand of type Illuminate\Support\Carbon is always truthy (see https://psalm.dev/156)
            endDatetime: [30;47m$impediment->end_datetime[0m ? Carbon::parse($impediment->end_datetime) : null,


INFO: DocblockTypeContradiction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L53\src/DTOs/[1;31mImpedimentData.php:53:97[0m]8;;\ - Docblock-defined type Illuminate\Support\Carbon for $impediment->end_datetime is never falsy (see https://psalm.dev/155)
            endDatetime: $impediment->end_datetime ? Carbon::parse($impediment->end_datetime) : [30;47mnull[0m,


INFO: RedundantConditionGivenDocblockType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L55\src/DTOs/[1;31mImpedimentData.php:55:23[0m]8;;\ - Docblock-defined type array<array-key, mixed> for $impediment->metadata is always isset (see https://psalm.dev/156)
            metadata: [30;47m$impediment->metadata[0m ?? [],


INFO: DocblockTypeContradiction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L55\src/DTOs/[1;31mImpedimentData.php:55:48[0m]8;;\ - Cannot resolve types for $impediment->metadata with docblock-defined type array<array-key, mixed> and !isset assertion (see https://psalm.dev/155)
            metadata: $impediment->metadata ?? [30;47m[][0m,


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ImpedimentData.php#L92\src/DTOs/[1;31mImpedimentData.php:92:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\ImpedimentData::withAvailabilityId (see https://psalm.dev/087)
    public function [97;41mwithAvailabilityId[0m(?int $availabilityId): self


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L11\src/DTOs/[1;31mScheduleData.php:11:7[0m]8;;\ - Class Roster\DTOs\ScheduleData is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mScheduleData[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L77\src/DTOs/[1;31mScheduleData.php:77:28[0m]8;;\ - Cannot find any calls to method Roster\DTOs\ScheduleData::fromModel (see https://psalm.dev/087)
    public static function [97;41mfromModel[0m(Schedule $schedule): self


INFO: RedundantConditionGivenDocblockType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L84\src/DTOs/[1;31mScheduleData.php:84:28[0m]8;;\ - Operand of type Illuminate\Support\Carbon is always truthy (see https://psalm.dev/156)
            startDatetime: [30;47m$schedule->start_datetime[0m ? Carbon::parse($schedule->start_datetime) : null,


INFO: DocblockTypeContradiction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L84\src/DTOs/[1;31mScheduleData.php:84:99[0m]8;;\ - Docblock-defined type Illuminate\Support\Carbon for $schedule->start_datetime is never falsy (see https://psalm.dev/155)
            startDatetime: $schedule->start_datetime ? Carbon::parse($schedule->start_datetime) : [30;47mnull[0m,


INFO: RedundantConditionGivenDocblockType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L85\src/DTOs/[1;31mScheduleData.php:85:26[0m]8;;\ - Operand of type Illuminate\Support\Carbon is always truthy (see https://psalm.dev/156)
            endDatetime: [30;47m$schedule->end_datetime[0m ? Carbon::parse($schedule->end_datetime) : null,


INFO: DocblockTypeContradiction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L85\src/DTOs/[1;31mScheduleData.php:85:93[0m]8;;\ - Docblock-defined type Illuminate\Support\Carbon for $schedule->end_datetime is never falsy (see https://psalm.dev/155)
            endDatetime: $schedule->end_datetime ? Carbon::parse($schedule->end_datetime) : [30;47mnull[0m,


INFO: RedundantConditionGivenDocblockType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L86\src/DTOs/[1;31mScheduleData.php:86:23[0m]8;;\ - Docblock-defined type array<array-key, mixed> for $schedule->metadata is always isset (see https://psalm.dev/156)
            metadata: [30;47m$schedule->metadata[0m ?? [],


INFO: DocblockTypeContradiction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L86\src/DTOs/[1;31mScheduleData.php:86:46[0m]8;;\ - Cannot resolve types for $schedule->metadata with docblock-defined type array<array-key, mixed> and !isset assertion (see https://psalm.dev/155)
            metadata: $schedule->metadata ?? [30;47m[][0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L88\src/DTOs/[1;31mScheduleData.php:88:28[0m]8;;\ - Magic instance property Roster\Models\Schedule::$schedulable_id is not defined (see https://psalm.dev/218)
            schedulableId: [30;47m$schedule->schedulable_id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L89\src/DTOs/[1;31mScheduleData.php:89:30[0m]8;;\ - Magic instance property Roster\Models\Schedule::$schedulable_type is not defined (see https://psalm.dev/218)
            schedulableType: [30;47m$schedule->schedulable_type[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L131\src/DTOs/[1;31mScheduleData.php:131:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\ScheduleData::withAvailabilityId (see https://psalm.dev/087)
    public function [97;41mwithAvailabilityId[0m(int $availabilityId): self


INFO: RedundantCondition - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L156\src/DTOs/[1;31mScheduleData.php:156:13[0m]8;;\ - Type string for $this->status is always string (see https://psalm.dev/122)
        if ([30;47mis_string($this->status)[0m) {


INFO: RedundantCondition - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L172\src/DTOs/[1;31mScheduleData.php:172:13[0m]8;;\ - Type string for $this->status is always string (see https://psalm.dev/122)
        if ([30;47mis_string($this->status)[0m && $this->hasValidStatus()) {


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/DTOs/ScheduleData.php#L182\src/DTOs/[1;31mScheduleData.php:182:21[0m]8;;\ - Cannot find any calls to method Roster\DTOs\ScheduleData::getStatusAsString (see https://psalm.dev/087)
    public function [97;41mgetStatusAsString[0m(): string


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Enums/ActivityType.php#L16\src/Enums/[1;31mActivityType.php:16:6[0m]8;;\ - Class Roster\Enums\ActivityType is never used (see https://psalm.dev/075)
enum [97;41mActivityType[0m: string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Enums/EntityType.php#L24\src/Enums/[1;31mEntityType.php:24:21[0m]8;;\ - Cannot find any calls to method Roster\Enums\EntityType::dateFields (see https://psalm.dev/087)
    public function [97;41mdateFields[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Enums/EntityType.php#L32\src/Enums/[1;31mEntityType.php:32:28[0m]8;;\ - Cannot find any calls to method Roster\Enums\EntityType::fromServiceClass (see https://psalm.dev/087)
    public static function [97;41mfromServiceClass[0m(string $className): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Enums/OperationType.php#L14\src/Enums/[1;31mOperationType.php:14:21[0m]8;;\ - Cannot find any calls to method Roster\Enums\OperationType::isWriteOperation (see https://psalm.dev/087)
    public function [97;41misWriteOperation[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Enums/OperationType.php#L19\src/Enums/[1;31mOperationType.php:19:21[0m]8;;\ - Cannot find any calls to method Roster\Enums\OperationType::isReadOperation (see https://psalm.dev/087)
    public function [97;41misReadOperation[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Enums/MissingResourceType.php#L38\src/Exceptions/Enums/[1;31mMissingResourceType.php:38:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\Enums\MissingResourceType::fromMessage (see https://psalm.dev/087)
    public static function [97;41mfromMessage[0m(string $message): ?self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Enums/MissingResourceType.php#L54\src/Exceptions/Enums/[1;31mMissingResourceType.php:54:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\Enums\MissingResourceType::toArray (see https://psalm.dev/087)
    public function [97;41mtoArray[0m(): array


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Messages/ErrorMessageFactory.php#L10\src/Exceptions/Messages/[1;31mErrorMessageFactory.php:10:7[0m]8;;\ - Class Roster\Exceptions\Messages\ErrorMessageFactory is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mErrorMessageFactory[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Messages/ErrorMessageFactory.php#L68\src/Exceptions/Messages/[1;31mErrorMessageFactory.php:68:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\Messages\ErrorMessageFactory::requiredField (see https://psalm.dev/087)
    public static function [97;41mrequiredField[0m(string $field): string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/Messages/ErrorMessageFactory.php#L79\src/Exceptions/Messages/[1;31mErrorMessageFactory.php:79:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\Messages\ErrorMessageFactory::invalidTimezone (see https://psalm.dev/087)
    public static function [97;41minvalidTimezone[0m(string $timezone): string


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L12\src/Exceptions/[1;31mNotFoundException.php:12:7[0m]8;;\ - Class Roster\Exceptions\NotFoundException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mNotFoundException[0m extends Exception


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L66\src/Exceptions/[1;31mNotFoundException.php:66:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::forSchedulableEntity (see https://psalm.dev/087)
    public static function [97;41mforSchedulableEntity[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L91\src/Exceptions/[1;31mNotFoundException.php:91:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::forRelationship (see https://psalm.dev/087)
    public static function [97;41mforRelationship[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L113\src/Exceptions/[1;31mNotFoundException.php:113:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::forAvailability (see https://psalm.dev/087)
    public static function [97;41mforAvailability[0m(int $availabilityId): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L123\src/Exceptions/[1;31mNotFoundException.php:123:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::forSchedule (see https://psalm.dev/087)
    public static function [97;41mforSchedule[0m(int $scheduleId): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L133\src/Exceptions/[1;31mNotFoundException.php:133:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::forImpediment (see https://psalm.dev/087)
    public static function [97;41mforImpediment[0m(int $impedimentId): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L141\src/Exceptions/[1;31mNotFoundException.php:141:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::forMissingSchedulable (see https://psalm.dev/087)
    public static function [97;41mforMissingSchedulable[0m(): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L152\src/Exceptions/[1;31mNotFoundException.php:152:28[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::forTimeSlot (see https://psalm.dev/087)
    public static function [97;41mforTimeSlot[0m(string $start, string $end): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L163\src/Exceptions/[1;31mNotFoundException.php:163:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::toArray (see https://psalm.dev/087)
    public function [97;41mtoArray[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L177\src/Exceptions/[1;31mNotFoundException.php:177:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::isForEntity (see https://psalm.dev/087)
    public function [97;41misForEntity[0m(string $entityType): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L187\src/Exceptions/[1;31mNotFoundException.php:187:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::getEntityType (see https://psalm.dev/087)
    public function [97;41mgetEntityType[0m(): ?string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L204\src/Exceptions/[1;31mNotFoundException.php:204:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::getEntityId (see https://psalm.dev/087)
    public function [97;41mgetEntityId[0m(): ?int


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/NotFoundException.php#L220\src/Exceptions/[1;31mNotFoundException.php:220:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\NotFoundException::isNotFound (see https://psalm.dev/087)
    public function [97;41misNotFound[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Exceptions/RosterException.php#L52\src/Exceptions/[1;31mRosterException.php:52:21[0m]8;;\ - Cannot find any calls to method Roster\Exceptions\RosterException::getContext (see https://psalm.dev/087)
    public function [97;41mgetContext[0m(): array


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Availability.php#L39\src/Facades/[1;31mAvailability.php:39:7[0m]8;;\ - Class Roster\Facades\Availability is never used (see https://psalm.dev/075)
class [97;41mAvailability[0m extends Facade


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Availability.php#L39\src/Facades/[1;31mAvailability.php:39:7[0m]8;;\ - Class Roster\Facades\Availability is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailability[0m extends Facade


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Availability.php#L46\src/Facades/[1;31mAvailability.php:46:5[0m]8;;\ - Method Roster\Facades\Availability::getfacadeaccessor should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get the service container binding for the facade.
     *
     * @return string The service container binding identifier
     */
    [97;41mprotected static function getFacadeAccessor(): string[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Impediment.php#L37\src/Facades/[1;31mImpediment.php:37:7[0m]8;;\ - Class Roster\Facades\Impediment is never used (see https://psalm.dev/075)
class [97;41mImpediment[0m extends Facade


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Impediment.php#L37\src/Facades/[1;31mImpediment.php:37:7[0m]8;;\ - Class Roster\Facades\Impediment is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpediment[0m extends Facade


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Impediment.php#L44\src/Facades/[1;31mImpediment.php:44:5[0m]8;;\ - Method Roster\Facades\Impediment::getfacadeaccessor should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get the service container binding for the facade.
     *
     * @return string The service container binding identifier
     */
    [97;41mprotected static function getFacadeAccessor(): string[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Schedule.php#L38\src/Facades/[1;31mSchedule.php:38:7[0m]8;;\ - Class Roster\Facades\Schedule is never used (see https://psalm.dev/075)
class [97;41mSchedule[0m extends Facade


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Schedule.php#L38\src/Facades/[1;31mSchedule.php:38:7[0m]8;;\ - Class Roster\Facades\Schedule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSchedule[0m extends Facade


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Facades/Schedule.php#L45\src/Facades/[1;31mSchedule.php:45:5[0m]8;;\ - Method Roster\Facades\Schedule::getfacadeaccessor should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get the service container binding for the facade.
     *
     * @return string The service container binding identifier
     */
    [97;41mprotected static function getFacadeAccessor(): string[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L20\src/Models/[1;31mAvailability.php:20:7[0m]8;;\ - Class Roster\Models\Availability is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailability[0m extends Model


INFO: NonInvariantDocblockPropertyType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L29\src/Models/[1;31mAvailability.php:29:15[0m]8;;\ - Property Roster\Models\Availability::$table has type string, not invariant with Illuminate\Database\Eloquent\Model::$table of type null|string (see https://psalm.dev/267)
    protected [30;47m$table[0m = 'roster_availabilities';


INFO: NonInvariantDocblockPropertyType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L52\src/Models/[1;31mAvailability.php:52:15[0m]8;;\ - Property Roster\Models\Availability::$casts has type array<string, string>, not invariant with Illuminate\Database\Eloquent\Concerns\HasAttributes::$casts of type array<array-key, mixed> (see https://psalm.dev/267)
    protected [30;47m$casts[0m = [


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L71\src/Models/[1;31mAvailability.php:71:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::schedules (see https://psalm.dev/087)
    public function [97;41mschedules[0m(): HasMany


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L79\src/Models/[1;31mAvailability.php:79:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::impediments (see https://psalm.dev/087)
    public function [97;41mimpediments[0m(): HasMany


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L117\src/Models/[1;31mAvailability.php:117:37[0m]8;;\ - Instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/041)
        return in_array($dayOfWeek, [30;47m$this->days[0m, true);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L131\src/Models/[1;31mAvailability.php:131:23[0m]8;;\ - Instance property Roster\Models\Availability::$daily_start is not defined (see https://psalm.dev/041)
        $dailyStart = [30;47m$this->daily_start[0m->format('H:i:s');


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L132\src/Models/[1;31mAvailability.php:132:21[0m]8;;\ - Instance property Roster\Models\Availability::$daily_end is not defined (see https://psalm.dev/041)
        $dailyEnd = [30;47m$this->daily_end[0m->format('H:i:s');


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L146\src/Models/[1;31mAvailability.php:146:13[0m]8;;\ - Instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/041)
        if ([30;47m$this->validity_start[0m && $start->lt($this->validity_start)) {


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L150\src/Models/[1;31mAvailability.php:150:18[0m]8;;\ - Instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/041)
        return !([30;47m$this->validity_end[0m && $end->gt($this->validity_end));


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L165\src/Models/[1;31mAvailability.php:165:13[0m]8;;\ - Instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/041)
        if ([30;47m$this->validity_start[0m && $date->lt($this->validity_start)) {


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L169\src/Models/[1;31mAvailability.php:169:18[0m]8;;\ - Instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/041)
        return !([30;47m$this->validity_end[0m && $date->gt($this->validity_end));


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L177\src/Models/[1;31mAvailability.php:177:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::getDailyDurationMinutes (see https://psalm.dev/087)
    public function [97;41mgetDailyDurationMinutes[0m(): int


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L179\src/Models/[1;31mAvailability.php:179:16[0m]8;;\ - Instance property Roster\Models\Availability::$daily_start is not defined (see https://psalm.dev/041)
        return [30;47m$this->daily_start[0m->diffInMinutes($this->daily_end);


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L179\src/Models/[1;31mAvailability.php:179:50[0m]8;;\ - Instance property Roster\Models\Availability::$daily_end is not defined (see https://psalm.dev/041)
        return $this->daily_start->diffInMinutes([30;47m$this->daily_end[0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L187\src/Models/[1;31mAvailability.php:187:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::getValidityDurationDays (see https://psalm.dev/087)
    public function [97;41mgetValidityDurationDays[0m(): ?int


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L189\src/Models/[1;31mAvailability.php:189:14[0m]8;;\ - Instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/041)
        if (![30;47m$this->validity_start[0m || !$this->validity_end) {


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L189\src/Models/[1;31mAvailability.php:189:40[0m]8;;\ - Instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/041)
        if (!$this->validity_start || ![30;47m$this->validity_end[0m) {


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L201\src/Models/[1;31mAvailability.php:201:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::hasUnlimitedValidity (see https://psalm.dev/087)
    public function [97;41mhasUnlimitedValidity[0m(): bool


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L203\src/Models/[1;31mAvailability.php:203:16[0m]8;;\ - Instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/041)
        return [30;47m$this->validity_end[0m === null;


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L216\src/Models/[1;31mAvailability.php:216:13[0m]8;;\ - Instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/041)
        if ([30;47m$this->validity_start[0m === null) {


INFO: UndefinedThisPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L233\src/Models/[1;31mAvailability.php:233:13[0m]8;;\ - Instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/041)
        if ([30;47m$this->validity_end[0m === null) {


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Availability.php#L246\src/Models/[1;31mAvailability.php:246:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::isValidityActive (see https://psalm.dev/087)
    public function [97;41misValidityActive[0m(?Carbon $date = null): bool


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L31\src/Models/[1;31mImpediment.php:31:7[0m]8;;\ - Class Roster\Models\Impediment is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpediment[0m extends Model


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L57\src/Models/[1;31mImpediment.php:57:24[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::metadata (see https://psalm.dev/087)
    protected function [97;41mmetadata[0m(): Attribute


INFO: MissingClosureReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L60\src/Models/[1;31mImpediment.php:60:18[0m]8;;\ - Closure does not have a return type, expecting mixed (see https://psalm.dev/068)
            get: [30;47mfn($value) => is_string($value) ? json_decode($value, true) : $value[0m,


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L60\src/Models/[1;31mImpediment.php:60:21[0m]8;;\ - Parameter $value has no provided type (see https://psalm.dev/153)
            get: fn([30;47m$value[0m) => is_string($value) ? json_decode($value, true) : $value,


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L61\src/Models/[1;31mImpediment.php:61:21[0m]8;;\ - Parameter $value has no provided type (see https://psalm.dev/153)
            set: fn([30;47m$value[0m) => is_array($value) ? json_encode($value) : $value


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L70\src/Models/[1;31mImpediment.php:70:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::availability (see https://psalm.dev/087)
    public function [97;41mavailability[0m(): BelongsTo


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L80\src/Models/[1;31mImpediment.php:80:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::schedulable (see https://psalm.dev/087)
    public function [97;41mschedulable[0m()


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L92\src/Models/[1;31mImpediment.php:92:21[0m]8;;\ - Cannot find explicit calls to method Roster\Models\Impediment::overlapsWith (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41moverlapsWith[0m(Carbon $start, Carbon $end): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L102\src/Models/[1;31mImpediment.php:102:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::getDurationMinutesAttribute (see https://psalm.dev/087)
    public function [97;41mgetDurationMinutesAttribute[0m(): float


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L112\src/Models/[1;31mImpediment.php:112:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::isActive (see https://psalm.dev/087)
    public function [97;41misActive[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L124\src/Models/[1;31mImpediment.php:124:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::isUpcoming (see https://psalm.dev/087)
    public function [97;41misUpcoming[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Impediment.php#L134\src/Models/[1;31mImpediment.php:134:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::isPast (see https://psalm.dev/087)
    public function [97;41misPast[0m(): bool


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L32\src/Models/[1;31mSchedule.php:32:7[0m]8;;\ - Class Roster\Models\Schedule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSchedule[0m extends Model


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L62\src/Models/[1;31mSchedule.php:62:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::availability (see https://psalm.dev/087)
    public function [97;41mavailability[0m(): BelongsTo


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L72\src/Models/[1;31mSchedule.php:72:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::schedulable (see https://psalm.dev/087)
    public function [97;41mschedulable[0m()


INFO: DocblockTypeContradiction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L74\src/Models/[1;31mSchedule.php:74:16[0m]8;;\ - Roster\Models\Availability does not contain null (see https://psalm.dev/155)
        return [30;47m$this->availability[0m?->schedulable();


INFO: RedundantConditionGivenDocblockType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L74\src/Models/[1;31mSchedule.php:74:16[0m]8;;\ - Docblock-defined type Roster\Models\Availability for $__tmp_nullsafe__1866 is never null (see https://psalm.dev/156)
        return [30;47m$this->availability?->schedulable()[0m;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L82\src/Models/[1;31mSchedule.php:82:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::getTypeAttribute (see https://psalm.dev/087)
    public function [97;41mgetTypeAttribute[0m(): string


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L84\src/Models/[1;31mSchedule.php:84:16[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
        return [30;47m$this->availability->type[0m;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L94\src/Models/[1;31mSchedule.php:94:21[0m]8;;\ - Cannot find explicit calls to method Roster\Models\Schedule::overlapsWith (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41moverlapsWith[0m(Carbon $start, Carbon $end): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L104\src/Models/[1;31mSchedule.php:104:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::getDurationMinutesAttribute (see https://psalm.dev/087)
    public function [97;41mgetDurationMinutesAttribute[0m(): float


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L114\src/Models/[1;31mSchedule.php:114:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::isActive (see https://psalm.dev/087)
    public function [97;41misActive[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L126\src/Models/[1;31mSchedule.php:126:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::isUpcoming (see https://psalm.dev/087)
    public function [97;41misUpcoming[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Models/Schedule.php#L136\src/Models/[1;31mSchedule.php:136:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::isPast (see https://psalm.dev/087)
    public function [97;41misPast[0m(): bool


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Observers/SchedulableObserver.php#L10\src/Observers/[1;31mSchedulableObserver.php:10:7[0m]8;;\ - Class Roster\Observers\SchedulableObserver is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSchedulableObserver[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Observers/SchedulableObserver.php#L19\src/Observers/[1;31mSchedulableObserver.php:19:21[0m]8;;\ - Cannot find any calls to method Roster\Observers\SchedulableObserver::creating (see https://psalm.dev/087)
    public function [97;41mcreating[0m(Model $model): void


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L23\src/Repositories/[1;31mAbstractRepository.php:23:30[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AbstractRepository::create (see https://psalm.dev/087)
    abstract public function [97;41mcreate[0m(array $data): Model;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L31\src/Repositories/[1;31mAbstractRepository.php:31:30[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AbstractRepository::update (see https://psalm.dev/087)
    abstract public function [97;41mupdate[0m(int $id, array $data): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L38\src/Repositories/[1;31mAbstractRepository.php:38:30[0m]8;;\ - Cannot find explicit calls to method Roster\Repositories\AbstractRepository::delete (but did find some potential callers) (see https://psalm.dev/087)
    abstract public function [97;41mdelete[0m(int $id): bool;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AbstractRepository.php#L52\src/Repositories/[1;31mAbstractRepository.php:52:30[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AbstractRepository::getAll (see https://psalm.dev/087)
    abstract public function [97;41mgetAll[0m(): Collection;


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L22\src/Repositories/[1;31mAvailabilityRepository.php:22:7[0m]8;;\ - Roster\Repositories\AvailabilityRepository has missing template params when extending Roster\Repositories\AbstractRepository, expecting 1 (see https://psalm.dev/182)
class [97;41mAvailabilityRepository[0m extends AbstractRepository implements AvailabilityRepositoryInterface


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L22\src/Repositories/[1;31mAvailabilityRepository.php:22:7[0m]8;;\ - Class Roster\Repositories\AvailabilityRepository is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityRepository[0m extends AbstractRepository implements AvailabilityRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L29\src/Repositories/[1;31mAvailabilityRepository.php:29:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::create should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function create(array $data): Availability[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L31\src/Repositories/[1;31mAvailabilityRepository.php:31:16[0m]8;;\ - Magic method Roster\Models\Availability::create does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::create($data)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L37\src/Repositories/[1;31mAvailabilityRepository.php:37:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L50\src/Repositories/[1;31mAvailabilityRepository.php:50:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::delete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function delete(int $id): bool[0m


INFO: InvalidNullableReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L50\src/Repositories/[1;31mAvailabilityRepository.php:50:38[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\AvailabilityRepository::delete is not nullable, but 'bool|null' contains null (see https://psalm.dev/144)
    public function delete(int $id): [30;47mbool[0m


INFO: NullableReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L54\src/Repositories/[1;31mAvailabilityRepository.php:54:16[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\AvailabilityRepository::delete is not nullable, but the function returns 'bool|null' (see https://psalm.dev/139)
        return [30;47mmatch (true) {
            $availability instanceof Availability => $availability->delete(),
            default => false,
        }[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L66\src/Repositories/[1;31mAvailabilityRepository.php:66:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::deletemultiple should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Delete multiple availabilities by their IDs.
     *
     * @param array<int> $ids Array of availability IDs to delete
     * @return bool True if any records were deleted
     */
    [97;41mpublic function deleteMultiple(array $ids): bool[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L68\src/Repositories/[1;31mAvailabilityRepository.php:68:16[0m]8;;\ - Magic method Roster\Models\Availability::wherein does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::whereIn('id', $ids)[0m->delete() > 0;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L74\src/Repositories/[1;31mAvailabilityRepository.php:74:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::find should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function find(int $id): ?Availability[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L76\src/Repositories/[1;31mAvailabilityRepository.php:76:16[0m]8;;\ - Magic method Roster\Models\Availability::find does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::find($id)[0m;


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L84\src/Repositories/[1;31mAvailabilityRepository.php:84:16[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::findForSchedulable is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
     * @return [30;47mCollection<int, Availability>[0m Collection of availabilities for the schedulable


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L86\src/Repositories/[1;31mAvailabilityRepository.php:86:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findforschedulable should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find availabilities for a specific schedulable entity.
     *
     * @param Model $model The schedulable entity
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the schedulable
     */
    [97;41mpublic function findForSchedulable(Model $model, ?string $type = null): Collection[0m


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L94\src/Repositories/[1;31mAvailabilityRepository.php:94:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::findForSchedulable (see https://psalm.dev/129)
        return [30;47m$builder->orderBy('daily_start')->get()[0m;


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L104\src/Repositories/[1;31mAvailabilityRepository.php:104:16[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getForDateRange is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
     * @return [30;47mCollection<int, Availability>[0m Collection of availabilities within the date range


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L106\src/Repositories/[1;31mAvailabilityRepository.php:106:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getfordaterange should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get availabilities for a specific date range.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of date range
     * @param Carbon $end End of date range
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities within the date range
     */
    [97;41mpublic function getForDateRange([0m


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L113\src/Repositories/[1;31mAvailabilityRepository.php:113:21[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
            ->where([30;47mfunction ($query) use ($end): void {
                $query->whereNull('validity_start')
                    ->orWhere('validity_start', '<=', $end);
            }[0m)


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L114\src/Repositories/[1;31mAvailabilityRepository.php:114:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
                $query->[30;47mwhereNull[0m('validity_start')


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L117\src/Repositories/[1;31mAvailabilityRepository.php:117:21[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
            ->where([30;47mfunction ($query) use ($start): void {
                $query->whereNull('validity_end')
                    ->orWhere('validity_end', '>=', $start);
            }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L118\src/Repositories/[1;31mAvailabilityRepository.php:118:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
                $query->[30;47mwhereNull[0m('validity_end')


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L122\src/Repositories/[1;31mAvailabilityRepository.php:122:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L126\src/Repositories/[1;31mAvailabilityRepository.php:126:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getForDateRange (see https://psalm.dev/129)
        return [30;47m$builder->get()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L139\src/Repositories/[1;31mAvailabilityRepository.php:139:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findfortimeslotwithconflictinfo should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find availability for a time slot with conflict information.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null The matching availability with conflict info or null
     * @throws InvalidArgumentException If the time range is invalid
     */
    [97;41mpublic function findForTimeSlotWithConflictInfo([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L146\src/Repositories/[1;31mAvailabilityRepository.php:146:16[0m]8;;\ - Magic method Roster\Models\Availability::where does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::where('schedulable_id', $model->id)[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L146\src/Repositories/[1;31mAvailabilityRepository.php:146:54[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        return Availability::where('schedulable_id', [30;47m$model->id[0m)


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L148\src/Repositories/[1;31mAvailabilityRepository.php:148:37[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            ->when($type, function ([30;47m$query[0m) use ($type): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L154\src/Repositories/[1;31mAvailabilityRepository.php:154:31[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            ->where(function ([30;47m$query[0m) use ($start): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L158\src/Repositories/[1;31mAvailabilityRepository.php:158:31[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            ->where(function ([30;47m$query[0m) use ($end): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L163\src/Repositories/[1;31mAvailabilityRepository.php:163:71[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
                'schedules as has_overlapping_schedules' => function ([30;47m$query[0m) use ($start, $end): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L167\src/Repositories/[1;31mAvailabilityRepository.php:167:75[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
                'impediments as has_overlapping_impediments' => function ([30;47m$query[0m) use ($start, $end): void {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L185\src/Repositories/[1;31mAvailabilityRepository.php:185:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findfortimeslot should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find availability for a specific time slot.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param string|null $type Optional availability type filter
     * @return Availability|null The matching availability or null
     * @throws InvalidArgumentException If the time range is invalid
     */
    [97;41mpublic function findForTimeSlot([0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L194\src/Repositories/[1;31mAvailabilityRepository.php:194:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L214\src/Repositories/[1;31mAvailabilityRepository.php:214:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getfordate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get availabilities for a specific date.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $date The date to check
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities for the date
     */
    [97;41mpublic function getForDate([0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L222\src/Repositories/[1;31mAvailabilityRepository.php:222:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L237\src/Repositories/[1;31mAvailabilityRepository.php:237:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getall should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function getAll(): Collection[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L237\src/Repositories/[1;31mAvailabilityRepository.php:237:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\AvailabilityRepository::getAll (see https://psalm.dev/087)
    public function [97;41mgetAll[0m(): Collection


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L250\src/Repositories/[1;31mAvailabilityRepository.php:250:16[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getAllForSchedulable is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
     * @return [30;47mCollection<int, Availability>[0m Collection of availabilities


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L252\src/Repositories/[1;31mAvailabilityRepository.php:252:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getallforschedulable should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get all availabilities for a schedulable entity.
     *
     * @param Model $model The schedulable entity
     * @param string|null $type Optional availability type filter
     * @param string|null $day Optional day filter
     * @return Collection<int, Availability> Collection of availabilities
     */
    [97;41mpublic function getAllForSchedulable([0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L260\src/Repositories/[1;31mAvailabilityRepository.php:260:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$type[0m) {


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L264\src/Repositories/[1;31mAvailabilityRepository.php:264:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$day[0m) {


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L268\src/Repositories/[1;31mAvailabilityRepository.php:268:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Availability>' for Roster\Repositories\AvailabilityRepository::getAllForSchedulable (see https://psalm.dev/129)
        return [30;47m$builder->orderBy('daily_start')->get()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L278\src/Repositories/[1;31mAvailabilityRepository.php:278:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::isavailableat should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if schedulable is available at specific datetime.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $datetime The datetime to check
     * @return bool True if available at the given datetime
     */
    [97;41mpublic function isAvailableAt(Model $model, Carbon $datetime): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L302\src/Repositories/[1;31mAvailabilityRepository.php:302:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findoverlapping should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find overlapping availabilities.
     *
     * @param Model $model The schedulable entity
     * @param array<string, mixed> $data The availability data to check
     * @param int|null $exceptId ID to exclude from search
     * @return Collection<int, Availability> Collection of overlapping availabilities
     * @throws InvalidArgumentException If time range is invalid
     */
    [97;41mpublic function findOverlapping([0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L304\src/Repositories/[1;31mAvailabilityRepository.php:304:15[0m]8;;\ - Argument 2 of Roster\Repositories\AvailabilityRepository::findOverlapping has wrong name $data, expecting $availabilityData as defined by Roster\Contracts\Repository\AvailabilityRepositoryInterface::findOverlapping (see https://psalm.dev/230)
        array [97;41m$data[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L320\src/Repositories/[1;31mAvailabilityRepository.php:320:50[0m]8;;\ - Argument 2 of Roster\Repositories\AvailabilityRepository::applyTimeOverlapFilters cannot be null, possibly null value provided (see https://psalm.dev/078)
        $this->applyTimeOverlapFilters($builder, [30;47m$dailyStart[0m, $dailyEnd);


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L320\src/Repositories/[1;31mAvailabilityRepository.php:320:63[0m]8;;\ - Argument 3 of Roster\Repositories\AvailabilityRepository::applyTimeOverlapFilters cannot be null, possibly null value provided (see https://psalm.dev/078)
        $this->applyTimeOverlapFilters($builder, $dailyStart, [30;47m$dailyEnd[0m);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L339\src/Repositories/[1;31mAvailabilityRepository.php:339:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::dotimerangesoverlap should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if time ranges overlap.
     *
     * @param Carbon $existingStart Existing start time
     * @param Carbon $existingEnd Existing end time
     * @param Carbon $newStart New start time
     * @param Carbon $newEnd New end time
     * @return bool True if time ranges overlap
     */
    [97;41mpublic function doTimeRangesOverlap([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L355\src/Repositories/[1;31mAvailabilityRepository.php:355:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::findbytype should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find related availabilities based on search criteria.
     *
     * @param Model $model The schedulable entity
     * @param array<string, mixed> $data Search criteria
     * @return Collection<int, Availability> Collection of related availabilities
     */
    [97;41mpublic function findByType(Model $model, array $data): Collection[0m


[0;31mERROR[0m: ParamNameMismatch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L355\src/Repositories/[1;31mAvailabilityRepository.php:355:52[0m]8;;\ - Argument 2 of Roster\Repositories\AvailabilityRepository::findByType has wrong name $data, expecting $availabilityData as defined by Roster\Contracts\Repository\AvailabilityRepositoryInterface::findByType (see https://psalm.dev/230)
    public function findByType(Model $model, array [97;41m$data[0m): Collection


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L378\src/Repositories/[1;31mAvailabilityRepository.php:378:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::buildquerywithfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Build filtered query for availabilities.
     *
     * @param Model $model The schedulable entity
     * @param array<string, mixed> $filters Filters to apply
     * @return Builder Eloquent query builder
     */
    [97;41mpublic function buildQueryWithFilters(Model $model, array $filters = []): Builder[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L406\src/Repositories/[1;31mAvailabilityRepository.php:406:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::isavailableondate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if an availability applies to a specific date.
     *
     * @param Availability $availability The availability to check
     * @param Carbon $date The date to check
     * @return bool True if the availability applies to the date
     */
    [97;41mpublic function isAvailableOnDate(Availability $availability, Carbon $date): bool[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L410\src/Repositories/[1;31mAvailabilityRepository.php:410:35[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
        if (!in_array($dayOfWeek, [30;47m$availability->days[0m)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L414\src/Repositories/[1;31mAvailabilityRepository.php:414:34[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/218)
        $isBeforeValidityStart = [30;47m$availability->validity_start[0m !== null && $date->lt($availability->validity_start);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L415\src/Repositories/[1;31mAvailabilityRepository.php:415:31[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/218)
        $isAfterValidityEnd = [30;47m$availability->validity_end[0m !== null && $date->gt($availability->validity_end);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L429\src/Repositories/[1;31mAvailabilityRepository.php:429:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::getavailabilitieswithconflictinfo should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Load availabilities with pre-loaded schedule and impediment conflicts.
     *
     * @param Model $model The schedulable entity
     * @param Carbon $start Start of the date range
     * @param Carbon $end End of the date range
     * @param string|null $type Optional availability type filter
     * @return Collection<int, Availability> Collection of availabilities with conflict info
     */
    [97;41mpublic function getAvailabilitiesWithConflictInfo([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L437\src/Repositories/[1;31mAvailabilityRepository.php:437:33[0m]8;;\ - Magic method Illuminate\Support\Collection::load does not exist (see https://psalm.dev/219)
        return $availabilities->[30;47mload[0m(['schedules', 'impediments']);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L447\src/Repositories/[1;31mAvailabilityRepository.php:447:5[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::filteravailabilitiesfordate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Filter availabilities for a specific date.
     *
     * @param Collection<int, Availability> $availabilities Collection of availabilities
     * @param Carbon $date Date to filter for
     * @return Collection<int, Availability> Filtered availabilities
     */
    [97;41mpublic function filterAvailabilitiesForDate(Collection $availabilities, Carbon $date): Collection[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L462\src/Repositories/[1;31mAvailabilityRepository.php:462:16[0m]8;;\ - Magic method Roster\Models\Availability::where does not exist (see https://psalm.dev/219)
        return [30;47mAvailability::where('schedulable_id', $model->id)[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L462\src/Repositories/[1;31mAvailabilityRepository.php:462:54[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        return Availability::where('schedulable_id', [30;47m$model->id[0m)


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L502\src/Repositories/[1;31mAvailabilityRepository.php:502:25[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but impure-Closure(static):void provided (see https://psalm.dev/004)
        $builder->where([30;47mfunction ($query) use ($startDate): void {
            $query->whereNull('validity_start')
                ->orWhere('validity_start', '<=', $startDate->toDateString());
        }[0m)->where(function ($query) use ($endDate): void {


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L503\src/Repositories/[1;31mAvailabilityRepository.php:503:21[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
            $query->[30;47mwhereNull[0m('validity_start')


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L505\src/Repositories/[1;31mAvailabilityRepository.php:505:19[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but impure-Closure(static):void provided (see https://psalm.dev/004)
        })->where([30;47mfunction ($query) use ($endDate): void {
            $query->whereNull('validity_end')
                ->orWhere('validity_end', '>=', $endDate->toDateString());
        }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L506\src/Repositories/[1;31mAvailabilityRepository.php:506:21[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereNull does not exist (see https://psalm.dev/022)
            $query->[30;47mwhereNull[0m('validity_end')


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L523\src/Repositories/[1;31mAvailabilityRepository.php:523:25[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
        $builder->where([30;47mfunction ($query) use ($days): void {
            foreach ($days as $day) {
                $query->orWhereJsonContains('days', $day);
            }
        }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L525\src/Repositories/[1;31mAvailabilityRepository.php:525:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::orWhereJsonContains does not exist (see https://psalm.dev/022)
                $query->[30;47morWhereJsonContains[0m('days', $day);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L539\src/Repositories/[1;31mAvailabilityRepository.php:539:25[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but impure-Closure(static):void provided (see https://psalm.dev/004)
        $builder->where([30;47mfunction ($query) use ($startTime, $endTime): void {
            $query->where('daily_start', '<', $endTime->format('H:i:s'))
                ->where('daily_end', '>', $startTime->format('H:i:s'));
        }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L540\src/Repositories/[1;31mAvailabilityRepository.php:540:21[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::where does not exist (see https://psalm.dev/022)
            $query->[30;47mwhere[0m('daily_start', '<', $endTime->format('H:i:s'))


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L554\src/Repositories/[1;31mAvailabilityRepository.php:554:25[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
        $builder->where([30;47mfunction ($query) use ($startDate, $endDate): void {
            match (true) {
                $startDate instanceof Carbon && $endDate instanceof Carbon =>
                $query->where('validity_start', '<=', $endDate)
                    ->where('validity_end', '>=', $startDate),

                $startDate instanceof Carbon =>
                $query->where(function ($subQuery) use ($startDate): void {
                    $subQuery->where('validity_end', '>=', $startDate)
                        ->orWhereNull('validity_end');
                }),

                $endDate instanceof Carbon =>
                $query->where(function ($subQuery) use ($endDate): void {
                    $subQuery->where('validity_start', '<=', $endDate)
                        ->orWhereNull('validity_start');
                }),

                default =>
                $query->where(function ($subQuery): void {
                    $subQuery->whereNull('validity_start')
                        ->orWhereNull('validity_end');
                }),
            };
        }[0m);


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L557\src/Repositories/[1;31mAvailabilityRepository.php:557:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::where does not exist (see https://psalm.dev/022)
                $query->[30;47mwhere[0m('validity_start', '<=', $endDate)


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L561\src/Repositories/[1;31mAvailabilityRepository.php:561:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::where does not exist (see https://psalm.dev/022)
                $query->[30;47mwhere[0m(function ($subQuery) use ($startDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L561\src/Repositories/[1;31mAvailabilityRepository.php:561:41[0m]8;;\ - Parameter $subQuery has no provided type (see https://psalm.dev/153)
                $query->where(function ([30;47m$subQuery[0m) use ($startDate): void {


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L567\src/Repositories/[1;31mAvailabilityRepository.php:567:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::where does not exist (see https://psalm.dev/022)
                $query->[30;47mwhere[0m(function ($subQuery) use ($endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L567\src/Repositories/[1;31mAvailabilityRepository.php:567:41[0m]8;;\ - Parameter $subQuery has no provided type (see https://psalm.dev/153)
                $query->where(function ([30;47m$subQuery[0m) use ($endDate): void {


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L573\src/Repositories/[1;31mAvailabilityRepository.php:573:25[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::where does not exist (see https://psalm.dev/022)
                $query->[30;47mwhere[0m(function ($subQuery): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L573\src/Repositories/[1;31mAvailabilityRepository.php:573:41[0m]8;;\ - Parameter $subQuery has no provided type (see https://psalm.dev/153)
                $query->where(function ([30;47m$subQuery[0m): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L591\src/Repositories/[1;31mAvailabilityRepository.php:591:38[0m]8;;\ - Parameter $relation has no provided type (see https://psalm.dev/153)
            'schedules' => function ([30;47m$relation[0m) use ($startDate, $endDate): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L597\src/Repositories/[1;31mAvailabilityRepository.php:597:40[0m]8;;\ - Parameter $relation has no provided type (see https://psalm.dev/153)
            'impediments' => function ([30;47m$relation[0m) use ($startDate, $endDate): void {


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L622\src/Repositories/[1;31mAvailabilityRepository.php:622:29[0m]8;;\ - Argument 1 of Illuminate\Database\Eloquent\Builder::where expects Closure(Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>):mixed|Illuminate\Contracts\Database\Query\Expression|array<array-key, mixed>|string, but pure-Closure(static):void provided (see https://psalm.dev/004)
            $builder->where([30;47mfunction ($q) use ($startDate, $endDate): void {
                $q->whereBetween('start_datetime', [$startDate, $endDate])
                    ->orWhereBetween('end_datetime', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate): void {
                        $subQuery->where('start_datetime', '<', $startDate)
                            ->where('end_datetime', '>', $endDate);
                    });
            }[0m),


INFO: UndefinedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L623\src/Repositories/[1;31mAvailabilityRepository.php:623:21[0m]8;;\ - Method Roster\Repositories\AvailabilityRepository::whereBetween does not exist (see https://psalm.dev/022)
                $q->[30;47mwhereBetween[0m('start_datetime', [$startDate, $endDate])


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/AvailabilityRepository.php#L625\src/Repositories/[1;31mAvailabilityRepository.php:625:41[0m]8;;\ - Parameter $subQuery has no provided type (see https://psalm.dev/153)
                    ->orWhere(function ([30;47m$subQuery[0m) use ($startDate, $endDate): void {


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L17\src/Repositories/[1;31mImpedimentRepository.php:17:7[0m]8;;\ - Roster\Repositories\ImpedimentRepository has missing template params when extending Roster\Repositories\AbstractRepository, expecting 1 (see https://psalm.dev/182)
class [97;41mImpedimentRepository[0m extends AbstractRepository implements ImpedimentRepositoryInterface


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L17\src/Repositories/[1;31mImpedimentRepository.php:17:7[0m]8;;\ - Class Roster\Repositories\ImpedimentRepository is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpedimentRepository[0m extends AbstractRepository implements ImpedimentRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L22\src/Repositories/[1;31mImpedimentRepository.php:22:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::create should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function create(array $data): Impediment[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L24\src/Repositories/[1;31mImpedimentRepository.php:24:16[0m]8;;\ - Magic method Roster\Models\Impediment::create does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::create($data)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L30\src/Repositories/[1;31mImpedimentRepository.php:30:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L40\src/Repositories/[1;31mImpedimentRepository.php:40:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::delete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function delete(int $id): bool[0m


INFO: InvalidNullableReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L40\src/Repositories/[1;31mImpedimentRepository.php:40:38[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ImpedimentRepository::delete is not nullable, but 'bool|null' contains null (see https://psalm.dev/144)
    public function delete(int $id): [30;47mbool[0m


INFO: NullableReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L44\src/Repositories/[1;31mImpedimentRepository.php:44:16[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ImpedimentRepository::delete is not nullable, but the function returns 'bool|null' (see https://psalm.dev/139)
        return [30;47m$impediment instanceof Impediment
            ? $impediment->delete()
            : false[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L52\src/Repositories/[1;31mImpedimentRepository.php:52:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::find should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function find(int $id): ?Impediment[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L54\src/Repositories/[1;31mImpedimentRepository.php:54:16[0m]8;;\ - Magic method Roster\Models\Impediment::find does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::find($id)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L60\src/Repositories/[1;31mImpedimentRepository.php:60:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::getall should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function getAll(): Collection[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L75\src/Repositories/[1;31mImpedimentRepository.php:75:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::findfortimeslot should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find impediments for a time slot.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @return Collection<int, Impediment>
     */
    [97;41mpublic function findForTimeSlot([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L80\src/Repositories/[1;31mImpedimentRepository.php:80:16[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::where('availability_id', $availabilityId)[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L96\src/Repositories/[1;31mImpedimentRepository.php:96:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::hasoverlappingimpediments should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if a time slot has overlapping impediments.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param int|null $excludeId Impediment ID to exclude
     * @return bool True if overlapping impediments exist
     */
    [97;41mpublic function hasOverlappingImpediments([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L102\src/Repositories/[1;31mImpedimentRepository.php:102:18[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        $query = [30;47mImpediment::where('availability_id', $availabilityId)[0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L106\src/Repositories/[1;31mImpedimentRepository.php:106:13[0m]8;;\ - Operand of type int|null contains type int, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$excludeId[0m) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L124\src/Repositories/[1;31mImpedimentRepository.php:124:5[0m]8;;\ - Method Roster\Repositories\ImpedimentRepository::findoverlappingimpediments should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find overlapping impediments with time range.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time range
     * @param Carbon $end End of time range
     * @param int|null $excludeId Impediment ID to exclude
     * @return Collection<int, Impediment>
     */
    [97;41mpublic function findOverlappingImpediments([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ImpedimentRepository.php#L130\src/Repositories/[1;31mImpedimentRepository.php:130:18[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        $query = [30;47mImpediment::where('availability_id', $availabilityId)[0m


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L17\src/Repositories/[1;31mScheduleRepository.php:17:7[0m]8;;\ - Roster\Repositories\ScheduleRepository has missing template params when extending Roster\Repositories\AbstractRepository, expecting 1 (see https://psalm.dev/182)
class [97;41mScheduleRepository[0m extends AbstractRepository implements ScheduleRepositoryInterface


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L17\src/Repositories/[1;31mScheduleRepository.php:17:7[0m]8;;\ - Class Roster\Repositories\ScheduleRepository is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mScheduleRepository[0m extends AbstractRepository implements ScheduleRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L22\src/Repositories/[1;31mScheduleRepository.php:22:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::create should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function create(array $data): Schedule[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L24\src/Repositories/[1;31mScheduleRepository.php:24:16[0m]8;;\ - Magic method Roster\Models\Schedule::create does not exist (see https://psalm.dev/219)
        return [30;47mSchedule::create($data)[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L30\src/Repositories/[1;31mScheduleRepository.php:30:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L40\src/Repositories/[1;31mScheduleRepository.php:40:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::delete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function delete(int $id): bool[0m


INFO: InvalidNullableReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L40\src/Repositories/[1;31mScheduleRepository.php:40:38[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ScheduleRepository::delete is not nullable, but 'bool|null' contains null (see https://psalm.dev/144)
    public function delete(int $id): [30;47mbool[0m


INFO: NullableReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L44\src/Repositories/[1;31mScheduleRepository.php:44:16[0m]8;;\ - The declared return type 'bool' for Roster\Repositories\ScheduleRepository::delete is not nullable, but the function returns 'bool|null' (see https://psalm.dev/139)
        return [30;47m$schedule instanceof Schedule
            ? $schedule->delete()
            : false[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L52\src/Repositories/[1;31mScheduleRepository.php:52:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::find should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function find(int $id): ?Schedule[0m


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L55\src/Repositories/[1;31mScheduleRepository.php:55:51[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            'availability.schedules' => function ([30;47m$query[0m): void {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L59\src/Repositories/[1;31mScheduleRepository.php:59:53[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
            'availability.impediments' => function ([30;47m$query[0m): void {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L68\src/Repositories/[1;31mScheduleRepository.php:68:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::getall should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritdoc}
     */
    [97;41mpublic function getAll(): Collection[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L68\src/Repositories/[1;31mScheduleRepository.php:68:21[0m]8;;\ - Cannot find any calls to method Roster\Repositories\ScheduleRepository::getAll (see https://psalm.dev/087)
    public function [97;41mgetAll[0m(): Collection


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L83\src/Repositories/[1;31mScheduleRepository.php:83:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::findfortimeslot should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find schedules for a time slot.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @return Collection<int, Schedule>
     */
    [97;41mpublic function findForTimeSlot([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L88\src/Repositories/[1;31mScheduleRepository.php:88:16[0m]8;;\ - Magic method Roster\Models\Schedule::where does not exist (see https://psalm.dev/219)
        return [30;47mSchedule::where('availability_id', $availabilityId)[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L104\src/Repositories/[1;31mScheduleRepository.php:104:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::hasoverlappingschedule should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Check if a time slot has overlapping schedules.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time slot
     * @param Carbon $end End of time slot
     * @param int|null $excludeId Schedule ID to exclude
     * @return bool True if overlapping schedules exist
     */
    [97;41mpublic function hasOverlappingSchedule([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L110\src/Repositories/[1;31mScheduleRepository.php:110:18[0m]8;;\ - Magic method Roster\Models\Schedule::where does not exist (see https://psalm.dev/219)
        $query = [30;47mSchedule::where('availability_id', $availabilityId)[0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L114\src/Repositories/[1;31mScheduleRepository.php:114:13[0m]8;;\ - Operand of type int|null contains type int, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m$excludeId[0m) {


[0;31mERROR[0m: UnusedVariable - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L119\src/Repositories/[1;31mScheduleRepository.php:119:9[0m]8;;\ - $sql is never referenced or the value is not used (see https://psalm.dev/077)
        [97;41m$sql[0m = $query->toSql();


[0;31mERROR[0m: UnusedVariable - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L120\src/Repositories/[1;31mScheduleRepository.php:120:9[0m]8;;\ - $bindings is never referenced or the value is not used (see https://psalm.dev/077)
        [97;41m$bindings[0m = $query->getBindings();


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L140\src/Repositories/[1;31mScheduleRepository.php:140:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::findoverlappingschedules should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Find overlapping schedules with time range.
     *
     * @param int $availabilityId The availability ID
     * @param Carbon $start Start of time range
     * @param Carbon $end End of time range
     * @param int|null $excludeId Schedule ID to exclude
     * @return Collection<int, Schedule>
     */
    [97;41mpublic function findOverlappingSchedules([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L146\src/Repositories/[1;31mScheduleRepository.php:146:18[0m]8;;\ - Magic method Roster\Models\Schedule::where does not exist (see https://psalm.dev/219)
        $query = [30;47mSchedule::where('availability_id', $availabilityId)[0m


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L165\src/Repositories/[1;31mScheduleRepository.php:165:16[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getAllForSchedulable is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
     * @return [30;47mCollection<int, Schedule>[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L167\src/Repositories/[1;31mScheduleRepository.php:167:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::getallforschedulable should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get all schedules for a schedulable.
     *
     * @param int $schedulableId The schedulable ID
     * @param string $schedulableType The schedulable class type
     * @param array<string, mixed> $filters Additional filters
     * @return Collection<int, Schedule>
     */
    [97;41mpublic function getAllForSchedulable([0m


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L177\src/Repositories/[1;31mScheduleRepository.php:177:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getAllForSchedulable (see https://psalm.dev/129)
        return [30;47m$builder->orderBy('start_datetime')->get()[0m;


INFO: MoreSpecificReturnType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L188\src/Repositories/[1;31mScheduleRepository.php:188:16[0m]8;;\ - The declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getForDateRange is more specific than the inferred return type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' (see https://psalm.dev/070)
     * @return [30;47mCollection<int, Schedule>[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L190\src/Repositories/[1;31mScheduleRepository.php:190:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::getfordaterange should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Get schedules between dates.
     *
     * @param int $schedulableId The schedulable ID
     * @param string $schedulableType The schedulable class type
     * @param Carbon $start Start of date range
     * @param Carbon $end End of date range
     * @param array<string, mixed> $filters Additional filters
     * @return Collection<int, Schedule>
     */
    [97;41mpublic function getForDateRange([0m


INFO: LessSpecificReturnStatement - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L203\src/Repositories/[1;31mScheduleRepository.php:203:16[0m]8;;\ - The type 'Illuminate\Database\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>' is more general than the declared return type 'Illuminate\Support\Collection<int, Roster\Models\Schedule>' for Roster\Repositories\ScheduleRepository::getForDateRange (see https://psalm.dev/129)
        return [30;47m$builder->orderBy('start_datetime')->get()[0m;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L213\src/Repositories/[1;31mScheduleRepository.php:213:5[0m]8;;\ - Method Roster\Repositories\ScheduleRepository::buildquerywithfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Apply filters to query.
     *
     * @param int $schedulableId The schedulable ID
     * @param string $schedulableType The schedulable class type
     * @param array<string, mixed> $filters Filters to apply
     */
    [97;41mpublic function buildQueryWithFilters([0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L229\src/Repositories/[1;31mScheduleRepository.php:229:16[0m]8;;\ - Magic method Roster\Models\Schedule::wherehas does not exist (see https://psalm.dev/219)
        return [30;47mSchedule::whereHas('availability', function ($query) use ($schedulableId, $schedulableType): void {
            $query->where('schedulable_id', $schedulableId)
                ->where('schedulable_type', $schedulableType);
        })[0m;


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Repositories/ScheduleRepository.php#L229\src/Repositories/[1;31mScheduleRepository.php:229:61[0m]8;;\ - Parameter $query has no provided type (see https://psalm.dev/153)
        return Schedule::whereHas('availability', function ([30;47m$query[0m) use ($schedulableId, $schedulableType): void {


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L33\src/[1;31mRosterServiceProvider.php:33:7[0m]8;;\ - Class Roster\RosterServiceProvider is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mRosterServiceProvider[0m extends ServiceProvider


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L35\src/[1;31mRosterServiceProvider.php:35:21[0m]8;;\ - Cannot find any calls to method Roster\RosterServiceProvider::boot (see https://psalm.dev/087)
    public function [97;41mboot[0m(): void


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L50\src/[1;31mRosterServiceProvider.php:50:5[0m]8;;\ - Method Roster\RosterServiceProvider::register should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function register(): void[0m


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L93\src/[1;31mRosterServiceProvider.php:93:68[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton(ValidatorInterface::class, function ([30;47m$app[0m) use ($useFileCache): Validator {


[0;31mERROR[0m: UnusedClosureParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L93\src/[1;31mRosterServiceProvider.php:93:68[0m]8;;\ - Param app is never referenced in this method (see https://psalm.dev/188)
        $this->app->singleton(ValidatorInterface::class, function ([97;41m$app[0m) use ($useFileCache): Validator {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L104\src/[1;31mRosterServiceProvider.php:104:61[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton(RuleScanner::class, function ([30;47m$app[0m) use ($useFileCache): RuleScanner {


[0;31mERROR[0m: UnusedClosureParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L104\src/[1;31mRosterServiceProvider.php:104:61[0m]8;;\ - Param app is never referenced in this method (see https://psalm.dev/188)
        $this->app->singleton(RuleScanner::class, function ([97;41m$app[0m) use ($useFileCache): RuleScanner {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L114\src/[1;31mRosterServiceProvider.php:114:64[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton('roster.availability', function ([30;47m$app[0m): AvailabilityService {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L121\src/[1;31mRosterServiceProvider.php:121:60[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton('roster.schedule', function ([30;47m$app[0m): ScheduleService {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L130\src/[1;31mRosterServiceProvider.php:130:62[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton('roster.impediment', function ([30;47m$app[0m): ImpedimentService {


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/RosterServiceProvider.php#L147\src/[1;31mRosterServiceProvider.php:147:74[0m]8;;\ - Parameter $app has no provided type (see https://psalm.dev/153)
        $this->app->singleton(ResourcePublisherService::class, function ([30;47m$app[0m): ResourcePublisherService {


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L20\src/Services/[1;31mAvailabilityService.php:20:7[0m]8;;\ - Class Roster\Services\AvailabilityService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityService[0m extends AbstractValidatingService


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L32\src/Services/[1;31mAvailabilityService.php:32:5[0m]8;;\ - Method Roster\Services\AvailabilityService::createdtofromarray should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function createDTOFromArray(array $data, OperationType $operationType): AvailabilityData[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L37\src/Services/[1;31mAvailabilityService.php:37:5[0m]8;;\ - Method Roster\Services\AvailabilityService::getentitytypeenum should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function getEntityTypeEnum(): EntityType[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L48\src/Services/[1;31mAvailabilityService.php:48:21[0m]8;;\ - Cannot find any calls to method Roster\Services\AvailabilityService::create (see https://psalm.dev/087)
    public function [97;41mcreate[0m(array $data): Availability


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L64\src/Services/[1;31mAvailabilityService.php:64:13[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            [30;47m$this->schedulable->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L64\src/Services/[1;31mAvailabilityService.php:64:13[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L65\src/Services/[1;31mAvailabilityService.php:65:23[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            get_class([30;47m$this->schedulable[0m)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L81\src/Services/[1;31mAvailabilityService.php:81:33[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
        $this->clearEntityCache([30;47m$availability->id[0m);


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L90\src/Services/[1;31mAvailabilityService.php:90:5[0m]8;;\ - Method Roster\Services\AvailabilityService::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Update an existing availability.
     * @param array<string, mixed> $data
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


INFO: MoreSpecificImplementedParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L90\src/Services/[1;31mAvailabilityService.php:90:43[0m]8;;\ - Argument 2 of Roster\Services\AvailabilityService::update has the more specific type 'array<string, mixed>', expecting 'array<array-key, mixed>' as defined by Roster\Services\Core\AbstractService::update (see https://psalm.dev/140)
    public function update(int $id, array [30;47m$data[0m): bool


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L121\src/Services/[1;31mAvailabilityService.php:121:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
                [30;47m$entity->days[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L122\src/Services/[1;31mAvailabilityService.php:122:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/218)
                [30;47m$entity->validity_start[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L123\src/Services/[1;31mAvailabilityService.php:123:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/218)
                [30;47m$entity->validity_end[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L148\src/Services/[1;31mAvailabilityService.php:148:21[0m]8;;\ - Cannot find explicit calls to method Roster\Services\AvailabilityService::delete (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mdelete[0m(int $id): bool


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L194\src/Services/[1;31mAvailabilityService.php:194:5[0m]8;;\ - Method Roster\Services\AvailabilityService::clearentitycache should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function clearEntityCache(int $entityId): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L199\src/Services/[1;31mAvailabilityService.php:199:5[0m]8;;\ - Method Roster\Services\AvailabilityService::find should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function find(int $id): ?Availability[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L204\src/Services/[1;31mAvailabilityService.php:204:5[0m]8;;\ - Method Roster\Services\AvailabilityService::get should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function get(): Collection[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L209\src/Services/[1;31mAvailabilityService.php:209:5[0m]8;;\ - Method Roster\Services\AvailabilityService::buildquerywithfilters should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function buildQueryWithFilters(): Builder[0m


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L211\src/Services/[1;31mAvailabilityService.php:211:69[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::buildQueryWithFilters cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $this->availabilityRepository->buildQueryWithFilters([30;47m$this->schedulable[0m, $this->filters);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L262\src/Services/[1;31mAvailabilityService.php:262:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            [30;47m$availability->schedulable_id[0m !== $this->schedulable->id ||


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L262\src/Services/[1;31mAvailabilityService.php:262:47[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            $availability->schedulable_id !== [30;47m$this->schedulable->id[0m ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L262\src/Services/[1;31mAvailabilityService.php:262:47[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            $availability->schedulable_id !== [30;47m$this->schedulable->id[0m ||


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L263\src/Services/[1;31mAvailabilityService.php:263:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            [30;47m$availability->schedulable_type[0m !== get_class($this->schedulable)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L263\src/Services/[1;31mAvailabilityService.php:263:59[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            $availability->schedulable_type !== get_class([30;47m$this->schedulable[0m)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L269\src/Services/[1;31mAvailabilityService.php:269:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
        if ([30;47m$availability->type[0m !== ($newData['type'] ?? null)) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L274\src/Services/[1;31mAvailabilityService.php:274:39[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
        $commonDays = array_intersect([30;47m$availability->days[0m, $newData['days'] ?? []);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L281\src/Services/[1;31mAvailabilityService.php:281:27[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_start is not defined (see https://psalm.dev/218)
            Carbon::parse([30;47m$availability->daily_start[0m),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L282\src/Services/[1;31mAvailabilityService.php:282:27[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_end is not defined (see https://psalm.dev/218)
            Carbon::parse([30;47m$availability->daily_end[0m),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L321\src/Services/[1;31mAvailabilityService.php:321:27[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_start is not defined (see https://psalm.dev/218)
            Carbon::parse([30;47m$availability->daily_start[0m),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L325\src/Services/[1;31mAvailabilityService.php:325:27[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_end is not defined (see https://psalm.dev/218)
            Carbon::parse([30;47m$availability->daily_end[0m),


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L333\src/Services/[1;31mAvailabilityService.php:333:61[0m]8;;\ - Magic instance property Roster\Models\Availability::$days is not defined (see https://psalm.dev/218)
        $mergedDays = array_values(array_unique(array_merge([30;47m$availability->days[0m, $newData['days'])));


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L336\src/Services/[1;31mAvailabilityService.php:336:34[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_start is not defined (see https://psalm.dev/218)
        $existingValidityStart = [30;47m$availability->validity_start[0m ? Carbon::parse($availability->validity_start) : null;


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L337\src/Services/[1;31mAvailabilityService.php:337:32[0m]8;;\ - Magic instance property Roster\Models\Availability::$validity_end is not defined (see https://psalm.dev/218)
        $existingValidityEnd = [30;47m$availability->validity_end[0m ? Carbon::parse($availability->validity_end) : null;


INFO: ArgumentTypeCoercion - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L346\src/Services/[1;31mAvailabilityService.php:346:47[0m]8;;\ - Argument 1 of min expects non-empty-array<array-key, mixed>, but parent type array{0?: float|int|string, 1?: float|int|string} provided (see https://psalm.dev/193)
            : Carbon::createFromTimestamp(min([30;47marray_map(fn($date) => $date->timestamp, $startDates)[0m));


INFO: ArgumentTypeCoercion - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L350\src/Services/[1;31mAvailabilityService.php:350:47[0m]8;;\ - Argument 1 of max expects non-empty-array<array-key, mixed>, but parent type array{0?: float|int|string, 1?: float|int|string} provided (see https://psalm.dev/193)
            : Carbon::createFromTimestamp(max([30;47marray_map(fn($date) => $date->timestamp, $endDates)[0m));


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L353\src/Services/[1;31mAvailabilityService.php:353:23[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
            'type' => [30;47m$availability->type[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L359\src/Services/[1;31mAvailabilityService.php:359:33[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            'schedulable_id' => [30;47m$this->schedulable->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L359\src/Services/[1;31mAvailabilityService.php:359:33[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            'schedulable_id' => [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/AvailabilityService.php#L360\src/Services/[1;31mAvailabilityService.php:360:45[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            'schedulable_type' => get_class([30;47m$this->schedulable[0m),


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L21\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:21:16[0m]8;;\ - Roster\Services\Core\AbstractAvailabilityValidatingService has missing template params when extending Roster\Services\Core\AbstractEntityScopingService, expecting 1 (see https://psalm.dev/182)
abstract class [97;41mAbstractAvailabilityValidatingService[0m extends AbstractEntityScopingService


[0;31mERROR[0m: PossiblyUnusedProperty - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L25\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:25:29[0m]8;;\ - Cannot find any references to property Roster\Services\Core\AbstractAvailabilityValidatingService::$currentAvailability (see https://psalm.dev/149)
    protected ?Availability [97;41m$currentAvailability[0m = null;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L82\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:82:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractAvailabilityValidatingService::between (see https://psalm.dev/087)
    public function [97;41mbetween[0m(Carbon $start, Carbon $end): Collection


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L130\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:130:5[0m]8;;\ - Method Roster\Services\Core\AbstractAvailabilityValidatingService::find should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mabstract public function find(int $id): mixed;[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L132\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:132:5[0m]8;;\ - Method Roster\Services\Core\AbstractAvailabilityValidatingService::get should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mabstract public function get(): Collection;[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L134\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:134:5[0m]8;;\ - Method Roster\Services\Core\AbstractAvailabilityValidatingService::buildquerywithfilters should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mabstract protected function buildQueryWithFilters(): Builder;[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L136\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:136:5[0m]8;;\ - Method Roster\Services\Core\AbstractAvailabilityValidatingService::clearentitycache should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mabstract protected function clearEntityCache(int $entityId): void;[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L138\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:138:33[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractAvailabilityValidatingService::getAvailabilityRepository (see https://psalm.dev/087)
    abstract protected function [97;41mgetAvailabilityRepository[0m(): mixed;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L140\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:140:33[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractAvailabilityValidatingService::getScheduleRepository (see https://psalm.dev/087)
    abstract protected function [97;41mgetScheduleRepository[0m(): mixed;


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractAvailabilityValidatingService.php#L142\src/Services/Core/[1;31mAbstractAvailabilityValidatingService.php:142:33[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractAvailabilityValidatingService::getImpedimentRepository (see https://psalm.dev/087)
    abstract protected function [97;41mgetImpedimentRepository[0m(): mixed;


[0;31mERROR[0m: PossiblyUnusedProperty - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L24\src/Services/Core/[1;31mAbstractEntityScopingService.php:24:21[0m]8;;\ - Cannot find any references to property Roster\Services\Core\AbstractEntityScopingService::$currentEntity (see https://psalm.dev/149)
    protected mixed [97;41m$currentEntity[0m = null;


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L32\src/Services/Core/[1;31mAbstractEntityScopingService.php:32:5[0m]8;;\ - Method Roster\Services\Core\AbstractEntityScopingService::setfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Set multiple filters at once.
     *
     * @param array<string, mixed> $filters Associative array of filter key-value pairs
     * @return $this
     */
    [97;41mfinal public function setFilters(array $filters): static[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L44\src/Services/Core/[1;31mAbstractEntityScopingService.php:44:27[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractEntityScopingService::whereType (see https://psalm.dev/087)
    final public function [97;41mwhereType[0m(string $type): static


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L55\src/Services/Core/[1;31mAbstractEntityScopingService.php:55:27[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractEntityScopingService::resetFilters (see https://psalm.dev/087)
    final public function [97;41mresetFilters[0m(): static


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L66\src/Services/Core/[1;31mAbstractEntityScopingService.php:66:27[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractEntityScopingService::getAll (see https://psalm.dev/087)
    final public function [97;41mgetAll[0m(): Collection


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L79\src/Services/Core/[1;31mAbstractEntityScopingService.php:79:30[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractEntityScopingService::applyCreateConfigurationRules (see https://psalm.dev/087)
    final protected function [97;41mapplyCreateConfigurationRules[0m(array $data): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L90\src/Services/Core/[1;31mAbstractEntityScopingService.php:90:30[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractEntityScopingService::applyUpdateConfigurationRules (see https://psalm.dev/087)
    final protected function [97;41mapplyUpdateConfigurationRules[0m(array $data): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractEntityScopingService.php#L131\src/Services/Core/[1;31mAbstractEntityScopingService.php:131:27[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractEntityScopingService::getEntityDisplayName (see https://psalm.dev/087)
    final public function [97;41mgetEntityDisplayName[0m(): string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L59\src/Services/Core/[1;31mAbstractService.php:59:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::getData (see https://psalm.dev/087)
    public function [97;41mgetData[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L70\src/Services/Core/[1;31mAbstractService.php:70:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::setData (see https://psalm.dev/087)
    public function [97;41msetData[0m(array $data): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L81\src/Services/Core/[1;31mAbstractService.php:81:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::getFilters (see https://psalm.dev/087)
    public function [97;41mgetFilters[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L92\src/Services/Core/[1;31mAbstractService.php:92:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::setFilters (see https://psalm.dev/087)
    public function [97;41msetFilters[0m(array $filters): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L103\src/Services/Core/[1;31mAbstractService.php:103:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::getSchedulable (see https://psalm.dev/087)
    public function [97;41mgetSchedulable[0m(): ?Model


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L114\src/Services/Core/[1;31mAbstractService.php:114:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::setSchedulable (see https://psalm.dev/087)
    public function [97;41msetSchedulable[0m(Model $model): self


[0;31mERROR[0m: PossiblyUnusedReturnValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L120\src/Services/Core/[1;31mAbstractService.php:120:51[0m]8;;\ - The return value for this method is never used (see https://psalm.dev/273)
    public function update(int $id, array $data): [97;41mbool[0m


[0;31mERROR[0m: UnusedVariable - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L124\src/Services/Core/[1;31mAbstractService.php:124:9[0m]8;;\ - $data is never referenced or the value is not used (see https://psalm.dev/077)
        [97;41m$data[0m = array_diff_key($data, array_flip(['schedulable_id', 'schedulable_type', 'availability_id']));


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L135\src/Services/Core/[1;31mAbstractService.php:135:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::for (see https://psalm.dev/087)
    public function [97;41mfor[0m(Model $model): static


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L148\src/Services/Core/[1;31mAbstractService.php:148:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::setFilter (see https://psalm.dev/087)
    public function [97;41msetFilter[0m(string $key, mixed $value): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractService.php#L159\src/Services/Core/[1;31mAbstractService.php:159:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::clear (see https://psalm.dev/087)
    public function [97;41mclear[0m(): self


[0;31mERROR[0m: MissingTemplateParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractValidatingService.php#L17\src/Services/Core/[1;31mAbstractValidatingService.php:17:16[0m]8;;\ - Roster\Services\Core\AbstractValidatingService has missing template params when extending Roster\Services\Core\AbstractEntityScopingService, expecting 1 (see https://psalm.dev/182)
abstract class [97;41mAbstractValidatingService[0m extends AbstractEntityScopingService


[0;31mERROR[0m: UnusedDocblockParam - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractValidatingService.php#L30\src/Services/Core/[1;31mAbstractValidatingService.php:30:36[0m]8;;\ - Docblock parameter $flags in docblock for Roster\Services\Core\AbstractValidatingService::validate does not have a counterpart in signature parameter list (see https://psalm.dev/319)
    /**
     * Validate data against rules.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> [97;41m$flags[0m Additional flags to pass to validation context
     */
    protected function validate(


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AbstractValidatingService.php#L38\src/Services/Core/[1;31mAbstractValidatingService.php:38:26[0m]8;;\ - Operand of type int|null contains type int, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        $currentEntity = [30;47m$entityId[0m ? $this->find($entityId) : null;


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L13\src/Services/Core/[1;31mAvailabilityChecker.php:13:7[0m]8;;\ - Class Roster\Services\Core\AvailabilityChecker is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityChecker[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L15\src/Services/Core/[1;31mAvailabilityChecker.php:15:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AvailabilityChecker::__construct (see https://psalm.dev/087)
    public function [97;41m__construct[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L26\src/Services/Core/[1;31mAvailabilityChecker.php:26:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AvailabilityChecker::isAvailableAt (see https://psalm.dev/087)
    public function [97;41misAvailableAt[0m(Model $model, Carbon $datetime): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/AvailabilityChecker.php#L41\src/Services/Core/[1;31mAvailabilityChecker.php:41:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AvailabilityChecker::isAvailableForPeriod (see https://psalm.dev/087)
    public function [97;41misAvailableForPeriod[0m(


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ResourcePublisherService.php#L14\src/Services/Core/[1;31mResourcePublisherService.php:14:7[0m]8;;\ - Class Roster\Services\Core\ResourcePublisherService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mResourcePublisherService[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ResourcePublisherService.php#L66\src/Services/Core/[1;31mResourcePublisherService.php:66:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\ResourcePublisherService::publishResource (see https://psalm.dev/087)
    public function [97;41mpublishResource[0m(


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/ResourcePublisherService.php#L102\src/Services/Core/[1;31mResourcePublisherService.php:102:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\ResourcePublisherService::isPublished (see https://psalm.dev/087)
    public function [97;41misPublished[0m(string $resourceType): bool


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L18\src/Services/Core/[1;31mSlotFinderService.php:18:7[0m]8;;\ - Class Roster\Services\Core\SlotFinderService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSlotFinderService[0m implements SlotFinderInterface


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L20\src/Services/Core/[1;31mSlotFinderService.php:20:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\SlotFinderService::__construct (see https://psalm.dev/087)
    public function [97;41m__construct[0m(


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L34\src/Services/Core/[1;31mSlotFinderService.php:34:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::isperiodavailable should have the "Override" attribute (see https://psalm.dev/358)
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


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L58\src/Services/Core/[1;31mSlotFinderService.php:58:43[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
                    if ($type !== null && [30;47m$availability->type[0m !== $type) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L63\src/Services/Core/[1;31mSlotFinderService.php:63:25[0m]8;;\ - Magic instance property Roster\Models\Availability::$start_time is not defined (see https://psalm.dev/218)
                        [30;47m$availability->start_time[0m->format('H:i') <= $current->format('H:i') &&


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L64\src/Services/Core/[1;31mSlotFinderService.php:64:25[0m]8;;\ - Magic instance property Roster\Models\Availability::$end_time is not defined (see https://psalm.dev/218)
                        [30;47m$availability->end_time[0m->format('H:i') >= $slotEnd->format('H:i');


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L88\src/Services/Core/[1;31mSlotFinderService.php:88:5[0m]8;;\ - Method Roster\Services\Core\SlotFinderService::calculateAvailableSlotsExcludingImpediments should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Calculate available time slots by removing impediments from a time range.
     *
     * @param Carbon $start Start of the time range
     * @param Carbon $end End of the time range
     * @param Collection $impediments Collection of impediments
     * @return Collection<int, array<string, mixed>> Available time slots
     */
    [97;41mpublic function calculateAvailableSlotsExcludingImpediments([0m


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L106\src/Services/Core/[1;31mSlotFinderService.php:106:39[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array{end: Illuminate\Support\Carbon&static, start: Illuminate\Support\Carbon&static} provided (see https://psalm.dev/004)
                $availableSlots->push([30;47m[
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ][0m);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L116\src/Services/Core/[1;31mSlotFinderService.php:116:35[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array{end: Illuminate\Support\Carbon&static, start: Illuminate\Support\Carbon&static} provided (see https://psalm.dev/004)
            $availableSlots->push([30;47m[
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ][0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L140\src/Services/Core/[1;31mSlotFinderService.php:140:35[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedules is not defined (see https://psalm.dev/218)
        $hasOverlappingSchedule = [30;47m$availability->schedules[0m->contains(


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L141\src/Services/Core/[1;31mSlotFinderService.php:141:16[0m]8;;\ - Parameter $schedule has no provided type (see https://psalm.dev/153)
            fn([30;47m$schedule[0m): bool => $schedule->overlapsWith($start, $end)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L144\src/Services/Core/[1;31mSlotFinderService.php:144:38[0m]8;;\ - Magic instance property Roster\Models\Availability::$impediments is not defined (see https://psalm.dev/218)
        $hasOverlappingImpediments = [30;47m$availability->impediments[0m->contains(


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/Core/SlotFinderService.php#L145\src/Services/Core/[1;31mSlotFinderService.php:145:16[0m]8;;\ - Parameter $impediment has no provided type (see https://psalm.dev/153)
            fn([30;47m$impediment[0m): bool => $impediment->overlapsWith($start, $end)


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L26\src/Services/[1;31mImpedimentService.php:26:7[0m]8;;\ - Class Roster\Services\ImpedimentService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mImpedimentService[0m extends AbstractAvailabilityValidatingService


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L53\src/Services/[1;31mImpedimentService.php:53:5[0m]8;;\ - Method Roster\Services\ImpedimentService::createdtofromarray should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function createDTOFromArray(array $data, OperationType $operationType): ImpedimentData[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L61\src/Services/[1;31mImpedimentService.php:61:5[0m]8;;\ - Method Roster\Services\ImpedimentService::getentitytypeenum should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getEntityTypeEnum(): EntityType[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L73\src/Services/[1;31mImpedimentService.php:73:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::create (see https://psalm.dev/087)
    public function [97;41mcreate[0m(Availability $availability, array $data): Impediment


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L76\src/Services/[1;31mImpedimentService.php:76:34[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
            'availability_id' => [30;47m$availability->id[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L77\src/Services/[1;31mImpedimentService.php:77:33[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            'schedulable_id' => [30;47m$this->schedulable->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L77\src/Services/[1;31mImpedimentService.php:77:33[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            'schedulable_id' => [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L78\src/Services/[1;31mImpedimentService.php:78:45[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            'schedulable_type' => get_class([30;47m$this->schedulable[0m)


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L109\src/Services/[1;31mImpedimentService.php:109:5[0m]8;;\ - Method Roster\Services\ImpedimentService::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Update an existing impediment.
     * @param array<string, mixed> $data
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


INFO: MoreSpecificImplementedParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L109\src/Services/[1;31mImpedimentService.php:109:43[0m]8;;\ - Argument 2 of Roster\Services\ImpedimentService::update has the more specific type 'array<string, mixed>', expecting 'array<array-key, mixed>' as defined by Roster\Services\Core\AbstractService::update (see https://psalm.dev/140)
    public function update(int $id, array [30;47m$data[0m): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L156\src/Services/[1;31mImpedimentService.php:156:21[0m]8;;\ - Cannot find explicit calls to method Roster\Services\ImpedimentService::delete (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mdelete[0m(int $id): bool


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L196\src/Services/[1;31mImpedimentService.php:196:5[0m]8;;\ - Method Roster\Services\ImpedimentService::executecreate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function executeCreate(): Impediment[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L206\src/Services/[1;31mImpedimentService.php:206:5[0m]8;;\ - Method Roster\Services\ImpedimentService::executeupdate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function executeUpdate(int $id): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L216\src/Services/[1;31mImpedimentService.php:216:5[0m]8;;\ - Method Roster\Services\ImpedimentService::executedelete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function executeDelete(int $id): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L226\src/Services/[1;31mImpedimentService.php:226:5[0m]8;;\ - Method Roster\Services\ImpedimentService::clearentitycache should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function clearEntityCache(int $entityId): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L234\src/Services/[1;31mImpedimentService.php:234:5[0m]8;;\ - Method Roster\Services\ImpedimentService::find should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mpublic function find(int $id): ?Impediment[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L236\src/Services/[1;31mImpedimentService.php:236:16[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        return [30;47mImpediment::where('schedulable_id', $this->schedulable->id)[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L236\src/Services/[1;31mImpedimentService.php:236:52[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
        return Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L236\src/Services/[1;31mImpedimentService.php:236:52[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        return Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L237\src/Services/[1;31mImpedimentService.php:237:51[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            ->where('schedulable_type', get_class([30;47m$this->schedulable[0m))


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L244\src/Services/[1;31mImpedimentService.php:244:5[0m]8;;\ - Method Roster\Services\ImpedimentService::get should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mpublic function get(): Collection[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L252\src/Services/[1;31mImpedimentService.php:252:5[0m]8;;\ - Method Roster\Services\ImpedimentService::buildquerywithfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function buildQueryWithFilters(): Builder[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L254\src/Services/[1;31mImpedimentService.php:254:18[0m]8;;\ - Magic method Roster\Models\Impediment::where does not exist (see https://psalm.dev/219)
        $query = [30;47mImpediment::where('schedulable_id', $this->schedulable->id)[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L254\src/Services/[1;31mImpedimentService.php:254:54[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
        $query = Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L254\src/Services/[1;31mImpedimentService.php:254:54[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        $query = Impediment::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L255\src/Services/[1;31mImpedimentService.php:255:51[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            ->where('schedulable_type', get_class([30;47m$this->schedulable[0m));


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L267\src/Services/[1;31mImpedimentService.php:267:5[0m]8;;\ - Method Roster\Services\ImpedimentService::getavailabilityrepository should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getAvailabilityRepository(): AvailabilityRepositoryInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L267\src/Services/[1;31mImpedimentService.php:267:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::getAvailabilityRepository (see https://psalm.dev/087)
    protected function [97;41mgetAvailabilityRepository[0m(): AvailabilityRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L275\src/Services/[1;31mImpedimentService.php:275:5[0m]8;;\ - Method Roster\Services\ImpedimentService::getschedulerepository should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getScheduleRepository(): ScheduleRepositoryInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L275\src/Services/[1;31mImpedimentService.php:275:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::getScheduleRepository (see https://psalm.dev/087)
    protected function [97;41mgetScheduleRepository[0m(): ScheduleRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L283\src/Services/[1;31mImpedimentService.php:283:5[0m]8;;\ - Method Roster\Services\ImpedimentService::getimpedimentrepository should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getImpedimentRepository(): ImpedimentRepositoryInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L283\src/Services/[1;31mImpedimentService.php:283:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::getImpedimentRepository (see https://psalm.dev/087)
    protected function [97;41mgetImpedimentRepository[0m(): ImpedimentRepositoryInterface


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L291\src/Services/[1;31mImpedimentService.php:291:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::isTimeSlotBlocked (see https://psalm.dev/087)
    public function [97;41misTimeSlotBlocked[0m(Carbon $start, Carbon $end, ?string $type = null): bool


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L293\src/Services/[1;31mImpedimentService.php:293:72[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
        $availability = $this->availabilityRepository->findForTimeSlot([30;47m$this->schedulable[0m, $start, $end, $type);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L299\src/Services/[1;31mImpedimentService.php:299:71[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
        return $this->impedimentRepository->hasOverlappingImpediments([30;47m$availability->id[0m, $start, $end);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L305\src/Services/[1;31mImpedimentService.php:305:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::getAvailableTimeSlots (see https://psalm.dev/087)
    public function [97;41mgetAvailableTimeSlots[0m(Carbon $start, Carbon $end, ?string $type = null): Collection


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L307\src/Services/[1;31mImpedimentService.php:307:72[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
        $availability = $this->availabilityRepository->findForTimeSlot([30;47m$this->schedulable[0m, $start, $end, $type);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L313\src/Services/[1;31mImpedimentService.php:313:69[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
        $impediments = $this->impedimentRepository->findForTimeSlot([30;47m$availability->id[0m, $start, $end);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L315\src/Services/[1;31mImpedimentService.php:315:82[0m]8;;\ - Argument 3 of Roster\Contracts\Services\SlotFinderInterface::calculateAvailableSlotsExcludingImpediments expects Illuminate\Support\Collection<array-key, mixed>, but Illuminate\Support\Collection<int, Roster\Models\Impediment> provided (see https://psalm.dev/004)
        return $this->slotFinder->calculateAvailableSlotsExcludingImpediments($start, $end, [30;47m$impediments[0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L321\src/Services/[1;31mImpedimentService.php:321:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::wouldOverlapWithSchedule (see https://psalm.dev/087)
    public function [97;41mwouldOverlapWithSchedule[0m(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ImpedimentService.php#L329\src/Services/[1;31mImpedimentService.php:329:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ImpedimentService::wouldOverlapWithOtherImpediment (see https://psalm.dev/087)
    public function [97;41mwouldOverlapWithOtherImpediment[0m(int $availabilityId, Carbon $start, Carbon $end, ?int $exceptImpedimentId = null): bool


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L26\src/Services/[1;31mScheduleService.php:26:7[0m]8;;\ - Class Roster\Services\ScheduleService is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mScheduleService[0m extends AbstractAvailabilityValidatingService


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L49\src/Services/[1;31mScheduleService.php:49:5[0m]8;;\ - Method Roster\Services\ScheduleService::createdtofromarray should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function createDTOFromArray(array $data, OperationType $operationType): ScheduleData[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L57\src/Services/[1;31mScheduleService.php:57:5[0m]8;;\ - Method Roster\Services\ScheduleService::getentitytypeenum should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getEntityTypeEnum(): EntityType[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L69\src/Services/[1;31mScheduleService.php:69:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::create (see https://psalm.dev/087)
    public function [97;41mcreate[0m(Availability $availability, array $data): Schedule


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L72\src/Services/[1;31mScheduleService.php:72:34[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
            'availability_id' => [30;47m$availability->id[0m,


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L73\src/Services/[1;31mScheduleService.php:73:33[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            'schedulable_id' => [30;47m$this->schedulable->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L73\src/Services/[1;31mScheduleService.php:73:33[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            'schedulable_id' => [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L74\src/Services/[1;31mScheduleService.php:74:45[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            'schedulable_type' => get_class([30;47m$this->schedulable[0m)


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L104\src/Services/[1;31mScheduleService.php:104:5[0m]8;;\ - Method Roster\Services\ScheduleService::update should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * Update an existing schedule.
     * @param array<string, mixed> $data
     */
    [97;41mpublic function update(int $id, array $data): bool[0m


INFO: MoreSpecificImplementedParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L104\src/Services/[1;31mScheduleService.php:104:43[0m]8;;\ - Argument 2 of Roster\Services\ScheduleService::update has the more specific type 'array<string, mixed>', expecting 'array<array-key, mixed>' as defined by Roster\Services\Core\AbstractService::update (see https://psalm.dev/140)
    public function update(int $id, array [30;47m$data[0m): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L155\src/Services/[1;31mScheduleService.php:155:21[0m]8;;\ - Cannot find explicit calls to method Roster\Services\ScheduleService::delete (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mdelete[0m(int $id): bool


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L174\src/Services/[1;31mScheduleService.php:174:33[0m]8;;\ - Magic instance property Roster\Models\Schedule::$schedulable_id is not defined (see https://psalm.dev/218)
            'schedulable_id' => [30;47m$entity->schedulable_id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L175\src/Services/[1;31mScheduleService.php:175:35[0m]8;;\ - Magic instance property Roster\Models\Schedule::$schedulable_type is not defined (see https://psalm.dev/218)
            'schedulable_type' => [30;47m$entity->schedulable_type[0m,


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L196\src/Services/[1;31mScheduleService.php:196:5[0m]8;;\ - Method Roster\Services\ScheduleService::executecreate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function executeCreate(): Schedule[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L204\src/Services/[1;31mScheduleService.php:204:5[0m]8;;\ - Method Roster\Services\ScheduleService::executeupdate should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function executeUpdate(int $id): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L212\src/Services/[1;31mScheduleService.php:212:5[0m]8;;\ - Method Roster\Services\ScheduleService::executedelete should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function executeDelete(int $id): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L220\src/Services/[1;31mScheduleService.php:220:5[0m]8;;\ - Method Roster\Services\ScheduleService::clearentitycache should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function clearEntityCache(int $entityId): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L228\src/Services/[1;31mScheduleService.php:228:5[0m]8;;\ - Method Roster\Services\ScheduleService::find should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mpublic function find(int $id): ?Schedule[0m


INFO: UndefinedMagicMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L230\src/Services/[1;31mScheduleService.php:230:16[0m]8;;\ - Magic method Roster\Models\Schedule::where does not exist (see https://psalm.dev/219)
        return [30;47mSchedule::where('schedulable_id', $this->schedulable->id)[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L230\src/Services/[1;31mScheduleService.php:230:50[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
        return Schedule::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L230\src/Services/[1;31mScheduleService.php:230:50[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
        return Schedule::where('schedulable_id', [30;47m$this->schedulable->id[0m)


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L231\src/Services/[1;31mScheduleService.php:231:51[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            ->where('schedulable_type', get_class([30;47m$this->schedulable[0m))


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L238\src/Services/[1;31mScheduleService.php:238:5[0m]8;;\ - Method Roster\Services\ScheduleService::get should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mpublic function get(): Collection[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L246\src/Services/[1;31mScheduleService.php:246:5[0m]8;;\ - Method Roster\Services\ScheduleService::buildquerywithfilters should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function buildQueryWithFilters(): Builder[0m


INFO: PossiblyNullPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L249\src/Services/[1;31mScheduleService.php:249:13[0m]8;;\ - Cannot get property on possibly null variable $this->schedulable of type Illuminate\Database\Eloquent\Model|null (see https://psalm.dev/082)
            [30;47m$this->schedulable->id[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L249\src/Services/[1;31mScheduleService.php:249:13[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L249\src/Services/[1;31mScheduleService.php:249:13[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\ScheduleRepositoryInterface::buildQueryWithFilters cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable->id[0m,


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L250\src/Services/[1;31mScheduleService.php:250:23[0m]8;;\ - Argument 1 of get_class cannot be null, possibly null value provided (see https://psalm.dev/078)
            get_class([30;47m$this->schedulable[0m),


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L258\src/Services/[1;31mScheduleService.php:258:5[0m]8;;\ - Method Roster\Services\ScheduleService::getavailabilityrepository should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getAvailabilityRepository(): AvailabilityRepositoryInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L258\src/Services/[1;31mScheduleService.php:258:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::getAvailabilityRepository (see https://psalm.dev/087)
    protected function [97;41mgetAvailabilityRepository[0m(): AvailabilityRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L266\src/Services/[1;31mScheduleService.php:266:5[0m]8;;\ - Method Roster\Services\ScheduleService::getschedulerepository should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getScheduleRepository(): ScheduleRepositoryInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L266\src/Services/[1;31mScheduleService.php:266:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::getScheduleRepository (see https://psalm.dev/087)
    protected function [97;41mgetScheduleRepository[0m(): ScheduleRepositoryInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L274\src/Services/[1;31mScheduleService.php:274:5[0m]8;;\ - Method Roster\Services\ScheduleService::getimpedimentrepository should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * {@inheritDoc}
     */
    [97;41mprotected function getImpedimentRepository(): ImpedimentRepositoryInterface[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L274\src/Services/[1;31mScheduleService.php:274:24[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::getImpedimentRepository (see https://psalm.dev/087)
    protected function [97;41mgetImpedimentRepository[0m(): ImpedimentRepositoryInterface


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L282\src/Services/[1;31mScheduleService.php:282:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::findNextSlot (see https://psalm.dev/087)
    public function [97;41mfindNextSlot[0m(


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L321\src/Services/[1;31mScheduleService.php:321:13[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlotWithConflictInfo cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L328\src/Services/[1;31mScheduleService.php:328:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$has_overlapping_schedules is not defined (see https://psalm.dev/218)
            && ![30;47m$availability->has_overlapping_schedules[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L329\src/Services/[1;31mScheduleService.php:329:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$has_overlapping_impediments is not defined (see https://psalm.dev/218)
            && ![30;47m$availability->has_overlapping_impediments[0m;


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L343\src/Services/[1;31mScheduleService.php:343:13[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::getForDate cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L379\src/Services/[1;31mScheduleService.php:379:14[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_start is not defined (see https://psalm.dev/218)
        if (![30;47m$availability->daily_start[0m || !$availability->daily_end) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L379\src/Services/[1;31mScheduleService.php:379:45[0m]8;;\ - Magic instance property Roster\Models\Availability::$daily_end is not defined (see https://psalm.dev/218)
        if (!$availability->daily_start || ![30;47m$availability->daily_end[0m) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L429\src/Services/[1;31mScheduleService.php:429:66[0m]8;;\ - Magic instance property Roster\Models\Availability::$type is not defined (see https://psalm.dev/218)
            if ($this->isTimeSlotAvailable($slotStart, $slotEnd, [30;47m$availability->type[0m)) {


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L448\src/Services/[1;31mScheduleService.php:448:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::findAvailableSlots (see https://psalm.dev/087)
    public function [97;41mfindAvailableSlots[0m(


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L461\src/Services/[1;31mScheduleService.php:461:39[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array<array-key, mixed> provided (see https://psalm.dev/004)
                $availableSlots->push([30;47m$slot[0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L473\src/Services/[1;31mScheduleService.php:473:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::isPeriodAvailable (see https://psalm.dev/087)
    public function [97;41misPeriodAvailable[0m(


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L480\src/Services/[1;31mScheduleService.php:480:13[0m]8;;\ - Argument 1 of Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot cannot be null, possibly null value provided (see https://psalm.dev/078)
            [30;47m$this->schedulable[0m,


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L497\src/Services/[1;31mScheduleService.php:497:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$id is not defined (see https://psalm.dev/218)
            [30;47m$availability->id[0m,


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L514\src/Services/[1;31mScheduleService.php:514:21[0m]8;;\ - Cannot find any calls to method Roster\Services\ScheduleService::calculateAvailableSlotsExcludingImpediments (see https://psalm.dev/087)
    public function [97;41mcalculateAvailableSlotsExcludingImpediments[0m(


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L523\src/Services/[1;31mScheduleService.php:523:35[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array{end: Illuminate\Support\Carbon&static, start: Illuminate\Support\Carbon&static} provided (see https://psalm.dev/004)
            $availableSlots->push([30;47m[
                'start' => $start->copy(),
                'end' => $end->copy(),
            ][0m);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L540\src/Services/[1;31mScheduleService.php:540:39[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array{end: Illuminate\Support\Carbon&static, start: Illuminate\Support\Carbon&static} provided (see https://psalm.dev/004)
                $availableSlots->push([30;47m[
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ][0m);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Services/ScheduleService.php#L552\src/Services/[1;31mScheduleService.php:552:35[0m]8;;\ - Argument 1 of Illuminate\Support\Collection::push expects never, but array{end: Illuminate\Support\Carbon&static, start: Illuminate\Support\Carbon&static} provided (see https://psalm.dev/004)
            $availableSlots->push([30;47m[
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ][0m);


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L23\src/Traits/[1;31mBelongsToSchedulable.php:23:31[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::bootBelongsToSchedulable (see https://psalm.dev/087)
    protected static function [97;41mbootBelongsToSchedulable[0m(): void


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L23\src/Traits/[1;31mBelongsToSchedulable.php:23:31[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::bootBelongsToSchedulable (see https://psalm.dev/087)
    protected static function [97;41mbootBelongsToSchedulable[0m(): void


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L23\src/Traits/[1;31mBelongsToSchedulable.php:23:31[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::bootBelongsToSchedulable (see https://psalm.dev/087)
    protected static function [97;41mbootBelongsToSchedulable[0m(): void


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L36\src/Traits/[1;31mBelongsToSchedulable.php:36:47[0m]8;;\ - Argument 2 of Roster\Models\Schedule::addGlobalScope expects Closure(Illuminate\Database\Eloquent\Builder<Roster\Models\Schedule&static>):mixed|Illuminate\Database\Eloquent\Scope|null, but impure-Closure(Illuminate\Database\Eloquent\Builder<static>):void provided (see https://psalm.dev/004)
        static::addGlobalScope('schedulable', [30;47mfunction (Builder $builder): void {
            $model = $builder->getModel();

            if ($model->schedulable_id && $model->schedulable_type) {
                $builder->where('schedulable_id', $model->schedulable_id)
                    ->where('schedulable_type', $model->schedulable_type);
            }
        }[0m);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L36\src/Traits/[1;31mBelongsToSchedulable.php:36:47[0m]8;;\ - Argument 2 of Roster\Models\Availability::addGlobalScope expects Closure(Illuminate\Database\Eloquent\Builder<Roster\Models\Availability&static>):mixed|Illuminate\Database\Eloquent\Scope|null, but impure-Closure(Illuminate\Database\Eloquent\Builder<static>):void provided (see https://psalm.dev/004)
        static::addGlobalScope('schedulable', [30;47mfunction (Builder $builder): void {
            $model = $builder->getModel();

            if ($model->schedulable_id && $model->schedulable_type) {
                $builder->where('schedulable_id', $model->schedulable_id)
                    ->where('schedulable_type', $model->schedulable_type);
            }
        }[0m);


INFO: InvalidArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L36\src/Traits/[1;31mBelongsToSchedulable.php:36:47[0m]8;;\ - Argument 2 of Roster\Models\Impediment::addGlobalScope expects Closure(Illuminate\Database\Eloquent\Builder<Roster\Models\Impediment&static>):mixed|Illuminate\Database\Eloquent\Scope|null, but impure-Closure(Illuminate\Database\Eloquent\Builder<static>):void provided (see https://psalm.dev/004)
        static::addGlobalScope('schedulable', [30;47mfunction (Builder $builder): void {
            $model = $builder->getModel();

            if ($model->schedulable_id && $model->schedulable_type) {
                $builder->where('schedulable_id', $model->schedulable_id)
                    ->where('schedulable_type', $model->schedulable_type);
            }
        }[0m);


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L39\src/Traits/[1;31mBelongsToSchedulable.php:39:17[0m]8;;\ - Magic instance property Roster\Models\Schedule::$schedulable_id is not defined (see https://psalm.dev/218)
            if ([30;47m$model->schedulable_id[0m && $model->schedulable_type) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L39\src/Traits/[1;31mBelongsToSchedulable.php:39:17[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            if ([30;47m$model->schedulable_id[0m && $model->schedulable_type) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L39\src/Traits/[1;31mBelongsToSchedulable.php:39:43[0m]8;;\ - Magic instance property Roster\Models\Schedule::$schedulable_type is not defined (see https://psalm.dev/218)
            if ($model->schedulable_id && [30;47m$model->schedulable_type[0m) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L39\src/Traits/[1;31mBelongsToSchedulable.php:39:43[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            if ($model->schedulable_id && [30;47m$model->schedulable_type[0m) {


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L57\src/Traits/[1;31mBelongsToSchedulable.php:57:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Availability::scopeForSchedulable (see https://psalm.dev/087)
    public function [97;41mscopeForSchedulable[0m(Builder $builder, Model $model): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L57\src/Traits/[1;31mBelongsToSchedulable.php:57:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Impediment::scopeForSchedulable (see https://psalm.dev/087)
    public function [97;41mscopeForSchedulable[0m(Builder $builder, Model $model): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/BelongsToSchedulable.php#L57\src/Traits/[1;31mBelongsToSchedulable.php:57:21[0m]8;;\ - Cannot find any calls to method Roster\Models\Schedule::scopeForSchedulable (see https://psalm.dev/087)
    public function [97;41mscopeForSchedulable[0m(Builder $builder, Model $model): Builder


INFO: PossiblyNullReference - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L47\src/Traits/[1;31mDateRangeOverlapTrait.php:47:36[0m]8;;\ - Cannot call method lte on possibly null value (see https://psalm.dev/083)
        return $effectiveNewStart->[30;47mlte[0m($effectiveExistingEnd) &&


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L47\src/Traits/[1;31mDateRangeOverlapTrait.php:47:40[0m]8;;\ - Argument 1 of Illuminate\Support\Carbon::lte cannot be null, possibly null value provided (see https://psalm.dev/078)
        return $effectiveNewStart->lte([30;47m$effectiveExistingEnd[0m) &&


INFO: PossiblyNullReference - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L48\src/Traits/[1;31mDateRangeOverlapTrait.php:48:31[0m]8;;\ - Cannot call method gte on possibly null value (see https://psalm.dev/083)
            $effectiveNewEnd->[30;47mgte[0m($effectiveExistingStart);


INFO: PossiblyNullArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/DateRangeOverlapTrait.php#L48\src/Traits/[1;31mDateRangeOverlapTrait.php:48:35[0m]8;;\ - Argument 1 of Illuminate\Support\Carbon::gte cannot be null, possibly null value provided (see https://psalm.dev/078)
            $effectiveNewEnd->gte([30;47m$effectiveExistingStart[0m);


[0;31mERROR[0m: PossiblyUnusedReturnValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L36\src/Traits/[1;31mFilterableTrait.php:36:16[0m]8;;\ - The return value for this method is never used (see https://psalm.dev/273)
     * @return [97;41mBuilder[0m The modified query builder


[0;31mERROR[0m: PossiblyUnusedReturnValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L62\src/Traits/[1;31mFilterableTrait.php:62:16[0m]8;;\ - The return value for this method is never used (see https://psalm.dev/273)
     * @return [97;41mBuilder[0m The modified query builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L88\src/Traits/[1;31mFilterableTrait.php:88:24[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::applyDayFilter (see https://psalm.dev/087)
    protected function [97;41mapplyDayFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L104\src/Traits/[1;31mFilterableTrait.php:104:24[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::applyStatusFilter (see https://psalm.dev/087)
    protected function [97;41mapplyStatusFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedReturnValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L118\src/Traits/[1;31mFilterableTrait.php:118:16[0m]8;;\ - The return value for this method is never used (see https://psalm.dev/273)
     * @return [97;41mBuilder[0m The modified query builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L136\src/Traits/[1;31mFilterableTrait.php:136:24[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::applyAvailabilityIdFilter (see https://psalm.dev/087)
    protected function [97;41mapplyAvailabilityIdFilter[0m(Builder $builder): Builder


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L153\src/Traits/[1;31mFilterableTrait.php:153:24[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::applySchedulableFilter (see https://psalm.dev/087)
    protected function [97;41mapplySchedulableFilter[0m(Builder $builder, ?Model $model = null): Builder


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L156\src/Traits/[1;31mFilterableTrait.php:156:47[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            $builder->where('schedulable_id', [30;47m$model->id[0m)


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L168\src/Traits/[1;31mFilterableTrait.php:168:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::whereStartDate (see https://psalm.dev/087)
    public function [97;41mwhereStartDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L180\src/Traits/[1;31mFilterableTrait.php:180:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::whereEndDate (see https://psalm.dev/087)
    public function [97;41mwhereEndDate[0m(Carbon $date): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L192\src/Traits/[1;31mFilterableTrait.php:192:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::whereStatus (see https://psalm.dev/087)
    public function [97;41mwhereStatus[0m(string $status): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L204\src/Traits/[1;31mFilterableTrait.php:204:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::whereReason (see https://psalm.dev/087)
    public function [97;41mwhereReason[0m(string $reason): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L216\src/Traits/[1;31mFilterableTrait.php:216:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::whereAvailabilityId (see https://psalm.dev/087)
    public function [97;41mwhereAvailabilityId[0m(int $availabilityId): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L226\src/Traits/[1;31mFilterableTrait.php:226:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::clearFilters (see https://psalm.dev/087)
    public function [97;41mclearFilters[0m(): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Traits/FilterableTrait.php#L250\src/Traits/[1;31mFilterableTrait.php:250:21[0m]8;;\ - Cannot find any calls to method Roster\Services\Core\AbstractService::hasFilter (see https://psalm.dev/087)
    public function [97;41mhasFilter[0m(string $key): bool


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Attributes/ValidationRule.php#L12\src/Validation/Attributes/[1;31mValidationRule.php:12:7[0m]8;;\ - Class Roster\Validation\Attributes\ValidationRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mValidationRule[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Cache/RuleCacheGenerator.php#L12\src/Validation/Cache/[1;31mRuleCacheGenerator.php:12:7[0m]8;;\ - Class Roster\Validation\Cache\RuleCacheGenerator is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mRuleCacheGenerator[0m


INFO: PossiblyFalseOperand - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Cache/RuleCacheGenerator.php#L56\src/Validation/Cache/[1;31mRuleCacheGenerator.php:56:26[0m]8;;\ - Right operand cannot be falsable, got false|int (see https://psalm.dev/162)
        return (time() - [30;47mfilemtime($this->cachePath)[0m) < ($maxAge * 3600);


INFO: PossiblyNullOperand - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Cache/RuleCacheGenerator.php#L104\src/Validation/Cache/[1;31mRuleCacheGenerator.php:104:58[0m]8;;\ - Cannot concatenate with a possibly null int|null (see https://psalm.dev/080)
        $entry .= $indent . $indent . "'priority' => " . [30;47m$attribute->priority[0m . ",\n";


INFO: InvalidOperand - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Cache/RuleCacheGenerator.php#L104\src/Validation/Cache/[1;31mRuleCacheGenerator.php:104:58[0m]8;;\ - Cannot concatenate with a int|null (see https://psalm.dev/058)
        $entry .= $indent . $indent . "'priority' => " . [30;47m$attribute->priority[0m . ",\n";


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L12\src/Validation/Context/[1;31mValidationContext.php:12:7[0m]8;;\ - Class Roster\Validation\Context\ValidationContext is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mValidationContext[0m implements ValidationContextInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L58\src/Validation/Context/[1;31mValidationContext.php:58:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::getoperation should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getOperation(): OperationType[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L63\src/Validation/Context/[1;31mValidationContext.php:63:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::getentitytype should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getEntityType(): EntityType[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L68\src/Validation/Context/[1;31mValidationContext.php:68:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::getschedulable should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getSchedulable(): ?Model[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L73\src/Validation/Context/[1;31mValidationContext.php:73:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::getcurrententity should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getCurrentEntity(): mixed[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L83\src/Validation/Context/[1;31mValidationContext.php:83:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::get should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function get(string $key, mixed $default = null): mixed[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L90\src/Validation/Context/[1;31mValidationContext.php:90:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::has should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function has(string $key): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L96\src/Validation/Context/[1;31mValidationContext.php:96:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::safedata should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function safeData(): array[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L109\src/Validation/Context/[1;31mValidationContext.php:109:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::rawget should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function rawGet(string $key, mixed $default = null): mixed[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L114\src/Validation/Context/[1;31mValidationContext.php:114:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::rawhas should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function rawHas(string $key): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L122\src/Validation/Context/[1;31mValidationContext.php:122:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::getdata should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * @return array<string, mixed>
     */
    [97;41mpublic function getData(): array[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L130\src/Validation/Context/[1;31mValidationContext.php:130:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::rawdata should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * @return array<string, mixed>
     */
    [97;41mpublic function rawData(): array[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L140\src/Validation/Context/[1;31mValidationContext.php:140:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::set should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function set(string $key, mixed $value): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L148\src/Validation/Context/[1;31mValidationContext.php:148:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::all should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * @return array<string, mixed>
     */
    [97;41mpublic function all(): array[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L158\src/Validation/Context/[1;31mValidationContext.php:158:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::setviolation should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function setViolation(string $field, string $message): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L163\src/Validation/Context/[1;31mValidationContext.php:163:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::addviolation should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function addViolation(string $field, string $message): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L179\src/Validation/Context/[1;31mValidationContext.php:179:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::getviolations should have the "Override" attribute (see https://psalm.dev/358)
    /**
     * @return array<string, string|array<int, string>>
     */
    [97;41mpublic function getViolations(): array[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L184\src/Validation/Context/[1;31mValidationContext.php:184:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::hasviolations should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function hasViolations(): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L194\src/Validation/Context/[1;31mValidationContext.php:194:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::setflag should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function setFlag(string $flag, mixed $value = true): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L199\src/Validation/Context/[1;31mValidationContext.php:199:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::hasflag should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function hasFlag(string $flag): bool[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Context/ValidationContext.php#L204\src/Validation/Context/[1;31mValidationContext.php:204:5[0m]8;;\ - Method Roster\Validation\Context\ValidationContext::getflag should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getFlag(string $flag, mixed $default = false): mixed[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Exceptions/ValidationFailedException.php#L12\src/Validation/Exceptions/[1;31mValidationFailedException.php:12:7[0m]8;;\ - Class Roster\Validation\Exceptions\ValidationFailedException is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mValidationFailedException[0m extends InvalidArgumentException


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Exceptions/ValidationFailedException.php#L43\src/Validation/Exceptions/[1;31mValidationFailedException.php:43:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Exceptions\ValidationFailedException::getViolations (see https://psalm.dev/087)
    public function [97;41mgetViolations[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Exceptions/ValidationFailedException.php#L48\src/Validation/Exceptions/[1;31mValidationFailedException.php:48:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Exceptions\ValidationFailedException::getOperation (see https://psalm.dev/087)
    public function [97;41mgetOperation[0m(): OperationType


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Exceptions/ValidationFailedException.php#L53\src/Validation/Exceptions/[1;31mValidationFailedException.php:53:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Exceptions\ValidationFailedException::getEntityType (see https://psalm.dev/087)
    public function [97;41mgetEntityType[0m(): EntityType


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Exceptions/ValidationFailedException.php#L58\src/Validation/Exceptions/[1;31mValidationFailedException.php:58:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Exceptions\ValidationFailedException::getFirstViolation (see https://psalm.dev/087)
    public function [97;41mgetFirstViolation[0m(): ?string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Exceptions/ValidationFailedException.php#L77\src/Validation/Exceptions/[1;31mValidationFailedException.php:77:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Exceptions\ValidationFailedException::toArray (see https://psalm.dev/087)
    public function [97;41mtoArray[0m(): array


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L15\src/Validation/[1;31mRuleScanner.php:15:7[0m]8;;\ - Class Roster\Validation\RuleScanner is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mRuleScanner[0m


INFO: PropertyNotSetInConstructor - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L22\src/Validation/[1;31mRuleScanner.php:22:18[0m]8;;\ - Property Roster\Validation\RuleScanner::$withCache is not defined in constructor of Roster\Validation\RuleScanner or in any private or final methods called in the constructor (see https://psalm.dev/074)
    private bool [30;47m$withCache[0m;


[0;31mERROR[0m: UnusedProperty - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L22\src/Validation/[1;31mRuleScanner.php:22:18[0m]8;;\ - Cannot find any references to private property Roster\Validation\RuleScanner::$withCache (see https://psalm.dev/150)
    private bool [97;41m$withCache[0m;


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L48\src/Validation/[1;31mRuleScanner.php:48:13[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m!$this->cacheFile[0m || !file_exists($this->cacheFile)) {


INFO: UnresolvableInclude - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L65\src/Validation/[1;31mRuleScanner.php:65:22[0m]8;;\ - Cannot resolve the given expression to a file path (see https://psalm.dev/106)
            $rules = [30;47mrequire $this->cacheFile[0m;


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L78\src/Validation/[1;31mRuleScanner.php:78:28[0m]8;;\ - Parameter $e has no provided type (see https://psalm.dev/153)
                        fn([30;47m$e[0m) => \Roster\Enums\EntityType::from($e),


INFO: MissingClosureParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L82\src/Validation/[1;31mRuleScanner.php:82:28[0m]8;;\ - Parameter $o has no provided type (see https://psalm.dev/153)
                        fn([30;47m$o[0m) => \Roster\Enums\OperationType::from($o),


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L131\src/Validation/[1;31mRuleScanner.php:131:21[0m]8;;\ - Operand of type null|string contains type string, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
                if ([30;47m$className[0m && class_exists($className)) {


[0;31mERROR[0m: UndefinedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L165\src/Validation/[1;31mRuleScanner.php:165:28[0m]8;;\ - Type array-key cannot be called as a class (see https://psalm.dev/019)
                $rules[] = [97;41mnew $className()[0m;


INFO: PossiblyFalseArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L175\src/Validation/[1;31mRuleScanner.php:175:50[0m]8;;\ - Argument 2 of preg_match cannot be false, possibly string value expected (see https://psalm.dev/104)
        if (preg_match('/namespace\s+([^;]+);/', [30;47m$content[0m, $namespaceMatches)) {


INFO: PossiblyFalseArgument - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/RuleScanner.php#L177\src/Validation/[1;31mRuleScanner.php:177:47[0m]8;;\ - Argument 2 of preg_match cannot be false, possibly string value expected (see https://psalm.dev/104)
            if (preg_match('/class\s+(\w+)/', [30;47m$content[0m, $classMatches)) {


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AbstractRule.php#L15\src/Validation/Rules/[1;31mAbstractRule.php:15:5[0m]8;;\ - Method Roster\Validation\Rules\AbstractRule::getpriority should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getPriority(): int[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AbstractRule.php#L29\src/Validation/Rules/[1;31mAbstractRule.php:29:5[0m]8;;\ - Method Roster\Validation\Rules\AbstractRule::getname should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getName(): string[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AbstractRule.php#L34\src/Validation/Rules/[1;31mAbstractRule.php:34:5[0m]8;;\ - Method Roster\Validation\Rules\AbstractRule::supports should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function supports(OperationType $operationType, EntityType $entityType): bool[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AbstractRule.php#L93\src/Validation/Rules/[1;31mAbstractRule.php:93:24[0m]8;;\ - Cannot find any calls to method Roster\Validation\Rules\AbstractRule::getDefaultTimezone (see https://psalm.dev/087)
    protected function [97;41mgetDefaultTimezone[0m(): string


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AbstractRule.php#L102\src/Validation/Rules/[1;31mAbstractRule.php:102:21[0m]8;;\ - Cannot find explicit calls to method Roster\Validation\Rules\AbstractRule::getValidationRuleAttribute (but did find some potential callers) (see https://psalm.dev/087)
    public function [97;41mgetValidationRuleAttribute[0m(): ?ValidationRule


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDateRangeRule.php#L19\src/Validation/Rules/[1;31mAvailabilityDateRangeRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityDateRangeRule is never used (see https://psalm.dev/075)
class [97;41mAvailabilityDateRangeRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDateRangeRule.php#L19\src/Validation/Rules/[1;31mAvailabilityDateRangeRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityDateRangeRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityDateRangeRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDateRangeRule.php#L21\src/Validation/Rules/[1;31mAvailabilityDateRangeRule.php:21:5[0m]8;;\ - Method Roster\Validation\Rules\AvailabilityDateRangeRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDateRangeRule.php#L191\src/Validation/Rules/[1;31mAvailabilityDateRangeRule.php:191:5[0m]8;;\ - Method Roster\Validation\Rules\AvailabilityDateRangeRule::getmaxdays should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mprotected function getMaxDays(): int[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysCoherenceRule.php#L20\src/Validation/Rules/[1;31mAvailabilityDaysCoherenceRule.php:20:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityDaysCoherenceRule is never used (see https://psalm.dev/075)
class [97;41mAvailabilityDaysCoherenceRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysCoherenceRule.php#L20\src/Validation/Rules/[1;31mAvailabilityDaysCoherenceRule.php:20:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityDaysCoherenceRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityDaysCoherenceRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysCoherenceRule.php#L22\src/Validation/Rules/[1;31mAvailabilityDaysCoherenceRule.php:22:5[0m]8;;\ - Method Roster\Validation\Rules\AvailabilityDaysCoherenceRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysCoherenceRule.php#L91\src/Validation/Rules/[1;31mAvailabilityDaysCoherenceRule.php:91:25[0m]8;;\ - Function Roster\Validation\Rules\roster_days_in_period does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        $daysInPeriod = [97;41mroster_days_in_period($validityStart, $validityEnd)[0m;


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityDaysCoherenceRule.php#L96\src/Validation/Rules/[1;31mAvailabilityDaysCoherenceRule.php:96:38[0m]8;;\ - Function Roster\Validation\Rules\roster_format_period_days_for_display does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
                $periodDescription = [97;41mroster_format_period_days_for_display($daysInPeriod)[0m;


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOverlapRule.php#L20\src/Validation/Rules/[1;31mAvailabilityOverlapRule.php:20:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityOverlapRule is never used (see https://psalm.dev/075)
class [97;41mAvailabilityOverlapRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOverlapRule.php#L20\src/Validation/Rules/[1;31mAvailabilityOverlapRule.php:20:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityOverlapRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityOverlapRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOverlapRule.php#L22\src/Validation/Rules/[1;31mAvailabilityOverlapRule.php:22:5[0m]8;;\ - Method Roster\Validation\Rules\AvailabilityOverlapRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOwnershipRule.php#L19\src/Validation/Rules/[1;31mAvailabilityOwnershipRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityOwnershipRule is never used (see https://psalm.dev/075)
class [97;41mAvailabilityOwnershipRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOwnershipRule.php#L19\src/Validation/Rules/[1;31mAvailabilityOwnershipRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityOwnershipRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityOwnershipRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOwnershipRule.php#L21\src/Validation/Rules/[1;31mAvailabilityOwnershipRule.php:21:5[0m]8;;\ - Method Roster\Validation\Rules\AvailabilityOwnershipRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOwnershipRule.php#L41\src/Validation/Rules/[1;31mAvailabilityOwnershipRule.php:41:13[0m]8;;\ - Operand of type mixed|null contains type mixed, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
        if ([30;47m!$availabilityId[0m) {


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOwnershipRule.php#L63\src/Validation/Rules/[1;31mAvailabilityOwnershipRule.php:63:13[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_id is not defined (see https://psalm.dev/218)
            [30;47m$availability->schedulable_id[0m !== $schedulable->id


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOwnershipRule.php#L63\src/Validation/Rules/[1;31mAvailabilityOwnershipRule.php:63:47[0m]8;;\ - Magic instance property Illuminate\Database\Eloquent\Model::$id is not defined (see https://psalm.dev/218)
            $availability->schedulable_id !== [30;47m$schedulable->id[0m


INFO: UndefinedMagicPropertyFetch - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityOwnershipRule.php#L64\src/Validation/Rules/[1;31mAvailabilityOwnershipRule.php:64:16[0m]8;;\ - Magic instance property Roster\Models\Availability::$schedulable_type is not defined (see https://psalm.dev/218)
            || [30;47m$availability->schedulable_type[0m !== get_class($schedulable)


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTimeRangeRule.php#L21\src/Validation/Rules/[1;31mAvailabilityTimeRangeRule.php:21:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityTimeRangeRule is never used (see https://psalm.dev/075)
class [97;41mAvailabilityTimeRangeRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTimeRangeRule.php#L21\src/Validation/Rules/[1;31mAvailabilityTimeRangeRule.php:21:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityTimeRangeRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityTimeRangeRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTimeRangeRule.php#L23\src/Validation/Rules/[1;31mAvailabilityTimeRangeRule.php:23:5[0m]8;;\ - Method Roster\Validation\Rules\AvailabilityTimeRangeRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


INFO: MissingParamType - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTimeRangeRule.php#L54\src/Validation/Rules/[1;31mAvailabilityTimeRangeRule.php:54:9[0m]8;;\ - Parameter $availability has no provided type (see https://psalm.dev/154)
        [30;47m$availability[0m,


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTypeRule.php#L17\src/Validation/Rules/[1;31mAvailabilityTypeRule.php:17:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityTypeRule is never used (see https://psalm.dev/075)
class [97;41mAvailabilityTypeRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTypeRule.php#L17\src/Validation/Rules/[1;31mAvailabilityTypeRule.php:17:7[0m]8;;\ - Class Roster\Validation\Rules\AvailabilityTypeRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mAvailabilityTypeRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/AvailabilityTypeRule.php#L19\src/Validation/Rules/[1;31mAvailabilityTypeRule.php:19:5[0m]8;;\ - Method Roster\Validation\Rules\AvailabilityTypeRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/DaysValidationRule.php#L18\src/Validation/Rules/[1;31mDaysValidationRule.php:18:7[0m]8;;\ - Class Roster\Validation\Rules\DaysValidationRule is never used (see https://psalm.dev/075)
class [97;41mDaysValidationRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/DaysValidationRule.php#L18\src/Validation/Rules/[1;31mDaysValidationRule.php:18:7[0m]8;;\ - Class Roster\Validation\Rules\DaysValidationRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mDaysValidationRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/DaysValidationRule.php#L20\src/Validation/Rules/[1;31mDaysValidationRule.php:20:5[0m]8;;\ - Method Roster\Validation\Rules\DaysValidationRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/DurationRule.php#L18\src/Validation/Rules/[1;31mDurationRule.php:18:7[0m]8;;\ - Class Roster\Validation\Rules\DurationRule is never used (see https://psalm.dev/075)
class [97;41mDurationRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/DurationRule.php#L18\src/Validation/Rules/[1;31mDurationRule.php:18:7[0m]8;;\ - Class Roster\Validation\Rules\DurationRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mDurationRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/DurationRule.php#L20\src/Validation/Rules/[1;31mDurationRule.php:20:5[0m]8;;\ - Method Roster\Validation\Rules\DurationRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/FutureDateRule.php#L19\src/Validation/Rules/[1;31mFutureDateRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\FutureDateRule is never used (see https://psalm.dev/075)
class [97;41mFutureDateRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/FutureDateRule.php#L19\src/Validation/Rules/[1;31mFutureDateRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\FutureDateRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mFutureDateRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/FutureDateRule.php#L21\src/Validation/Rules/[1;31mFutureDateRule.php:21:5[0m]8;;\ - Method Roster\Validation\Rules\FutureDateRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/RequiredFieldsRule.php#L17\src/Validation/Rules/[1;31mRequiredFieldsRule.php:17:7[0m]8;;\ - Class Roster\Validation\Rules\RequiredFieldsRule is never used (see https://psalm.dev/075)
class [97;41mRequiredFieldsRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/RequiredFieldsRule.php#L17\src/Validation/Rules/[1;31mRequiredFieldsRule.php:17:7[0m]8;;\ - Class Roster\Validation\Rules\RequiredFieldsRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mRequiredFieldsRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/RequiredFieldsRule.php#L19\src/Validation/Rules/[1;31mRequiredFieldsRule.php:19:5[0m]8;;\ - Method Roster\Validation\Rules\RequiredFieldsRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/SchedulableConsistencyRule.php#L19\src/Validation/Rules/[1;31mSchedulableConsistencyRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\SchedulableConsistencyRule is never used (see https://psalm.dev/075)
class [97;41mSchedulableConsistencyRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/SchedulableConsistencyRule.php#L19\src/Validation/Rules/[1;31mSchedulableConsistencyRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\SchedulableConsistencyRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSchedulableConsistencyRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/SchedulableConsistencyRule.php#L21\src/Validation/Rules/[1;31mSchedulableConsistencyRule.php:21:5[0m]8;;\ - Method Roster\Validation\Rules\SchedulableConsistencyRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/SchedulableValidationRule.php#L18\src/Validation/Rules/[1;31mSchedulableValidationRule.php:18:7[0m]8;;\ - Class Roster\Validation\Rules\SchedulableValidationRule is never used (see https://psalm.dev/075)
class [97;41mSchedulableValidationRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/SchedulableValidationRule.php#L18\src/Validation/Rules/[1;31mSchedulableValidationRule.php:18:7[0m]8;;\ - Class Roster\Validation\Rules\SchedulableValidationRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mSchedulableValidationRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/SchedulableValidationRule.php#L20\src/Validation/Rules/[1;31mSchedulableValidationRule.php:20:5[0m]8;;\ - Method Roster\Validation\Rules\SchedulableValidationRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php#L21\src/Validation/Rules/[1;31mScheduleOverlapRule.php:21:7[0m]8;;\ - Class Roster\Validation\Rules\ScheduleOverlapRule is never used (see https://psalm.dev/075)
class [97;41mScheduleOverlapRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php#L21\src/Validation/Rules/[1;31mScheduleOverlapRule.php:21:7[0m]8;;\ - Class Roster\Validation\Rules\ScheduleOverlapRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mScheduleOverlapRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php#L23\src/Validation/Rules/[1;31mScheduleOverlapRule.php:23:5[0m]8;;\ - Method Roster\Validation\Rules\ScheduleOverlapRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


[0;31mERROR[0m: UnusedForeachValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php#L56\src/Validation/Rules/[1;31mScheduleOverlapRule.php:56:45[0m]8;;\ - $schedule is never referenced or the value is not used (see https://psalm.dev/275)
                foreach ($allOverlapping as [97;41m$schedule[0m) {


INFO: RiskyTruthyFalsyComparison - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php#L61\src/Validation/Rules/[1;31mScheduleOverlapRule.php:61:17[0m]8;;\ - Operand of type mixed|null contains type mixed, which can be falsy and truthy. This can cause possibly unexpected behavior. Use strict comparison instead. (see https://psalm.dev/356)
            if ([30;47m$excludeId[0m) {


[0;31mERROR[0m: UnusedForeachValue - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/ScheduleOverlapRule.php#L64\src/Validation/Rules/[1;31mScheduleOverlapRule.php:64:59[0m]8;;\ - $schedule is never referenced or the value is not used (see https://psalm.dev/275)
                    foreach ($overlappingExcludingSelf as [97;41m$schedule[0m) {


[0;31mERROR[0m: UnusedClass - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/TimeSlotDateTimeRule.php#L19\src/Validation/Rules/[1;31mTimeSlotDateTimeRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\TimeSlotDateTimeRule is never used (see https://psalm.dev/075)
class [97;41mTimeSlotDateTimeRule[0m extends AbstractRule


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/TimeSlotDateTimeRule.php#L19\src/Validation/Rules/[1;31mTimeSlotDateTimeRule.php:19:7[0m]8;;\ - Class Roster\Validation\Rules\TimeSlotDateTimeRule is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mTimeSlotDateTimeRule[0m extends AbstractRule


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Rules/TimeSlotDateTimeRule.php#L21\src/Validation/Rules/[1;31mTimeSlotDateTimeRule.php:21:5[0m]8;;\ - Method Roster\Validation\Rules\TimeSlotDateTimeRule::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate(ValidationContextInterface $validationContext): void[0m


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/ValidationResult.php#L7\src/Validation/[1;31mValidationResult.php:7:7[0m]8;;\ - Class Roster\Validation\ValidationResult is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mValidationResult[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/ValidationResult.php#L35\src/Validation/[1;31mValidationResult.php:35:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\ValidationResult::hasViolations (see https://psalm.dev/087)
    public function [97;41mhasViolations[0m(): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/ValidationResult.php#L40\src/Validation/[1;31mValidationResult.php:40:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\ValidationResult::merge (see https://psalm.dev/087)
    public function [97;41mmerge[0m(ValidationResult $validationResult): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/ValidationResult.php#L48\src/Validation/[1;31mValidationResult.php:48:28[0m]8;;\ - Cannot find any calls to method Roster\Validation\ValidationResult::valid (see https://psalm.dev/087)
    public static function [97;41mvalid[0m(): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/ValidationResult.php#L53\src/Validation/[1;31mValidationResult.php:53:28[0m]8;;\ - Cannot find any calls to method Roster\Validation\ValidationResult::invalid (see https://psalm.dev/087)
    public static function [97;41minvalid[0m(array $violations): self


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/ValidationResult.php#L61\src/Validation/[1;31mValidationResult.php:61:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\ValidationResult::toArray (see https://psalm.dev/087)
    public function [97;41mtoArray[0m(): array


INFO: ClassMustBeFinal - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L16\src/Validation/[1;31mValidator.php:16:7[0m]8;;\ - Class Roster\Validation\Validator is never extended and is not part of the public API, and thus must be made final. (see https://psalm.dev/361)
class [30;47mValidator[0m implements ValidatorInterface


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L49\src/Validation/[1;31mValidator.php:49:5[0m]8;;\ - Method Roster\Validation\Validator::validate should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function validate([0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L85\src/Validation/[1;31mValidator.php:85:5[0m]8;;\ - Method Roster\Validation\Validator::registerRule should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function registerRule(RuleInterface $rule): void[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L133\src/Validation/[1;31mValidator.php:133:5[0m]8;;\ - Method Roster\Validation\Validator::getrulesfor should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function getRulesFor(OperationType $operationType, EntityType $entityType): array[0m


[0;31mERROR[0m: MissingOverrideAttribute - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L139\src/Validation/[1;31mValidator.php:139:5[0m]8;;\ - Method Roster\Validation\Validator::hasrulesfor should have the "Override" attribute (see https://psalm.dev/358)
    [97;41mpublic function hasRulesFor(OperationType $operationType, EntityType $entityType): bool[0m


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L154\src/Validation/[1;31mValidator.php:154:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Validator::getAllRules (see https://psalm.dev/087)
    public function [97;41mgetAllRules[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L162\src/Validation/[1;31mValidator.php:162:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Validator::hasRule (see https://psalm.dev/087)
    public function [97;41mhasRule[0m(string $ruleClass): bool


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L176\src/Validation/[1;31mValidator.php:176:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Validator::getRuleCount (see https://psalm.dev/087)
    public function [97;41mgetRuleCount[0m(): int


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L184\src/Validation/[1;31mValidator.php:184:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Validator::getRulesSortedByPriority (see https://psalm.dev/087)
    public function [97;41mgetRulesSortedByPriority[0m(): array


[0;31mERROR[0m: PossiblyUnusedMethod - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/Validation/Validator.php#L194\src/Validation/[1;31mValidator.php:194:21[0m]8;;\ - Cannot find any calls to method Roster\Validation\Validator::reset (see https://psalm.dev/087)
    public function [97;41mreset[0m(): void


INFO: PossiblyFalseOperand - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L97\src/[1;31mhelpers.php:97:27[0m]8;;\ - Left operand cannot be falsable, got false|int<0, 6> (see https://psalm.dev/162)
            if ($next !== [30;47m$current[0m + 1 && !($current === 6 && $next === 0)) {


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L120\src/[1;31mhelpers.php:120:16[0m]8;;\ - Function roster_format_days_for_display does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        return [97;41mroster_format_days_for_display($days)[0m;


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L189\src/[1;31mhelpers.php:189:25[0m]8;;\ - Function roster_days_in_period does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        $daysInPeriod = [97;41mroster_days_in_period($startDate, $endDate)[0m;


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L205\src/[1;31mhelpers.php:205:25[0m]8;;\ - Function roster_days_in_period does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
        $daysInPeriod = [97;41mroster_days_in_period($startDate, $endDate)[0m;


INFO: RedundantFunctionCall - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L214\src/[1;31mhelpers.php:214:16[0m]8;;\ - The call to array_values is unnecessary, list<string> is already a list (see https://psalm.dev/280)
        return [30;47marray_values[0m($validDays);


[0;31mERROR[0m: UndefinedFunction - ]8;;file:///home/andy-kani/pro/sites/packages/laravel-roster/src/helpers.php#L233\src/[1;31mhelpers.php:233:25[0m]8;;\ - Function roster_period_duration_in_days does not exist, consider enabling the allFunctionsGlobal config option if scanning legacy codebases (see https://psalm.dev/021)
            $duration = [97;41mroster_period_duration_in_days($startDate, $endDate)[0m;


------------------------------
[0;31m345 errors[0m found
------------------------------
311 other issues found.
------------------------------
Psalm can automatically fix 316 issues.
Run Psalm again with
[30;48;5;195m--alter --issues=MissingOverrideAttribute,UnusedVariable,InvalidNullableReturnType,PossiblyUnusedMethod,ClassMustBeFinal,MissingParamType --dry-run[0m
to see what it can fix.
------------------------------

Checks took 3.76 seconds and used 441.849MB of memory
Psalm was able to infer types for 89.8100% of the codebase
