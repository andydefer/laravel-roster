# PHPStan Static Analysis Report
*Generated: mer. 24 déc. 2025 05:30:12 WAT*

 ------ ----------------------------------------------------------------------------------------------------------------------
  Line   src/Commands/CacheRulesCommand.php
 ------ ----------------------------------------------------------------------------------------------------------------------
  90     Parameter #1 $value of function count expects array|Countable, mixed given.
         🪪  argument.type
  95     Argument of an invalid type mixed supplied for foreach, only iterables are supported.
         🪪  foreach.nonIterable
  98     Cannot access offset 'class' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  99     Cannot access offset 'priority' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  100    Cannot access offset 'entities' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  100    Parameter #2 $array of function implode expects array, mixed given.
         🪪  argument.type
  101    Cannot access offset 'operations' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  101    Parameter #2 $array of function implode expects array, mixed given.
         🪪  argument.type
  122    Parameter #1 $bytes of method Roster\Commands\CacheRulesCommand::formatBytes() expects int, int<0, max>|false given.
         🪪  argument.type
  123    Parameter #1 $value of function count expects array|Countable, mixed given.
         🪪  argument.type
 ------ ----------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Repository/AvailabilityRepositoryInterface.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  224    Method Roster\Contracts\Repository\AvailabilityRepositoryInterface::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not
         specify its types: TModel
         🪪  missingType.generics
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Repository/ScheduleRepositoryInterface.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  139    Method Roster\Contracts\Repository\ScheduleRepositoryInterface::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify
         its types: TModel
         🪪  missingType.generics
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------
  Line   src/Contracts/Services/AvailabilityValidatorInterface.php
 ------ ------------------------------------------------------------------------------------------------
  20     PHPDoc tag @throws with type Roster\Exceptions\ValidationException is not subtype of Throwable
         🪪  throws.notThrowable
 ------ ------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Services/SlotFinderInterface.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  42     Method Roster\Contracts\Services\SlotFinderInterface::getAvailableSlotsFromImpediments() has parameter $impediments with generic class Illuminate\Support\Collection but does
         not specify its types: TKey, TValue
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Validation/ValidationContextInterface.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------
  74     Method Roster\Contracts\Validation\ValidationContextInterface::rawData() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  99     Method Roster\Contracts\Validation\ValidationContextInterface::getViolations() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Validation/ValidatorInterface.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------
  13     Method Roster\Contracts\Validation\ValidatorInterface::validate() has parameter $additionalRules with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/DTOs/AvailabilityData.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------
  13     Method Roster\DTOs\AvailabilityData::__construct() has parameter $days with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  31     Parameter $id of class Roster\DTOs\AvailabilityData constructor expects int|null, mixed given.
         🪪  argument.type
  32     Parameter $type of class Roster\DTOs\AvailabilityData constructor expects string|null, mixed given.
         🪪  argument.type
  34     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  35     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  36     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  37     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  38     Parameter $schedulableId of class Roster\DTOs\AvailabilityData constructor expects int|null, mixed given.
         🪪  argument.type
  39     Parameter $schedulableType of class Roster\DTOs\AvailabilityData constructor expects string|null, mixed given.
         🪪  argument.type
  46     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  46     Parameter $id of class Roster\DTOs\AvailabilityData constructor expects int|null, mixed given.
         🪪  argument.type
  47     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  47     Parameter $type of class Roster\DTOs\AvailabilityData constructor expects string|null, mixed given.
         🪪  argument.type
  48     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  48     Parameter $days of class Roster\DTOs\AvailabilityData constructor expects array|null, mixed given.
         🪪  argument.type
  49     Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  49     Parameter $validityStart of class Roster\DTOs\AvailabilityData constructor expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  50     Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  50     Parameter $validityEnd of class Roster\DTOs\AvailabilityData constructor expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  51     Access to an undefined property Roster\Models\Availability::$daily_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  51     Parameter $dailyStart of class Roster\DTOs\AvailabilityData constructor expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  52     Access to an undefined property Roster\Models\Availability::$daily_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  52     Parameter $dailyEnd of class Roster\DTOs\AvailabilityData constructor expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  53     Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  53     Parameter $schedulableId of class Roster\DTOs\AvailabilityData constructor expects int|null, mixed given.
         🪪  argument.type
  54     Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  54     Parameter $schedulableType of class Roster\DTOs\AvailabilityData constructor expects string|null, mixed given.
         🪪  argument.type
  92     Method Roster\DTOs\AvailabilityData::withDaysInfo() has parameter $days with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  126    Method Roster\DTOs\AvailabilityData::withAutoFilteredDaysForUpdate() has parameter $existingDays with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  156    Parameter #1 $days of function roster_get_valid_days_in_period expects array<string>, array given.
         🪪  argument.type
  164    Method Roster\DTOs\AvailabilityData::getAutoAdjustedDays() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  188    Method Roster\DTOs\AvailabilityData::filterDaysByCurrentPeriod() has parameter $existingDays with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  188    Method Roster\DTOs\AvailabilityData::filterDaysByCurrentPeriod() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  197    Parameter #1 $days of function roster_get_valid_days_in_period expects array<string>, array given.
         🪪  argument.type
  222    Method Roster\DTOs\AvailabilityData::getDaysOrDefault() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/DTOs/ImpedimentData.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  12     Method Roster\DTOs\ImpedimentData::__construct() has parameter $metadata with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  36     Parameter $id of class Roster\DTOs\ImpedimentData constructor expects int|null, mixed given.
         🪪  argument.type
  37     Parameter $availabilityId of class Roster\DTOs\ImpedimentData constructor expects int|null, mixed given.
         🪪  argument.type
  38     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  39     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  40     Parameter $reason of class Roster\DTOs\ImpedimentData constructor expects string|null, mixed given.
         🪪  argument.type
  41     Parameter $metadata of class Roster\DTOs\ImpedimentData constructor expects array|null, mixed given.
         🪪  argument.type
  42     Parameter $schedulableId of class Roster\DTOs\ImpedimentData constructor expects int|null, mixed given.
         🪪  argument.type
  43     Parameter $schedulableType of class Roster\DTOs\ImpedimentData constructor expects string|null, mixed given.
         🪪  argument.type
  52     Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  53     Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/DTOs/ScheduleData.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  13     Method Roster\DTOs\ScheduleData::__construct() has parameter $metadata with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  52     Call to function is_string() with mixed will always evaluate to false.
         🪪  function.impossibleType
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  64     Parameter $id of class Roster\DTOs\ScheduleData constructor expects int|null, mixed given.
         🪪  argument.type
  65     Parameter $availabilityId of class Roster\DTOs\ScheduleData constructor expects int|null, mixed given.
         🪪  argument.type
  66     Parameter $title of class Roster\DTOs\ScheduleData constructor expects string|null, mixed given.
         🪪  argument.type
  67     Parameter $description of class Roster\DTOs\ScheduleData constructor expects string|null, mixed given.
         🪪  argument.type
  68     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  69     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  70     Parameter $metadata of class Roster\DTOs\ScheduleData constructor expects array|null, mixed given.
         🪪  argument.type
  72     Parameter $schedulableId of class Roster\DTOs\ScheduleData constructor expects int|null, mixed given.
         🪪  argument.type
  73     Parameter $schedulableType of class Roster\DTOs\ScheduleData constructor expects string|null, mixed given.
         🪪  argument.type
  84     Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  85     Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  88     Access to an undefined property Roster\Models\Schedule::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Parameter $schedulableId of class Roster\DTOs\ScheduleData constructor expects int|null, mixed given.
         🪪  argument.type
  89     Access to an undefined property Roster\Models\Schedule::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  89     Parameter $schedulableType of class Roster\DTOs\ScheduleData constructor expects string|null, mixed given.
         🪪  argument.type
  156    If condition is always true.
         🪪  if.alwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  160    Unreachable statement - code above always terminates.
         🪪  deadCode.unreachable
  172    Left side of && is always true.
         🪪  booleanAnd.leftAlwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------
  Line   src/Enums/EntityType.php
 ------ --------------------------------------------------------------------------------------------------------------
  24     Method Roster\Enums\EntityType::dateFields() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ --------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------
  Line   src/Exceptions/NotFoundException.php
 ------ ---------------------------------------------------------------------------------
  22     Property Roster\Exceptions\NotFoundException::$code has no type specified.
         🪪  missingType.property
  41     Parameter #2 $code of method Exception::__construct() expects int, mixed given.
         🪪  argument.type
 ------ ---------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/RosterException.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------
  27     Method Roster\Exceptions\RosterException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  52     Method Roster\Exceptions\RosterException::getContext() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -------------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Facades/Availability.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------
  39     Class Roster\Facades\Availability has PHPDoc tag @method for method create() parameter #1 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  39     Class Roster\Facades\Availability has PHPDoc tag @method for method findByType() parameter #1 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  39     Class Roster\Facades\Availability has PHPDoc tag @method for method findOverlapping() parameter #1 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  39     Class Roster\Facades\Availability has PHPDoc tag @method for method hasOverlapping() parameter #1 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  39     Class Roster\Facades\Availability has PHPDoc tag @method for method update() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Facades/Impediment.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------
  37     Class Roster\Facades\Impediment has PHPDoc tag @method for method create() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  37     Class Roster\Facades\Impediment has PHPDoc tag @method for method update() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Facades/Schedule.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------
  38     Class Roster\Facades\Schedule has PHPDoc tag @method for method create() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  38     Class Roster\Facades\Schedule has PHPDoc tag @method for method findFirstAvailablePeriod() return type with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  38     Class Roster\Facades\Schedule has PHPDoc tag @method for method findNextAvailableSlot() return type with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  38     Class Roster\Facades\Schedule has PHPDoc tag @method for method update() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Models/Availability.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  63     Method Roster\Models\Availability::schedulable() return type with generic class Illuminate\Database\Eloquent\Relations\MorphTo does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  71     Method Roster\Models\Availability::schedules() return type with generic class Illuminate\Database\Eloquent\Relations\HasMany does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  79     Method Roster\Models\Availability::impediments() return type with generic class Illuminate\Database\Eloquent\Relations\HasMany does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  117    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  117    Parameter #2 $haystack of function in_array expects array, mixed given.
         🪪  argument.type
  131    Access to an undefined property Roster\Models\Availability::$daily_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  131    Cannot call method format() on mixed.
         🪪  method.nonObject
  132    Access to an undefined property Roster\Models\Availability::$daily_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  132    Cannot call method format() on mixed.
         🪪  method.nonObject
  146    Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  146    Parameter #1 $date of method Carbon\Carbon::lt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  150    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  150    Parameter #1 $date of method Carbon\Carbon::gt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  165    Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  165    Parameter #1 $date of method Carbon\Carbon::lt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  169    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  169    Parameter #1 $date of method Carbon\Carbon::gt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  179    Access to an undefined property Roster\Models\Availability::$daily_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  179    Access to an undefined property Roster\Models\Availability::$daily_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  179    Cannot call method diffInMinutes() on mixed.
         🪪  method.nonObject
  179    Method Roster\Models\Availability::getDailyDurationMinutes() should return int but returns mixed.
         🪪  return.type
  189    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  189    Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  193    Cannot call method diffInDays() on mixed.
         🪪  method.nonObject
  193    Method Roster\Models\Availability::getValidityDurationDays() should return int|null but returns mixed.
         🪪  return.type
  203    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  216    Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  220    Parameter #1 $date of method Carbon\Carbon::gte() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  233    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  237    Parameter #1 $date of method Carbon\Carbon::gt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Models/Impediment.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  31     Class Roster\Models\Impediment has PHPDoc tag @property for property $metadata with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  47     Property Roster\Models\Impediment::$casts type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  72     Method Roster\Models\Impediment::availability() should return Illuminate\Database\Eloquent\Relations\BelongsTo<Roster\Models\Availability, Roster\Models\Impediment> but retu
         rns Illuminate\Database\Eloquent\Relations\BelongsTo<Roster\Models\Availability, $this(Roster\Models\Impediment)>.
         🪪  return.type
         💡  Template type TDeclaringModel on class Illuminate\Database\Eloquent\Relations\BelongsTo is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-cova
         riant
  80     Method Roster\Models\Impediment::schedulable() return type with generic class Illuminate\Database\Eloquent\Relations\MorphTo does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Models/Schedule.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  32     Class Roster\Models\Schedule has PHPDoc tag @property for property $metadata with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  50     Property Roster\Models\Schedule::$casts type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  64     Method Roster\Models\Schedule::availability() should return Illuminate\Database\Eloquent\Relations\BelongsTo<Roster\Models\Availability, Roster\Models\Schedule> but returns
         Illuminate\Database\Eloquent\Relations\BelongsTo<Roster\Models\Availability, $this(Roster\Models\Schedule)>.
         🪪  return.type
         💡  Template type TDeclaringModel on class Illuminate\Database\Eloquent\Relations\BelongsTo is not covariant. Learn more: https://phpstan.org/blog/whats-up-with-template-cova
         riant
  72     Method Roster\Models\Schedule::schedulable() return type with generic class Illuminate\Database\Eloquent\Relations\Relation does not specify its types: TRelatedModel,
         TDeclaringModel, TResult
         🪪  missingType.generics
  74     Using nullsafe method call on non-nullable type Roster\Models\Availability. Use -> instead.
         🪪  nullsafe.neverNull
  84     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  84     Method Roster\Models\Schedule::getTypeAttribute() should return string but returns mixed.
         🪪  return.type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Repositories/AvailabilityRepository.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  22     Class Roster\Repositories\AvailabilityRepository extends generic class Roster\Repositories\AbstractRepository but does not specify its types: TModel
         🪪  missingType.generics
  31     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  31     Method Roster\Repositories\AvailabilityRepository::create() should return Roster\Models\Availability but returns mixed.
         🪪  return.type
  54     Method Roster\Repositories\AvailabilityRepository::delete() should return bool but returns bool|null.
         🪪  return.type
  68     Call to an undefined static method Roster\Models\Availability::whereIn().
         🪪  staticMethod.notFound
  68     Cannot call method delete() on mixed.
         🪪  method.nonObject
  76     Call to an undefined static method Roster\Models\Availability::find().
         🪪  staticMethod.notFound
  76     Method Roster\Repositories\AvailabilityRepository::find() should return Roster\Models\Availability|null but returns mixed.
         🪪  return.type
  94     Method Roster\Repositories\AvailabilityRepository::findForSchedulable() should return Illuminate\Support\Collection<int, Roster\Models\Availability> but returns Illuminate\S
         upport\Collection<int, stdClass>.
         🪪  return.type
  126    Method Roster\Repositories\AvailabilityRepository::getForDateRange() should return Illuminate\Support\Collection<int, Roster\Models\Availability> but returns Illuminate\Data
         base\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>.
         🪪  return.type
  146    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  146    Call to an undefined static method Roster\Models\Availability::where().
         🪪  staticMethod.notFound
  146    Cannot call method first() on mixed.
         🪪  method.nonObject
  146    Cannot call method when() on mixed.
         🪪  method.nonObject
  146    Cannot call method where() on mixed.
         🪪  method.nonObject
  146    Cannot call method where() on mixed.
         🪪  method.nonObject
  146    Cannot call method where() on mixed.
         🪪  method.nonObject
  146    Cannot call method where() on mixed.
         🪪  method.nonObject
  146    Cannot call method where() on mixed.
         🪪  method.nonObject
  146    Cannot call method whereJsonContains() on mixed.
         🪪  method.nonObject
  146    Cannot call method withExists() on mixed.
         🪪  method.nonObject
  146    Method Roster\Repositories\AvailabilityRepository::findForTimeSlotWithConflictInfo() should return Roster\Models\Availability|null but returns mixed.
         🪪  return.type
  149    Cannot call method where() on mixed.
         🪪  method.nonObject
  155    Cannot call method orWhere() on mixed.
         🪪  method.nonObject
  155    Cannot call method whereNull() on mixed.
         🪪  method.nonObject
  159    Cannot call method orWhere() on mixed.
         🪪  method.nonObject
  159    Cannot call method whereNull() on mixed.
         🪪  method.nonObject
  164    Cannot call method where() on mixed.
         🪪  method.nonObject
  164    Cannot call method where() on mixed.
         🪪  method.nonObject
  168    Cannot call method where() on mixed.
         🪪  method.nonObject
  168    Cannot call method where() on mixed.
         🪪  method.nonObject
  226    Parameter #1 $builder of method Roster\Repositories\AvailabilityRepository::applyDateFilters() expects Illuminate\Database\Eloquent\Builder,
         Illuminate\Database\Query\Builder given.
         🪪  argument.type
  239    Method Roster\Repositories\AvailabilityRepository::getAll() should return Illuminate\Support\Collection<int, Illuminate\Database\Eloquent\Model> but returns Illuminate\Suppo
         rt\Collection<int, stdClass>.
         🪪  return.type
  268    Method Roster\Repositories\AvailabilityRepository::getAllForSchedulable() should return Illuminate\Support\Collection<int, Roster\Models\Availability> but returns Illuminate
         \Support\Collection<int, stdClass>.
         🪪  return.type
  288    Parameter #1 $builder of method Roster\Repositories\AvailabilityRepository::applyDateFilters() expects Illuminate\Database\Eloquent\Builder,
         Illuminate\Database\Query\Builder given.
         🪪  argument.type
  307    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  308    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  310    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  311    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  319    Parameter #2 $days of method Roster\Repositories\AvailabilityRepository::applyDayFilters() expects array<string>, mixed given.
         🪪  argument.type
  320    Parameter #2 $startTime of method Roster\Repositories\AvailabilityRepository::applyTimeOverlapFilters() expects Illuminate\Support\Carbon, Illuminate\Support\Carbon|null
         given.
         🪪  argument.type
  320    Parameter #3 $endTime of method Roster\Repositories\AvailabilityRepository::applyTimeOverlapFilters() expects Illuminate\Support\Carbon, Illuminate\Support\Carbon|null
         given.
         🪪  argument.type
  378    Method Roster\Repositories\AvailabilityRepository::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types:
         TModel
         🪪  missingType.generics
  385    Parameter #1 $string of function strtolower expects string, mixed given.
         🪪  argument.type
  391    Parameter #1 $string of function strtolower expects string, mixed given.
         🪪  argument.type
  410    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  410    Parameter #2 $haystack of function in_array expects array, mixed given.
         🪪  argument.type
  414    Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  414    Parameter #1 $date of method Carbon\Carbon::lt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  415    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  415    Parameter #1 $date of method Carbon\Carbon::gt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  437    Call to an undefined method Illuminate\Support\Collection<int, Roster\Models\Availability>::load().
         🪪  method.notFound
  437    Method Roster\Repositories\AvailabilityRepository::getAvailabilitiesWithConflictInfo() should return Illuminate\Support\Collection<int, Roster\Models\Availability> but retur
         ns mixed.
         🪪  return.type
  460    Method Roster\Repositories\AvailabilityRepository::buildBaseQuery() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  462    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  462    Call to an undefined static method Roster\Models\Availability::where().
         🪪  staticMethod.notFound
  462    Cannot call method where() on mixed.
         🪪  method.nonObject
  462    Method Roster\Repositories\AvailabilityRepository::buildBaseQuery() should return Illuminate\Database\Eloquent\Builder but returns mixed.
         🪪  return.type
  473    Method Roster\Repositories\AvailabilityRepository::applyTimeSlotFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify
         its types: TModel
         🪪  missingType.generics
  488    Method Roster\Repositories\AvailabilityRepository::applyDateFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  500    Method Roster\Repositories\AvailabilityRepository::applyDateRangeFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not
         specify its types: TModel
         🪪  missingType.generics
  517    Method Roster\Repositories\AvailabilityRepository::applyDayFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  537    Method Roster\Repositories\AvailabilityRepository::applyTimeOverlapFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not
         specify its types: TModel
         🪪  missingType.generics
  552    Method Roster\Repositories\AvailabilityRepository::applyDateOverlapFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not
         specify its types: TModel
         🪪  missingType.generics
  588    Method Roster\Repositories\AvailabilityRepository::eagerLoadRelations() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify
         its types: TModel
         🪪  missingType.generics
  593    Cannot call method getQuery() on mixed.
         🪪  method.nonObject
  594    Parameter #1 $builder of method Roster\Repositories\AvailabilityRepository::applyRelationDateFilter() expects Illuminate\Database\Eloquent\Builder, mixed given.
         🪪  argument.type
  595    Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  599    Cannot call method getQuery() on mixed.
         🪪  method.nonObject
  600    Parameter #1 $builder of method Roster\Repositories\AvailabilityRepository::applyRelationDateFilter() expects Illuminate\Database\Eloquent\Builder, mixed given.
         🪪  argument.type
  601    Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  618    Method Roster\Repositories\AvailabilityRepository::applyRelationDateFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not
         specify its types: TModel
         🪪  missingType.generics
  626    Cannot call method where() on mixed.
         🪪  method.nonObject
  626    Cannot call method where() on mixed.
         🪪  method.nonObject
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Repositories/ImpedimentRepository.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  17     Class Roster\Repositories\ImpedimentRepository extends generic class Roster\Repositories\AbstractRepository but does not specify its types: TModel
         🪪  missingType.generics
  24     Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  24     Method Roster\Repositories\ImpedimentRepository::create() should return Roster\Models\Impediment but returns mixed.
         🪪  return.type
  44     Method Roster\Repositories\ImpedimentRepository::delete() should return bool but returns bool|null.
         🪪  return.type
  54     Call to an undefined static method Roster\Models\Impediment::find().
         🪪  staticMethod.notFound
  54     Method Roster\Repositories\ImpedimentRepository::find() should return Roster\Models\Impediment|null but returns mixed.
         🪪  return.type
  62     Method Roster\Repositories\ImpedimentRepository::getAll() should return Illuminate\Support\Collection<int, Illuminate\Database\Eloquent\Model> but returns Illuminate\Support
         \Collection<int, stdClass>.
         🪪  return.type
  80     Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  80     Cannot call method get() on mixed.
         🪪  method.nonObject
  80     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  80     Cannot call method where() on mixed.
         🪪  method.nonObject
  80     Cannot call method where() on mixed.
         🪪  method.nonObject
  80     Method Roster\Repositories\ImpedimentRepository::findForTimeSlot() should return Illuminate\Support\Collection<int, Roster\Models\Impediment> but returns mixed.
         🪪  return.type
  102    Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  102    Cannot call method where() on mixed.
         🪪  method.nonObject
  102    Cannot call method where() on mixed.
         🪪  method.nonObject
  107    Cannot call method where() on mixed.
         🪪  method.nonObject
  112    Cannot call method exists() on mixed.
         🪪  method.nonObject
  112    Method Roster\Repositories\ImpedimentRepository::hasOverlappingImpediments() should return bool but returns mixed.
         🪪  return.type
  130    Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  130    Cannot call method where() on mixed.
         🪪  method.nonObject
  137    Cannot call method where() on mixed.
         🪪  method.nonObject
  140    Cannot call method get() on mixed.
         🪪  method.nonObject
  140    Method Roster\Repositories\ImpedimentRepository::findOverlappingImpediments() should return Illuminate\Support\Collection<int, Roster\Models\Impediment> but returns mixed.
         🪪  return.type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Repositories/ScheduleRepository.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  17     Class Roster\Repositories\ScheduleRepository extends generic class Roster\Repositories\AbstractRepository but does not specify its types: TModel
         🪪  missingType.generics
  24     Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  24     Method Roster\Repositories\ScheduleRepository::create() should return Roster\Models\Schedule but returns mixed.
         🪪  return.type
  44     Method Roster\Repositories\ScheduleRepository::delete() should return bool but returns bool|null.
         🪪  return.type
  56     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  56     Cannot call method where() on mixed.
         🪪  method.nonObject
  60     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  70     Method Roster\Repositories\ScheduleRepository::getAll() should return Illuminate\Support\Collection<int, Illuminate\Database\Eloquent\Model> but returns Illuminate\Support\C
         ollection<int, stdClass>.
         🪪  return.type
  88     Call to an undefined static method Roster\Models\Schedule::where().
         🪪  staticMethod.notFound
  88     Cannot call method get() on mixed.
         🪪  method.nonObject
  88     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  88     Cannot call method where() on mixed.
         🪪  method.nonObject
  88     Cannot call method where() on mixed.
         🪪  method.nonObject
  88     Method Roster\Repositories\ScheduleRepository::findForTimeSlot() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns mixed.
         🪪  return.type
  110    Call to an undefined static method Roster\Models\Schedule::where().
         🪪  staticMethod.notFound
  110    Cannot call method where() on mixed.
         🪪  method.nonObject
  110    Cannot call method where() on mixed.
         🪪  method.nonObject
  115    Cannot call method where() on mixed.
         🪪  method.nonObject
  119    Cannot call method toSql() on mixed.
         🪪  method.nonObject
  120    Cannot call method getBindings() on mixed.
         🪪  method.nonObject
  124    Cannot call method exists() on mixed.
         🪪  method.nonObject
  128    Method Roster\Repositories\ScheduleRepository::hasOverlappingSchedule() should return bool but returns mixed.
         🪪  return.type
  146    Call to an undefined static method Roster\Models\Schedule::where().
         🪪  staticMethod.notFound
  146    Cannot call method where() on mixed.
         🪪  method.nonObject
  153    Cannot call method where() on mixed.
         🪪  method.nonObject
  156    Cannot call method get() on mixed.
         🪪  method.nonObject
  156    Method Roster\Repositories\ScheduleRepository::findOverlappingSchedules() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns mixed.
         🪪  return.type
  177    Method Roster\Repositories\ScheduleRepository::getAllForSchedulable() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns Illuminate\Support
         \Collection<int, stdClass>.
         🪪  return.type
  203    Method Roster\Repositories\ScheduleRepository::getForDateRange() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns Illuminate\Support\Coll
         ection<int, stdClass>.
         🪪  return.type
  213    Method Roster\Repositories\ScheduleRepository::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  227    Method Roster\Repositories\ScheduleRepository::buildSchedulableQuery() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  229    Call to an undefined static method Roster\Models\Schedule::whereHas().
         🪪  staticMethod.notFound
  229    Method Roster\Repositories\ScheduleRepository::buildSchedulableQuery() should return Illuminate\Database\Eloquent\Builder but returns mixed.
         🪪  return.type
  230    Cannot call method where() on mixed.
         🪪  method.nonObject
  230    Cannot call method where() on mixed.
         🪪  method.nonObject
  241    Method Roster\Repositories\ScheduleRepository::applyCommonFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/RosterServiceProvider.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  96     Parameter #2 ...$arrays of function array_merge expects array, mixed given.
         🪪  argument.type
  99     Parameter #2 $useCacheFile of class Roster\Validation\RuleScanner constructor expects bool, mixed given.
         🪪  argument.type
  106    Parameter #2 ...$arrays of function array_merge expects array, mixed given.
         🪪  argument.type
  107    Parameter #2 $useCacheFile of class Roster\Validation\RuleScanner constructor expects bool, mixed given.
         🪪  argument.type
  116    Cannot call method make() on mixed.
         🪪  method.nonObject
  116    Parameter $validator of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Validation\ValidatorInterface, mixed given.
         🪪  argument.type
  117    Cannot call method make() on mixed.
         🪪  method.nonObject
  117    Parameter $availabilityRepository of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Repository\AvailabilityRepositoryInterface, mixed given.
         🪪  argument.type
  123    Cannot call method make() on mixed.
         🪪  method.nonObject
  123    Parameter $validator of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Validation\ValidatorInterface, mixed given.
         🪪  argument.type
  124    Cannot call method make() on mixed.
         🪪  method.nonObject
  124    Parameter $availabilityRepository of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Repository\AvailabilityRepositoryInterface, mixed given.
         🪪  argument.type
  125    Cannot call method make() on mixed.
         🪪  method.nonObject
  125    Parameter $impedimentRepository of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Repository\ImpedimentRepositoryInterface, mixed given.
         🪪  argument.type
  126    Cannot call method make() on mixed.
         🪪  method.nonObject
  126    Parameter $scheduleRepository of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Repository\ScheduleRepositoryInterface, mixed given.
         🪪  argument.type
  132    Cannot call method make() on mixed.
         🪪  method.nonObject
  132    Parameter $validator of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Validation\ValidatorInterface, mixed given.
         🪪  argument.type
  133    Cannot call method make() on mixed.
         🪪  method.nonObject
  133    Parameter $availabilityRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\AvailabilityRepositoryInterface, mixed given.
         🪪  argument.type
  134    Cannot call method make() on mixed.
         🪪  method.nonObject
  134    Parameter $impedimentRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\ImpedimentRepositoryInterface, mixed given.
         🪪  argument.type
  135    Cannot call method make() on mixed.
         🪪  method.nonObject
  135    Parameter $scheduleRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\ScheduleRepositoryInterface, mixed given.
         🪪  argument.type
  136    Cannot call method make() on mixed.
         🪪  method.nonObject
  136    Parameter $slotFinder of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Services\SlotFinderInterface, mixed given.
         🪪  argument.type
  149    Parameter $application of class Roster\Services\Core\ResourcePublisherService constructor expects Illuminate\Contracts\Foundation\Application, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/AvailabilityService.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  32     Method Roster\Services\AvailabilityService::createDTOFromArray() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  34     Parameter #1 $data of static method Roster\DTOs\AvailabilityData::fromArray() expects array<string, mixed>, array given.
         🪪  argument.type
  48     Method Roster\Services\AvailabilityService::create() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  50     Property Roster\Services\Core\AbstractService::$data (array<string, mixed>) does not accept array.
         🪪  assign.propertyType
  64     Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  64     Parameter #1 $schedulableId of method Roster\DTOs\AvailabilityData::withSchedulableInfo() expects int|null, mixed given.
         🪪  argument.type
  65     Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  65     Parameter #2 $schedulableType of method Roster\DTOs\AvailabilityData::withSchedulableInfo() expects string|null, class-string<Illuminate\Database\Eloquent\Model>|false given
         .
         🪪  argument.type
  81     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  81     Parameter #1 $entityId of method Roster\Services\AvailabilityService::clearEntityCache() expects int, mixed given.
         🪪  argument.type
  119    Parameter #1 $days of method Roster\DTOs\AvailabilityData::withDaysInfo() expects array|null, mixed given.
         🪪  argument.type
  121    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  121    Parameter #1 $existingDays of method Roster\DTOs\AvailabilityData::withAutoFilteredDaysForUpdate() expects array, mixed given.
         🪪  argument.type
  122    Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  122    Parameter #2 $existingValidityStart of method Roster\DTOs\AvailabilityData::withAutoFilteredDaysForUpdate() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  123    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  123    Parameter #3 $existingValidityEnd of method Roster\DTOs\AvailabilityData::withAutoFilteredDaysForUpdate() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  209    Method Roster\Services\AvailabilityService::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  211    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::buildQueryWithFilters() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  232    Parameter #2 $type of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForSchedulable() expects string|null, mixed given.
         🪪  argument.type
  262    Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  262    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  263    Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  263    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  269    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  274    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  274    Parameter #1 $array of function array_intersect expects array, mixed given.
         🪪  argument.type
  274    Parameter #2 ...$arrays of function array_intersect expects array, mixed given.
         🪪  argument.type
  281    Access to an undefined property Roster\Models\Availability::$daily_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  281    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  282    Access to an undefined property Roster\Models\Availability::$daily_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  282    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  283    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  284    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  321    Access to an undefined property Roster\Models\Availability::$daily_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  321    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  322    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  325    Access to an undefined property Roster\Models\Availability::$daily_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  325    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  326    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  333    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  333    Parameter #1 ...$arrays of function array_merge expects array, mixed given.
         🪪  argument.type
  333    Parameter #2 ...$arrays of function array_merge expects array, mixed given.
         🪪  argument.type
  333    Unable to resolve the template type T in call to function array_values
         🪪  argument.templateType
         💡  See: https://phpstan.org/blog/solving-phpstan-error-unable-to-resolve-template-type
  336    Access to an undefined property Roster\Models\Availability::$validity_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  336    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  337    Access to an undefined property Roster\Models\Availability::$validity_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  337    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  338    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  339    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  353    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  359    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  360    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/AbstractAvailabilityValidatingService.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  21     Class Roster\Services\Core\AbstractAvailabilityValidatingService extends generic class Roster\Services\Core\AbstractEntityScopingService but does not specify its types:
         TEntity
         🪪  missingType.generics
  37     Method Roster\Services\Core\AbstractAvailabilityValidatingService::validate() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  68     Method Roster\Services\Core\AbstractAvailabilityValidatingService::createDTOFromArray() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  82     Method Roster\Services\Core\AbstractAvailabilityValidatingService::between() return type with generic class Illuminate\Support\Collection does not specify its types: TKey,
         TValue
         🪪  missingType.generics
  134    Method Roster\Services\Core\AbstractAvailabilityValidatingService::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not
         specify its types: TModel
         🪪  missingType.generics
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/AbstractEntityScopingService.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  68     Method Roster\Services\Core\AbstractEntityScopingService::getAll() should return Illuminate\Support\Collection<int, TEntity> but returns Illuminate\Support\Collection<int, m
         ixed>.
         🪪  return.type
  139    Method Roster\Services\Core\AbstractEntityScopingService::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its
         types: TModel
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/AbstractService.php
 ------ --------------------------------------------------------------------------------------------------------------------------------
  120    Method Roster\Services\Core\AbstractService::update() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ --------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/AbstractValidatingService.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
  17     Class Roster\Services\Core\AbstractValidatingService extends generic class Roster\Services\Core\AbstractEntityScopingService but does not specify its types: TEntity
         🪪  missingType.generics
  32     PHPDoc tag @param references unknown parameter: $flags
         🪪  parameter.notFound
  64     Method Roster\Services\Core\AbstractValidatingService::createDTOFromArray() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/SlotFinderService.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  58     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  63     Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  63     Cannot call method format() on mixed.
         🪪  method.nonObject
  64     Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  64     Cannot call method format() on mixed.
         🪪  method.nonObject
  88     Method Roster\Services\Core\SlotFinderService::getAvailableSlotsFromImpediments() has parameter $impediments with generic class Illuminate\Support\Collection but does not
         specify its types: TKey, TValue
         🪪  missingType.generics
  140    Access to an undefined property Roster\Models\Availability::$schedules.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  140    Cannot call method contains() on mixed.
         🪪  method.nonObject
  141    Anonymous function should return bool but returns mixed.
         🪪  return.type
  141    Cannot call method overlapsWith() on mixed.
         🪪  method.nonObject
  144    Access to an undefined property Roster\Models\Availability::$impediments.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  144    Cannot call method contains() on mixed.
         🪪  method.nonObject
  145    Anonymous function should return bool but returns mixed.
         🪪  return.type
  145    Cannot call method overlapsWith() on mixed.
         🪪  method.nonObject
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/ImpedimentService.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  53     Method Roster\Services\ImpedimentService::createDTOFromArray() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  55     Parameter #1 $data of static method Roster\DTOs\ImpedimentData::fromArray() expects array<string, mixed>, array given.
         🪪  argument.type
  73     Method Roster\Services\ImpedimentService::create() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  75     Property Roster\Services\Core\AbstractService::$data (array<string, mixed>) does not accept array.
         🪪  assign.propertyType
  76     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  77     Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  78     Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  86     Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  86     Parameter #1 $schedulableId of method Roster\DTOs\ImpedimentData::withSchedulableInfo() expects int|null, mixed given.
         🪪  argument.type
  87     Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  87     Parameter #2 $schedulableType of method Roster\DTOs\ImpedimentData::withSchedulableInfo() expects string|null, class-string<Illuminate\Database\Eloquent\Model>|false given.
         🪪  argument.type
  236    Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  236    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  236    Cannot call method find() on mixed.
         🪪  method.nonObject
  236    Cannot call method where() on mixed.
         🪪  method.nonObject
  236    Method Roster\Services\ImpedimentService::find() should return Roster\Models\Impediment|null but returns mixed.
         🪪  return.type
  237    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  252    Method Roster\Services\ImpedimentService::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  254    Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  254    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  254    Cannot call method where() on mixed.
         🪪  method.nonObject
  255    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  257    Parameter #1 $builder of method Roster\Services\Core\AbstractService::applyDateFilters() expects Illuminate\Database\Eloquent\Builder, mixed given.
         🪪  argument.type
  258    Parameter #1 $builder of method Roster\Services\Core\AbstractService::applyTypeFilter() expects Illuminate\Database\Eloquent\Builder, mixed given.
         🪪  argument.type
  259    Parameter #1 $builder of method Roster\Services\Core\AbstractService::applyReasonFilter() expects Illuminate\Database\Eloquent\Builder, mixed given.
         🪪  argument.type
  261    Method Roster\Services\ImpedimentService::buildQueryWithFilters() should return Illuminate\Database\Eloquent\Builder but returns mixed.
         🪪  return.type
  293    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  299    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  299    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  305    Method Roster\Services\ImpedimentService::getAvailableTimeSlots() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  307    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  313    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  313    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::findForTimeSlot() expects int, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/ScheduleService.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  49     Method Roster\Services\ScheduleService::createDTOFromArray() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  51     Parameter #1 $data of static method Roster\DTOs\ScheduleData::fromArray() expects array<string, mixed>, array given.
         🪪  argument.type
  69     Method Roster\Services\ScheduleService::create() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  71     Property Roster\Services\Core\AbstractService::$data (array<string, mixed>) does not accept array.
         🪪  assign.propertyType
  72     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  73     Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  74     Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  82     Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  82     Parameter #1 $schedulableId of method Roster\DTOs\ScheduleData::withSchedulableInfo() expects int|null, mixed given.
         🪪  argument.type
  83     Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  83     Parameter #2 $schedulableType of method Roster\DTOs\ScheduleData::withSchedulableInfo() expects string|null, class-string<Illuminate\Database\Eloquent\Model>|false given.
         🪪  argument.type
  174    Access to an undefined property Roster\Models\Schedule::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  175    Access to an undefined property Roster\Models\Schedule::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  230    Call to an undefined static method Roster\Models\Schedule::where().
         🪪  staticMethod.notFound
  230    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  230    Cannot call method find() on mixed.
         🪪  method.nonObject
  230    Cannot call method where() on mixed.
         🪪  method.nonObject
  230    Method Roster\Services\ScheduleService::find() should return Roster\Models\Schedule|null but returns mixed.
         🪪  return.type
  231    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  246    Method Roster\Services\ScheduleService::buildQueryWithFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  249    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  249    Parameter #1 $schedulableId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::buildQueryWithFilters() expects int, mixed given.
         🪪  argument.type
  250    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  250    Parameter #2 $schedulableType of method Roster\Contracts\Repository\ScheduleRepositoryInterface::buildQueryWithFilters() expects string, class-string<Illuminate\Database\Elo
         quent\Model>|false given.
         🪪  argument.type
  282    Method Roster\Services\ScheduleService::findNextSlot() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  290    Parameter #1 $value of method Carbon\Carbon::addDays() expects float|int, mixed given.
         🪪  argument.type
  305    Method Roster\Services\ScheduleService::findNextSlot() should return array|Illuminate\Support\Carbon|null but returns mixed.
         🪪  return.type
  321    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlotWithConflictInfo() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  328    Access to an undefined property Roster\Models\Availability::$has_overlapping_schedules.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  329    Access to an undefined property Roster\Models\Availability::$has_overlapping_impediments.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  335    Method Roster\Services\ScheduleService::findAvailableSlotInDay() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  343    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::getForDate() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  372    Method Roster\Services\ScheduleService::findSlotInAvailability() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  379    Access to an undefined property Roster\Models\Availability::$daily_end.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  379    Access to an undefined property Roster\Models\Availability::$daily_start.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  389    Cannot call method format() on mixed.
         🪪  method.nonObject
  389    Parameter #1 $time of method Carbon\Carbon::setTimeFromTimeString() expects string, mixed given.
         🪪  argument.type
  390    Cannot call method format() on mixed.
         🪪  method.nonObject
  390    Parameter #1 $time of method Carbon\Carbon::setTimeFromTimeString() expects string, mixed given.
         🪪  argument.type
  415    Binary operation "*" between float and mixed results in an error.
         🪪  binaryOp.invalid
  415    Binary operation "/" between int and mixed results in an error.
         🪪  binaryOp.invalid
  429    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  429    Parameter #3 $type of method Roster\Services\ScheduleService::isTimeSlotAvailable() expects string|null, mixed given.
         🪪  argument.type
  439    Parameter #1 $value of method Carbon\Carbon::addMinutes() expects float|int, mixed given.
         🪪  argument.type
  448    Method Roster\Services\ScheduleService::findAvailableSlots() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  480    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  497    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  497    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::hasOverlappingSchedule() expects int, mixed given.
         🪪  argument.type
  503    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  503    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  514    Method Roster\Services\ScheduleService::getAvailableSlotsFromImpediments() has parameter $impediments with generic class Illuminate\Support\Collection but does not specify
         its types: TKey, TValue
         🪪  missingType.generics
  514    Method Roster\Services\ScheduleService::getAvailableSlotsFromImpediments() return type with generic class Illuminate\Support\Collection does not specify its types: TKey,
         TValue
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/BelongsToSchedulable.php (in context of class Roster\Models\Availability)
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  39     Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  39     Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  57     Method Roster\Models\Availability::scopeForSchedulable() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  57     Method Roster\Models\Availability::scopeForSchedulable() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/BelongsToSchedulable.php (in context of class Roster\Models\Impediment)
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  57     Method Roster\Models\Impediment::scopeForSchedulable() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types: TModel
         🪪  missingType.generics
  57     Method Roster\Models\Impediment::scopeForSchedulable() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/BelongsToSchedulable.php (in context of class Roster\Models\Schedule)
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  39     Access to an undefined property Roster\Models\Schedule::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  39     Access to an undefined property Roster\Models\Schedule::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  57     Method Roster\Models\Schedule::scopeForSchedulable() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types: TModel
         🪪  missingType.generics
  57     Method Roster\Models\Schedule::scopeForSchedulable() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------
  Line   src/Traits/DateRangeOverlapTrait.php (in context of class Roster\Repositories\AvailabilityRepository)
 ------ -------------------------------------------------------------------------------------------------------
  47     Cannot call method lte() on Illuminate\Support\Carbon|null.
         🪪  method.nonObject
  48     Cannot call method gte() on Illuminate\Support\Carbon|null.
         🪪  method.nonObject
 ------ -------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/FilterableTrait.php (in context of class Roster\Services\Core\AbstractService)
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  38     Method Roster\Services\Core\AbstractService::applyDateFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  38     Method Roster\Services\Core\AbstractService::applyDateFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  64     Method Roster\Services\Core\AbstractService::applyTypeFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  64     Method Roster\Services\Core\AbstractService::applyTypeFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  88     Method Roster\Services\Core\AbstractService::applyDayFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  88     Method Roster\Services\Core\AbstractService::applyDayFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  104    Method Roster\Services\Core\AbstractService::applyStatusFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  104    Method Roster\Services\Core\AbstractService::applyStatusFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  120    Method Roster\Services\Core\AbstractService::applyReasonFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  120    Method Roster\Services\Core\AbstractService::applyReasonFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  123    Binary operation "." between '%' and mixed results in an error.
         🪪  binaryOp.invalid
  136    Method Roster\Services\Core\AbstractService::applyAvailabilityIdFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify
         its types: TModel
         🪪  missingType.generics
  136    Method Roster\Services\Core\AbstractService::applyAvailabilityIdFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types:
         TModel
         🪪  missingType.generics
  153    Method Roster\Services\Core\AbstractService::applySchedulableFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  153    Method Roster\Services\Core\AbstractService::applySchedulableFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  156    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/HasRoster.php (in context of class Tests\Support\TestSchedulable)
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  24     Method Tests\Support\TestSchedulable::schedules() return type with generic class Illuminate\Database\Eloquent\Relations\MorphMany does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  34     Method Tests\Support\TestSchedulable::availabilities() return type with generic class Illuminate\Database\Eloquent\Relations\MorphMany does not specify its types:
         TRelatedModel, TDeclaringModel
         🪪  missingType.generics
  44     Method Tests\Support\TestSchedulable::impediments() return type with generic class Illuminate\Database\Eloquent\Relations\MorphMany does not specify its types:
         TRelatedModel, TDeclaringModel
         🪪  missingType.generics
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Cache/RuleCacheGenerator.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------
  56     Binary operation "*" between mixed and 3600 results in an error.
         🪪  binaryOp.invalid
  68     Method Roster\Validation\Cache\RuleCacheGenerator::buildCacheFile() has parameter $rules with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  89     Parameter #2 $attribute of method Roster\Validation\Cache\RuleCacheGenerator::buildRuleEntry() expects Roster\Validation\Attributes\ValidationRule, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Context/ValidationContext.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------
  39     Method Roster\Validation\Context\ValidationContext::__construct() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  48     Property Roster\Validation\Context\ValidationContext::$data (array<string, mixed>) does not accept array.
         🪪  assign.propertyType
 ------ --------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Exceptions/ValidationFailedException.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------
  23     Method Roster\Validation\Exceptions\ValidationFailedException::__construct() has parameter $violations with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  31     Property Roster\Validation\Exceptions\ValidationFailedException::$violations (array<string, mixed>) does not accept array.
         🪪  assign.propertyType
  68     Method Roster\Validation\Exceptions\ValidationFailedException::getFirstViolation() should return string|null but returns mixed.
         🪪  return.type
  71     Method Roster\Validation\Exceptions\ValidationFailedException::getFirstViolation() should return string|null but returns mixed.
         🪪  return.type
  87     Method Roster\Validation\Exceptions\ValidationFailedException::fromViolations() has parameter $violations with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  98     Method Roster\Validation\Exceptions\ValidationFailedException::buildMessage() has parameter $violations with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  118    Parameter #3 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  121    Parameter #3 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/RuleScanner.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------
  22     Property Roster\Validation\RuleScanner::$withCache is unused.
         🪪  property.unused
         💡  See: https://phpstan.org/developing-extensions/always-read-written-properties
  24     Property Roster\Validation\RuleScanner::$cachedRules type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  29     Method Roster\Validation\RuleScanner::__construct() has parameter $ruleDirectories with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  35     Property Roster\Validation\RuleScanner::$cacheFile (string|null) does not accept mixed.
         🪪  assign.propertyType
  38     Method Roster\Validation\RuleScanner::scan() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  62     Method Roster\Validation\RuleScanner::loadFromCache() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  76     Cannot access offset 'priority' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  76     Parameter $priority of class Roster\Validation\Attributes\ValidationRule constructor expects int|null, mixed given.
         🪪  argument.type
  78     Parameter #1 $value of static method Roster\Enums\EntityType::from() expects int|string, mixed given.
         🪪  argument.type
  79     Cannot access offset 'entities' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  79     Parameter #2 $array of function array_map expects array, mixed given.
         🪪  argument.type
  82     Parameter #1 $value of static method Roster\Enums\OperationType::from() expects int|string, mixed given.
         🪪  argument.type
  83     Cannot access offset 'operations' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  83     Parameter #2 $array of function array_map expects array, mixed given.
         🪪  argument.type
  100    Method Roster\Validation\RuleScanner::regenerateCache() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  110    Method Roster\Validation\RuleScanner::doScan() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  121    Parameter #1 $filename of function is_dir expects string, mixed given.
         🪪  argument.type
  126    Parameter #1 $dirs of method Symfony\Component\Finder\Finder::in() expects array<string>|string, mixed given.
         🪪  argument.type
  137    Cannot call method getValidationRuleAttribute() on mixed.
         🪪  method.nonObject
  152    Cannot access property $priority on mixed.
         🪪  property.nonObject
  152    Cannot access property $priority on mixed.
         🪪  property.nonObject
  158    Method Roster\Validation\RuleScanner::instantiateRules() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  175    Parameter #2 $subject of function preg_match expects string, string|false given.
         🪪  argument.type
  177    Parameter #2 $subject of function preg_match expects string, string|false given.
         🪪  argument.type
 ------ -----------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/AbstractRule.php
 ------ ----------------------------------------------------------------------------------------------------------------
  71     Method Roster\Validation\Rules\AbstractRule::getMinimumDuration() should return int but returns mixed.
         🪪  return.type
  80     Method Roster\Validation\Rules\AbstractRule::getMaxDays() should return int but returns mixed.
         🪪  return.type
  85     Method Roster\Validation\Rules\AbstractRule::shouldValidateFutureDates() should return bool but returns mixed.
         🪪  return.type
  90     Method Roster\Validation\Rules\AbstractRule::allowPastDates() should return bool but returns mixed.
         🪪  return.type
  95     Method Roster\Validation\Rules\AbstractRule::getDefaultTimezone() should return string but returns mixed.
         🪪  return.type
 ------ ----------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/AvailabilityDateRangeRule.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  26     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityDateRangeRule::validateValidityDates() expects object|null, mixed given.
         🪪  argument.type
  27     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityDateRangeRule::validateDailyTimes() expects object|null, mixed given.
         🪪  argument.type
  56     Using nullsafe property access "?->validity_start" on left side of ?? is unnecessary. Use -> instead.
         🪪  nullsafe.neverNull
  60     Using nullsafe property access "?->validity_end" on left side of ?? is unnecessary. Use -> instead.
         🪪  nullsafe.neverNull
  92     Using nullsafe property access "?->daily_start" on left side of ?? is unnecessary. Use -> instead.
         🪪  nullsafe.neverNull
  96     Using nullsafe property access "?->daily_end" on left side of ?? is unnecessary. Use -> instead.
         🪪  nullsafe.neverNull
  118    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  119    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  163    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  164    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  193    Method Roster\Validation\Rules\AvailabilityDateRangeRule::getMaxDays() should return int but returns mixed.
         🪪  return.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/AvailabilityDaysCoherenceRule.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  58     Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  68     Parameter #3 $entity of method Roster\Validation\Rules\AvailabilityDaysCoherenceRule::getValidityDate() expects object|null, mixed given.
         🪪  argument.type
  69     Parameter #3 $entity of method Roster\Validation\Rules\AvailabilityDaysCoherenceRule::getValidityDate() expects object|null, mixed given.
         🪪  argument.type
  78     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  79     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  91     Parameter #1 $startDate of function roster_days_in_period expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  91     Parameter #2 $endDate of function roster_days_in_period expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  100    Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/AvailabilityOverlapRule.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------
  38     Right side of && is always true.
         🪪  booleanAnd.rightAlwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  54     Cannot access property $id on mixed.
         🪪  property.nonObject
  59     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityOverlapRule::getFieldValue() expects object|null, mixed given.
         🪪  argument.type
  60     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityOverlapRule::getFieldValue() expects object|null, mixed given.
         🪪  argument.type
  61     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityOverlapRule::getFieldValue() expects object|null, mixed given.
         🪪  argument.type
  62     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityOverlapRule::getFieldValue() expects object|null, mixed given.
         🪪  argument.type
  63     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityOverlapRule::getFieldValue() expects object|null, mixed given.
         🪪  argument.type
  64     Parameter #2 $entity of method Roster\Validation\Rules\AvailabilityOverlapRule::getFieldValue() expects object|null, mixed given.
         🪪  argument.type
  76     Parameter #3 $exceptId of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findOverlapping() expects int|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/AvailabilityOwnershipRule.php
 ------ --------------------------------------------------------------------------------------------------------------------------
  38     Cannot access property $availability_id on mixed.
         🪪  property.nonObject
  52     Parameter #1 $id of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::find() expects int, mixed given.
         🪪  argument.type
  63     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  63     Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  64     Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ --------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/AvailabilityTimeRangeRule.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  30     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  31     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  39     Variable $availability in PHPDoc tag @var does not match assigned variable $availabilityRepository.
         🪪  varTag.differentVariable
  40     Parameter #1 $id of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::find() expects int, mixed given.
         🪪  argument.type
  52     Method Roster\Validation\Rules\AvailabilityTimeRangeRule::validateTimeRange() has parameter $availability with no type specified.
         🪪  missingType.parameter
  60     Cannot access property $days on mixed.
         🪪  property.nonObject
  60     Parameter #2 $haystack of function in_array expects array, mixed given.
         🪪  argument.type
  68     Cannot access property $daily_start on mixed.
         🪪  property.nonObject
  68     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  69     Cannot access property $daily_end on mixed.
         🪪  property.nonObject
  69     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  86     Cannot access property $validity_start on mixed.
         🪪  property.nonObject
  86     Cannot access property $validity_start on mixed.
         🪪  property.nonObject
  86     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  93     Cannot access property $validity_end on mixed.
         🪪  property.nonObject
  93     Cannot access property $validity_end on mixed.
         🪪  property.nonObject
  93     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/AvailabilityTypeRule.php
 ------ ----------------------------------------------------------------------------------------------
  43     Parameter #2 $haystack of function in_array expects array, mixed given.
         🪪  argument.type
  46     Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ----------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/DaysValidationRule.php
 ------ ----------------------------------------------------------------------------------------------
  78     Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ----------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/DurationRule.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  49     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  50     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  89     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  90     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/FutureDateRule.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  47     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  67     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/RequiredFieldsRule.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------
  45     Parameter #1 $key of function array_key_exists expects int|string, mixed given.
         🪪  argument.type
  47     Parameter #1 $field of method Roster\Contracts\Validation\ValidationContextInterface::setViolation() expects string, mixed given.
         🪪  argument.type
  48     Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  54     Method Roster\Validation\Rules\RequiredFieldsRule::getRequiredFields() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------
  Line   src/Validation/Rules/SchedulableConsistencyRule.php
 ------ -----------------------------------------------------
  48     Cannot call method find() on mixed.
         🪪  method.nonObject
  56     Cannot access property $schedulable_id on mixed.
         🪪  property.nonObject
  57     Cannot access property $schedulable_type on mixed.
         🪪  property.nonObject
 ------ -----------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/SchedulableValidationRule.php
 ------ ----------------------------------------------------------------------------------------------
  80     Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  81     Parameter #3 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  92     Parameter #3 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  111    Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  112    Parameter #3 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  123    Parameter #3 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ----------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/ScheduleOverlapRule.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------
  31     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  32     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  46     Cannot access property $id on mixed.
         🪪  property.nonObject
  54     Parameter #1 $availabilityId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::findOverlappingSchedules() expects int, mixed given.
         🪪  argument.type
  62     Parameter #1 $availabilityId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::findOverlappingSchedules() expects int, mixed given.
         🪪  argument.type
  62     Parameter #4 $excludeId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::findOverlappingSchedules() expects int|null, mixed given.
         🪪  argument.type
  69     Parameter #1 $availabilityId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::hasOverlappingSchedule() expects int, mixed given.
         🪪  argument.type
  69     Parameter #4 $excludeId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::hasOverlappingSchedule() expects int|null, mixed given.
         🪪  argument.type
  83     Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Rules/TimeSlotDateTimeRule.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  29     Parameter #2 $entity of method Roster\Validation\Rules\TimeSlotDateTimeRule::validateUpdate() expects object|null, mixed given.
         🪪  argument.type
  60     Using nullsafe property access "?->start_datetime" on left side of ?? is unnecessary. Use -> instead.
         🪪  nullsafe.neverNull
  64     Using nullsafe property access "?->end_datetime" on left side of ?? is unnecessary. Use -> instead.
         🪪  nullsafe.neverNull
  81     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  82     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/ValidationResult.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------
  16     Method Roster\Validation\ValidationResult::__construct() has parameter $violations with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  53     Method Roster\Validation\ValidationResult::invalid() has parameter $violations with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -----------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Validation/Validator.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  45     Parameter #1 $rule of method Roster\Validation\Validator::registerRule() expects Roster\Contracts\Validation\RuleInterface, mixed given.
         🪪  argument.type
  49     Method Roster\Validation\Validator::validate() has parameter $additionalRules with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  66     Parameter #2 $callback of function usort expects callable(mixed, mixed): int, Closure(Roster\Contracts\Validation\RuleInterface, Roster\Contracts\Validation\RuleInterface):
         int<-1, 1> given.
         🪪  argument.type
         💡  Type Roster\Contracts\Validation\RuleInterface of parameter #1 $a of passed callable needs to be same or wider than parameter type mixed of accepting callable.
         💡  Type Roster\Contracts\Validation\RuleInterface of parameter #2 $b of passed callable needs to be same or wider than parameter type mixed of accepting callable.
  72     Cannot call method validate() on mixed.
         🪪  method.nonObject
  75     Cannot call method getName() on mixed.
         🪪  method.nonObject
  75     Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  97     Instanceof between Roster\Enums\EntityType and Roster\Enums\EntityType will always evaluate to true.
         🪪  instanceof.alwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  102    Instanceof between Roster\Enums\OperationType and Roster\Enums\OperationType will always evaluate to true.
         🪪  instanceof.alwaysTrue
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  184    Method Roster\Validation\Validator::getRulesSortedByPriority() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------
  Line   src/helpers.php
 ------ -------------------------------------------------------------------------------------------
  214    Parameter #1 $array (list<string>) of array_values is already a list, call has no effect.
         🪪  arrayValues.list
 ------ -------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Api/AvailabilityControllerTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Api/ImpedimentControllerTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Api/ScheduleControllerTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Commands/InstallRosterCommandTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Facades/AvailabilityFacadeTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Facades/ImpedimentFacadeTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Facades/ScheduleFacadeTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Integration/ServiceIntegrationTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Feature/Services/AvailabilityServiceDaysCoherenceTest.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------
  28     Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  28     Property Tests\Feature\Services\AvailabilityServiceDaysCoherenceTest::$testSchedulable (Tests\Support\TestSchedulable) does not accept mixed.
         🪪  assign.propertyType
  49     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  68     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  107    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  126    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  132    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  133    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  150    Cannot access property $id on mixed.
         🪪  property.nonObject
  150    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  154    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  155    Cannot access property $days on mixed.
         🪪  property.nonObject
  155    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertNotContains() expects iterable, mixed given.
         🪪  argument.type
  156    Cannot access property $days on mixed.
         🪪  property.nonObject
  156    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertContains() expects iterable, mixed given.
         🪪  argument.type
  157    Cannot access property $days on mixed.
         🪪  property.nonObject
  157    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertContains() expects iterable, mixed given.
         🪪  argument.type
  158    Cannot access property $days on mixed.
         🪪  property.nonObject
  158    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertContains() expects iterable, mixed given.
         🪪  argument.type
  164    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  165    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  181    Cannot access property $id on mixed.
         🪪  property.nonObject
  181    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  185    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  186    Cannot access property $days on mixed.
         🪪  property.nonObject
  192    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  193    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  213    Cannot access property $id on mixed.
         🪪  property.nonObject
  213    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  232    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  251    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  257    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  258    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  273    Cannot access property $id on mixed.
         🪪  property.nonObject
  273    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  277    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  278    Cannot access property $days on mixed.
         🪪  property.nonObject
  301    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  302    Parameter #1 $array of function sort expects TArray of array<T>, mixed given.
         🪪  argument.type
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Feature/Validation/ValidationFlowTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Integration/Database/AvailabilityIntegrationTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Integration/Database/ImpedimentIntegrationTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Integration/Database/ScheduleIntegrationTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Integration/ServiceProvider/DependencyInjectionTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Integration/ServiceProvider/ServiceRegistrationTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Integration/Traits/BelongsToSchedulableTest.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------
  41     Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Integration/Traits/BelongsToSchedulableTest.php:34::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  55     Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  56     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  74     Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  75     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  76     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  94     Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  95     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  96     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  112    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  113    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  114    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  123    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  123    Access to an undefined property Roster\Models\Schedule::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  124    Access to an undefined property Roster\Models\Schedule::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  137    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  138    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  152    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  153    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  154    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  162    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  173    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  174    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  175    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  183    Cannot call method update() on mixed.
         🪪  method.nonObject
  184    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  186    Cannot access property $title on mixed.
         🪪  property.nonObject
  199    Call to an undefined static method Roster\Models\Availability::forSchedulable().
         🪪  staticMethod.notFound
  199    Cannot call method get() on mixed.
         🪪  method.nonObject
  200    Call to an undefined static method Roster\Models\Availability::forSchedulable().
         🪪  staticMethod.notFound
  200    Cannot call method get() on mixed.
         🪪  method.nonObject
  202    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertCount() expects Countable|iterable, mixed given.
         🪪  argument.type
  203    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  203    Cannot access property $id on mixed.
         🪪  property.nonObject
  203    Cannot call method first() on mixed.
         🪪  method.nonObject
  205    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertCount() expects Countable|iterable, mixed given.
         🪪  argument.type
  206    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  206    Cannot access property $id on mixed.
         🪪  property.nonObject
  206    Cannot call method first() on mixed.
         🪪  method.nonObject
  214    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  214    Method Tests\Integration\Traits\BelongsToSchedulableTest::createTestAvailability() should return Roster\Models\Availability but returns mixed.
         🪪  return.type
  215    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  236    Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Integration/Traits/BelongsToSchedulableTest.php:229::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  247    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  247    Method Tests\Integration\Traits\BelongsToSchedulableTest::createAvailabilityForSchedulable() should return Roster\Models\Availability but returns mixed.
         🪪  return.type
  248    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Integration/Validation/RuleIntegrationTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ ------------------------------------
  Line   tests/TestCase.php
 ------ ------------------------------------
  42     Cannot call method set() on mixed.
         🪪  method.nonObject
  44     Cannot call method set() on mixed.
         🪪  method.nonObject
 ------ ------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Commands/InstallRosterCommandTest.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  72     Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  78     PHPDoc tag @var with type Symfony\Component\Console\Output\Output&Tests\Unit\Commands\OutputWithBuffer is not subtype of native type
         Symfony\Component\Console\Output\Output@anonymous/tests/Unit/Commands/InstallRosterCommandTest.php:78.
         🪪  varTag.nativeType
  103    Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  109    PHPDoc tag @var with type Symfony\Component\Console\Output\Output&Tests\Unit\Commands\OutputWithBuffer is not subtype of native type
         Symfony\Component\Console\Output\Output@anonymous/tests/Unit/Commands/InstallRosterCommandTest.php:109.
         🪪  varTag.nativeType
  127    Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  162    Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  165    PHPDoc tag @var with type Symfony\Component\Console\Output\Output&Tests\Unit\Commands\OutputWithBuffer is not subtype of native type
         Symfony\Component\Console\Output\Output@anonymous/tests/Unit/Commands/InstallRosterCommandTest.php:165.
         🪪  varTag.nativeType
  185    Cannot call method expectsOutput() on Illuminate\Testing\PendingCommand|int.
         🪪  method.nonObject
  200    Cannot call method id() on mixed.
         🪪  method.nonObject
  203    Cannot call method expectsOutput() on Illuminate\Testing\PendingCommand|int.
         🪪  method.nonObject
  214    Cannot call method id() on mixed.
         🪪  method.nonObject
  217    Cannot call method expectsOutput() on Illuminate\Testing\PendingCommand|int.
         🪪  method.nonObject
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Contracts/Validation/RuleInterfaceTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Contracts/Validation/ValidatorInterfaceTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/DTOs/AvailabilityDataTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/DTOs/ImpedimentDataTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/DTOs/ScheduleDataTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Enums/DaysOfWeekTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Enums/EntityTypeTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Enums/OperationTypeTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Exceptions/MissingSchedulableExceptionTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Exceptions/ValidationFailedExceptionTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Models/AvailabilityTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Models/ImpedimentTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Models/ScheduleTest.php
 ------ -----------------------------------------------------------------------------------------------
  32     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Models/Traits/BelongsToSchedulableTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Models/Traits/HasRosterTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Repositories/AvailabilityRepositoryTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Repositories/ImpedimentRepositoryTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Repositories/ScheduleRepositoryTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/AvailabilityServiceTest.php
 ------ -------------------------------------------------------------------------------------------------------------------------------
  27     Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  27     Property Tests\Unit\Services\AvailabilityServiceTest::$testSchedulable (Tests\Support\TestSchedulable) does not accept mixed.
         🪪  assign.propertyType
  49     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  51     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  55     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  56     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  57     Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  57     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  58     Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  77     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  78     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  84     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  85     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  109    Cannot access property $id on mixed.
         🪪  property.nonObject
  113    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  123    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  124    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  141    Cannot access property $id on mixed.
         🪪  property.nonObject
  141    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  146    Cannot access property $id on mixed.
         🪪  property.nonObject
  173    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  174    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  185    Cannot access property $id on mixed.
         🪪  property.nonObject
  185    Parameter #1 $id of method Roster\Services\AvailabilityService::delete() expects int, mixed given.
         🪪  argument.type
  190    Cannot access property $id on mixed.
         🪪  property.nonObject
  213    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  214    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  225    Cannot access property $id on mixed.
         🪪  property.nonObject
  225    Parameter #1 $id of method Roster\Services\AvailabilityService::find() expects int, mixed given.
         🪪  argument.type
  229    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  229    Cannot access property $id on mixed.
         🪪  property.nonObject
  230    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  248    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  249    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  259    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  260    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  276    Cannot access property $type on mixed.
         🪪  property.nonObject
  301    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  302    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  321    Cannot access property $id on mixed.
         🪪  property.nonObject
  321    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  327    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  328    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  345    Cannot access property $id on mixed.
         🪪  property.nonObject
  345    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  386    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  387    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  412    Cannot access property $id on mixed.
         🪪  property.nonObject
  418    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  466    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  467    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  478    Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  482    Cannot access property $id on mixed.
         🪪  property.nonObject
  491    Cannot access property $id on mixed.
         🪪  property.nonObject
  491    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  497    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  498    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  508    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  509    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  524    Cannot access property $type on mixed.
         🪪  property.nonObject
  545    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  546    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  557    Cannot access property $id on mixed.
         🪪  property.nonObject
  562    Cannot access property $id on mixed.
         🪪  property.nonObject
  562    Cannot access property $id on mixed.
         🪪  property.nonObject
  608    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  609    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  626    Cannot access property $id on mixed.
         🪪  property.nonObject
  626    Parameter #1 $id of method Roster\Services\AvailabilityService::update() expects int, mixed given.
         🪪  argument.type
  631    Cannot access property $id on mixed.
         🪪  property.nonObject
  700    Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/Core/AvailabilityCheckerTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/Core/ResourcePublisherServiceTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/Core/TimeSlotUtilityTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/ImpedimentServiceTest.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  40     Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  40     Property Tests\Feature\Services\ImpedimentServiceTest::$testSchedulable (Tests\Support\TestSchedulable) does not accept mixed.
         🪪  assign.propertyType
  43     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  43     Property Tests\Feature\Services\ImpedimentServiceTest::$testAvailability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  44     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  55     Property Tests\Feature\Services\ImpedimentServiceTest::$impedimentService (Roster\Services\ImpedimentService) does not accept mixed.
         🪪  assign.propertyType
  56     Property Tests\Feature\Services\ImpedimentServiceTest::$scheduleService (Roster\Services\ScheduleService) does not accept mixed.
         🪪  assign.propertyType
  91     Call to method PHPUnit\Framework\Assert::assertNotNull() with int will always evaluate to true.
         🪪  method.alreadyNarrowedType
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  92     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  93     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  138    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  139    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  140    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  154    Cannot access property $id on mixed.
         🪪  property.nonObject
  154    Parameter #1 $id of method Roster\Services\ImpedimentService::update() expects int, mixed given.
         🪪  argument.type
  158    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  159    Cannot access property $reason on mixed.
         🪪  property.nonObject
  160    Cannot access property $metadata on mixed.
         🪪  property.nonObject
  162    Cannot access property $start_datetime on mixed.
         🪪  property.nonObject
  168    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  169    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  170    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  183    Cannot access property $id on mixed.
         🪪  property.nonObject
  183    Parameter #1 $id of method Roster\Services\ImpedimentService::update() expects int, mixed given.
         🪪  argument.type
  187    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  188    Cannot access property $start_datetime on mixed.
         🪪  property.nonObject
  189    Cannot access property $end_datetime on mixed.
         🪪  property.nonObject
  219    Call to an undefined static method Roster\Models\Impediment::find().
         🪪  staticMethod.notFound
  242    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  243    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  244    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  252    Cannot access property $id on mixed.
         🪪  property.nonObject
  252    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  256    Cannot access property $id on mixed.
         🪪  property.nonObject
  262    Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  263    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  264    Cannot access property $id on mixed.
         🪪  property.nonObject
  274    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  275    Cannot access property $id on mixed.
         🪪  property.nonObject
  276    Cannot access property $id on mixed.
         🪪  property.nonObject
  284    Cannot access property $id on mixed.
         🪪  property.nonObject
  284    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  316    Cannot access property $reason on mixed.
         🪪  property.nonObject
  317    Cannot access property $reason on mixed.
         🪪  property.nonObject
  397    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  398    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  399    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  409    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  409    Parameter #1 $availabilityId of method Roster\Services\ImpedimentService::wouldOverlapWithSchedule() expects int, mixed given.
         🪪  argument.type
  421    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  422    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  423    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  433    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  433    Parameter #1 $availabilityId of method Roster\Services\ImpedimentService::wouldOverlapWithSchedule() expects int, mixed given.
         🪪  argument.type
  445    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  446    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  447    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  456    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  456    Parameter #1 $availabilityId of method Roster\Services\ImpedimentService::wouldOverlapWithSchedule() expects int, mixed given.
         🪪  argument.type
  459    Cannot access property $id on mixed.
         🪪  property.nonObject
  459    Parameter #4 $exceptImpedimentId of method Roster\Services\ImpedimentService::wouldOverlapWithSchedule() expects int|null, mixed given.
         🪪  argument.type
  469    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  470    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  471    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  480    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  480    Parameter #1 $availabilityId of method Roster\Services\ImpedimentService::wouldOverlapWithOtherImpediment() expects int, mixed given.
         🪪  argument.type
  492    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  493    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  494    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  503    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  503    Parameter #1 $availabilityId of method Roster\Services\ImpedimentService::wouldOverlapWithOtherImpediment() expects int, mixed given.
         🪪  argument.type
  558    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  559    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  570    Parameter #1 $availability of method Roster\Services\ImpedimentService::create() expects Roster\Models\Availability, mixed given.
         🪪  argument.type
  612    Parameter #1 $validator of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Validation\ValidatorInterface, mixed given.
         🪪  argument.type
  613    Parameter #2 $availabilityRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\AvailabilityRepositoryInterface, mixed given.
         🪪  argument.type
  614    Parameter #3 $impedimentRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\ImpedimentRepositoryInterface, mixed given.
         🪪  argument.type
  615    Parameter #4 $scheduleRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\ScheduleRepositoryInterface, mixed given.
         🪪  argument.type
  628    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  629    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  648    Parameter #1 $validator of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Validation\ValidatorInterface, mixed given.
         🪪  argument.type
  650    Parameter #3 $impedimentRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\ImpedimentRepositoryInterface, mixed given.
         🪪  argument.type
  651    Parameter #4 $scheduleRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\ScheduleRepositoryInterface, mixed given.
         🪪  argument.type
  652    Parameter #5 $slotFinder of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Services\SlotFinderInterface, mixed given.
         🪪  argument.type
  717    Cannot access property $reason on mixed.
         🪪  property.nonObject
  718    Cannot access property $reason on mixed.
         🪪  property.nonObject
  719    Cannot access property $reason on mixed.
         🪪  property.nonObject
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/ScheduleServiceTest.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------
  31     Property Tests\Unit\Services\ScheduleServiceTest::$baseScheduleData type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  38     Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  38     Property Tests\Unit\Services\ScheduleServiceTest::$testSchedulable (Tests\Support\TestSchedulable) does not accept mixed.
         🪪  assign.propertyType
  41     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  41     Property Tests\Unit\Services\ScheduleServiceTest::$testAvailability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  42     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  97     Call to method PHPUnit\Framework\Assert::assertNotNull() with int will always evaluate to true.
         🪪  method.alreadyNarrowedType
         💡  Because the type is coming from a PHPDoc, you can turn off this check by setting treatPhpDocTypesAsCertain: false in your phpstan.neon.
  98     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  99     Access to an undefined property Roster\Models\Schedule::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  99     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  100    Access to an undefined property Roster\Models\Schedule::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  161    Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  162    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  163    Cannot access property $id on mixed.
         🪪  property.nonObject
  175    Parameter #1 $availability of method Roster\Services\ScheduleService::create() expects Roster\Models\Availability, mixed given.
         🪪  argument.type
  282    Call to an undefined static method Roster\Models\Schedule::find().
         🪪  staticMethod.notFound
  314    Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  315    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  316    Cannot access property $id on mixed.
         🪪  property.nonObject
  326    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  327    Cannot access property $id on mixed.
         🪪  property.nonObject
  328    Cannot access property $id on mixed.
         🪪  property.nonObject
  337    Cannot access property $id on mixed.
         🪪  property.nonObject
  337    Parameter #1 $id of method Roster\Services\ScheduleService::find() expects int, mixed given.
         🪪  argument.type
  369    Cannot access property $title on mixed.
         🪪  property.nonObject
  370    Cannot access property $title on mixed.
         🪪  property.nonObject
  392    Cannot access property $title on mixed.
         🪪  property.nonObject
  393    Cannot access property $title on mixed.
         🪪  property.nonObject
  458    Cannot call method gte() on mixed.
         🪪  method.nonObject
  491    Cannot access offset 'start' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  491    Cannot call method format() on mixed.
         🪪  method.nonObject
  492    Cannot access offset 'start' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  492    Cannot call method format() on mixed.
         🪪  method.nonObject
  498    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  499    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  561    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  562    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  563    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  597    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  598    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  609    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  610    Cannot access property $id on mixed.
         🪪  property.nonObject
  611    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  653    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  653    Cannot call method format() on mixed.
         🪪  method.nonObject
  654    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  655    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  657    Cannot call method lte() on mixed.
         🪪  method.nonObject
  658    Cannot call method gte() on mixed.
         🪪  method.nonObject
  659    Cannot call method format() on mixed.
         🪪  method.nonObject
  659    Cannot call method format() on mixed.
         🪪  method.nonObject
  659    Part $slotEnd->format('H:i') (mixed) of encapsed string cannot be cast to string.
         🪪  encapsedStringPart.nonString
  659    Part $slotStart->format('H:i') (mixed) of encapsed string cannot be cast to string.
         🪪  encapsedStringPart.nonString
  703    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  704    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  705    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  739    Unable to resolve the template type TValue in call to function collect
         🪪  argument.templateType
         💡  See: https://phpstan.org/blog/solving-phpstan-error-unable-to-resolve-template-type
  740    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  741    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  742    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  748    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  749    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  750    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  773    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  773    Cannot call method format() on mixed.
         🪪  method.nonObject
  774    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  774    Cannot call method format() on mixed.
         🪪  method.nonObject
  776    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  776    Cannot call method format() on mixed.
         🪪  method.nonObject
  777    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  777    Cannot call method format() on mixed.
         🪪  method.nonObject
  779    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  779    Cannot call method format() on mixed.
         🪪  method.nonObject
  780    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  780    Cannot call method format() on mixed.
         🪪  method.nonObject
  797    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  797    Cannot call method format() on mixed.
         🪪  method.nonObject
  798    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  798    Cannot call method format() on mixed.
         🪪  method.nonObject
  830    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  831    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  845    Parameter #1 $availability of method Roster\Services\ScheduleService::create() expects Roster\Models\Availability, mixed given.
         🪪  argument.type
  889    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  890    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  891    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  898    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  899    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  900    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  917    Cannot access offset 'start' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  917    Cannot call method gte() on mixed.
         🪪  method.nonObject
  1000   Cannot access offset 'start' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  1000   Parameter #1 $start of method Roster\Services\ScheduleService::isTimeSlotAvailable() expects Illuminate\Support\Carbon, mixed given.
         🪪  argument.type
  1001   Cannot access offset 'end' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  1001   Parameter #2 $end of method Roster\Services\ScheduleService::isTimeSlotAvailable() expects Illuminate\Support\Carbon, mixed given.
         🪪  argument.type
  1010   Cannot access offset 'start' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  1011   Cannot access offset 'end' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  1022   Cannot access offset 'start' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  1023   Cannot access offset 'end' on array|Illuminate\Support\Carbon.
         🪪  offsetAccess.nonOffsetAccessible
  1066   Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  1067   Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  1086   Parameter #1 $availability of method Roster\Services\ScheduleService::create() expects Roster\Models\Availability, mixed given.
         🪪  argument.type
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/ServiceAbstractTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/RuleScannerTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------
  Line   tests/Unit/Validation/Rules/AvailabilityDaysCoherenceRuleTest.php
 ------ -------------------------------------------------------------------
  238    Cannot cast mixed to string.
         🪪  cast.string
  240    Cannot cast mixed to string.
         🪪  cast.string
 ------ -------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/Rules/AvailabilityRulesTest.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------
  29     Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  29     Property Tests\Unit\Validation\Rules\AvailabilityRulesTest::$testSchedulable (Tests\Support\TestSchedulable) does not accept mixed.
         🪪  assign.propertyType
  80     Cannot cast array<int, string>|string to string.
         🪪  cast.string
  80     Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertStringContainsString() expects string, mixed given.
         🪪  argument.type
  81     Cannot cast array<int, string>|string to string.
         🪪  cast.string
  81     Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertStringContainsString() expects string, mixed given.
         🪪  argument.type
  123    Cannot cast array<int, string>|string to string.
         🪪  cast.string
  123    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertStringContainsString() expects string, mixed given.
         🪪  argument.type
  124    Cannot cast array<int, string>|string to string.
         🪪  cast.string
  124    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertStringContainsString() expects string, mixed given.
         🪪  argument.type
 ------ -------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/Rules/DateRangeRulesTest.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------
  31     Call to an undefined static method Tests\Support\TestSchedulable::create().
         🪪  staticMethod.notFound
  31     Property Tests\Unit\Validation\Rules\DateRangeRulesTest::$testSchedulable (Tests\Support\TestSchedulable) does not accept mixed.
         🪪  assign.propertyType
  82     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  83     Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  114    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  115    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  147    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  148    Access to an undefined property Tests\Support\TestSchedulable::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  197    Cannot cast array<int, string>|string to string.
         🪪  cast.string
  197    Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertStringContainsString() expects string, mixed given.
         🪪  argument.type
 ------ ----------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/Rules/DurationRuleTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/Rules/FutureDateRuleTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/Rules/RequiredFieldsRuleTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/Rules/ScheduleOverlapRuleTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/ValidationContextTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------
  Line   tests/Unit/Validation/ValidatorTest.php
 ------ -----------------------------------------------------------------------------------------------
  21     Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
 ------ -----------------------------------------------------------------------------------------------

 [ERROR] Found 1059 errors

