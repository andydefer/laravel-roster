# PHPStan Static Analysis Report
*Generated: sam. 20 déc. 2025 09:41:43 WAT*

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Installation/InstallationExecutorInterface.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------
  18     Method Roster\Contracts\Installation\InstallationExecutorInterface::executeSteps() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  18     Method Roster\Contracts\Installation\InstallationExecutorInterface::executeSteps() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Installation/InstallationStepInterface.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------
  34     Method Roster\Contracts\Installation\InstallationStepInterface::shouldExecute() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Repository/AvailabilityRepositoryInterface.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  215    Method Roster\Contracts\Repository\AvailabilityRepositoryInterface::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its
         types: TModel
         🪪  missingType.generics
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Repository/ScheduleRepositoryInterface.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  139    Method Roster\Contracts\Repository\ScheduleRepositoryInterface::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its
         types: TModel
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Services/SchedulableServiceInterface.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  41     Method Roster\Contracts\Services\SchedulableServiceInterface::all() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  48     Method Roster\Contracts\Services\SchedulableServiceInterface::get() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Contracts/Services/SlotFinderInterface.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  22     Method Roster\Contracts\Services\SlotFinderInterface::findNextSlot() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  59     Method Roster\Contracts\Services\SlotFinderInterface::findFirstAvailablePeriod() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  110    Method Roster\Contracts\Services\SlotFinderInterface::getAvailableSlotsFromImpediments() has parameter $impediments with generic class Illuminate\Support\Collection but does
         not specify its types: TKey, TValue
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/AvailabilityViolationException.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------
  24     Method Roster\Exceptions\AvailabilityViolationException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/MissingResourceException.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------
  24     Method Roster\Exceptions\MissingResourceException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/NoMatchingAvailabilityException.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------
  23     Method Roster\Exceptions\NoMatchingAvailabilityException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/OverlappingImpedimentException.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------
  24     Method Roster\Exceptions\OverlappingImpedimentException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/OverlappingScheduleException.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------
  24     Method Roster\Exceptions\OverlappingScheduleException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------

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

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/ScheduleImpedimentOverlapException.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------
  27     Method Roster\Exceptions\ScheduleImpedimentOverlapException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/TimeRangeValidationException.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------
  25     Method Roster\Exceptions\TimeRangeValidationException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/TimeSlotOverlapException.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------
  27     Method Roster\Exceptions\TimeSlotOverlapException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Exceptions/ValidationException.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------
  27     Method Roster\Exceptions\ValidationException::__construct() has parameter $context with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ -----------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Facades/Availability.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  31     PHPDoc tag @method has invalid value (static array<array{start: Carbon, end: Carbon, type: string, availability_id: int}> findSlotsInPeriod(
             Carbon $startDate,
             Carbon $endDate,
             int $durationMinutes = 60,
             int $intervalMinutes = 30
         ) Get all available slots in a period.): Unexpected token "\n * ", expected variable at offset 1343 on line 18
         🪪  phpDoc.parseError
  40     Class Roster\Facades\Availability has PHPDoc tag @method for method create() parameter #1 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  40     Class Roster\Facades\Availability has PHPDoc tag @method for method update() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Facades/Impediment.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  30     Class Roster\Facades\Impediment has PHPDoc tag @method for method create() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  30     Class Roster\Facades\Impediment has PHPDoc tag @method for method update() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  30     PHPDoc tag @method for method Roster\Facades\Impediment::all() return type contains generic class Illuminate\Support\Collection but does not specify its types: TKey, TValue
         🪪  missingType.generics
  30     PHPDoc tag @method for method Roster\Facades\Impediment::between() return type contains generic class Illuminate\Support\Collection but does not specify its types: TKey,
         TValue
         🪪  missingType.generics
  30     PHPDoc tag @method for method Roster\Facades\Impediment::get() return type contains generic class Illuminate\Support\Collection but does not specify its types: TKey, TValue
         🪪  missingType.generics
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Facades/Schedule.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  32     Class Roster\Facades\Schedule has PHPDoc tag @method for method create() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  32     Class Roster\Facades\Schedule has PHPDoc tag @method for method findNextAvailableSlot() return type with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  32     Class Roster\Facades\Schedule has PHPDoc tag @method for method update() parameter #2 $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  32     PHPDoc tag @method for method Roster\Facades\Schedule::all() return type contains generic class Illuminate\Support\Collection but does not specify its types: TKey, TValue
         🪪  missingType.generics
  32     PHPDoc tag @method for method Roster\Facades\Schedule::between() return type contains generic class Illuminate\Support\Collection but does not specify its types: TKey,
         TValue
         🪪  missingType.generics
  32     PHPDoc tag @method for method Roster\Facades\Schedule::get() return type contains generic class Illuminate\Support\Collection but does not specify its types: TKey, TValue
         🪪  missingType.generics
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Models/Availability.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  27     Property Roster\Models\Availability::$casts type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  38     Method Roster\Models\Availability::schedulable() return type with generic class Illuminate\Database\Eloquent\Relations\MorphTo does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  46     Method Roster\Models\Availability::schedules() return type with generic class Illuminate\Database\Eloquent\Relations\HasMany does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  54     Method Roster\Models\Availability::impediments() return type with generic class Illuminate\Database\Eloquent\Relations\HasMany does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  66     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  66     Parameter #2 $haystack of function in_array expects array, mixed given.
         🪪  argument.type
  75     Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  75     Cannot call method format() on mixed.
         🪪  method.nonObject
  76     Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  76     Cannot call method format() on mixed.
         🪪  method.nonObject
  82     Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  82     Parameter #1 $date of method Carbon\Carbon::lt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  86     Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  86     Parameter #1 $date of method Carbon\Carbon::gt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Models/Impediment.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  25     Property Roster\Models\Impediment::$casts type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  34     Method Roster\Models\Impediment::availability() return type with generic class Illuminate\Database\Eloquent\Relations\BelongsTo does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  42     Method Roster\Models\Impediment::schedulable() has no return type specified.
         🪪  missingType.return
  52     Access to an undefined property Roster\Models\Impediment::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  52     Access to an undefined property Roster\Models\Impediment::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  52     Cannot call method gt() on mixed.
         🪪  method.nonObject
  52     Cannot call method lt() on mixed.
         🪪  method.nonObject
  60     Access to an undefined property Roster\Models\Impediment::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  60     Access to an undefined property Roster\Models\Impediment::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  60     Cannot call method diffInMinutes() on mixed.
         🪪  method.nonObject
  60     Method Roster\Models\Impediment::getDurationMinutesAttribute() should return float but returns mixed.
         🪪  return.type
  70     Access to an undefined property Roster\Models\Impediment::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  70     Access to an undefined property Roster\Models\Impediment::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  70     Cannot call method gte() on mixed.
         🪪  method.nonObject
  70     Cannot call method lte() on mixed.
         🪪  method.nonObject
  78     Access to an undefined property Roster\Models\Impediment::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  78     Cannot call method gt() on mixed.
         🪪  method.nonObject
  78     Method Roster\Models\Impediment::isUpcoming() should return bool but returns mixed.
         🪪  return.type
  86     Access to an undefined property Roster\Models\Impediment::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  86     Cannot call method lt() on mixed.
         🪪  method.nonObject
  86     Method Roster\Models\Impediment::isPast() should return bool but returns mixed.
         🪪  return.type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Models/Schedule.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  26     Property Roster\Models\Schedule::$casts type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  36     Method Roster\Models\Schedule::availability() return type with generic class Illuminate\Database\Eloquent\Relations\BelongsTo does not specify its types: TRelatedModel,
         TDeclaringModel
         🪪  missingType.generics
  44     Method Roster\Models\Schedule::schedulable() has no return type specified.
         🪪  missingType.return
  46     Access to an undefined property Roster\Models\Schedule::$availability.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  46     Cannot call method schedulable() on mixed.
         🪪  method.nonObject
  54     Access to an undefined property Roster\Models\Schedule::$availability.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  54     Cannot access property $type on mixed.
         🪪  property.nonObject
  54     Method Roster\Models\Schedule::getTypeAttribute() should return string but returns mixed.
         🪪  return.type
  62     Access to an undefined property Roster\Models\Schedule::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  62     Access to an undefined property Roster\Models\Schedule::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  62     Cannot call method gt() on mixed.
         🪪  method.nonObject
  62     Cannot call method lt() on mixed.
         🪪  method.nonObject
  70     Access to an undefined property Roster\Models\Schedule::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  70     Access to an undefined property Roster\Models\Schedule::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  70     Cannot call method diffInMinutes() on mixed.
         🪪  method.nonObject
  70     Method Roster\Models\Schedule::getDurationMinutesAttribute() should return float but returns mixed.
         🪪  return.type
  80     Access to an undefined property Roster\Models\Schedule::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  80     Access to an undefined property Roster\Models\Schedule::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  80     Cannot call method gte() on mixed.
         🪪  method.nonObject
  80     Cannot call method lte() on mixed.
         🪪  method.nonObject
  88     Access to an undefined property Roster\Models\Schedule::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Cannot call method gt() on mixed.
         🪪  method.nonObject
  88     Method Roster\Models\Schedule::isUpcoming() should return bool but returns mixed.
         🪪  return.type
  96     Access to an undefined property Roster\Models\Schedule::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  96     Cannot call method lt() on mixed.
         🪪  method.nonObject
  96     Method Roster\Models\Schedule::isPast() should return bool but returns mixed.
         🪪  return.type
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Repositories/AvailabilityRepository.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  32     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  32     Method Roster\Repositories\AvailabilityRepository::create() should return Roster\Models\Availability but returns mixed.
         🪪  return.type
  69     Method Roster\Repositories\AvailabilityRepository::getForDateRange() should return Illuminate\Support\Collection<int, Roster\Models\Availability> but returns Illuminate\Data
         base\Eloquent\Collection<int, Illuminate\Database\Eloquent\Model>.
         🪪  return.type
  80     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  80     Call to an undefined static method Roster\Models\Availability::where().
         🪪  staticMethod.notFound
  80     Cannot call method first() on mixed.
         🪪  method.nonObject
  80     Cannot call method when() on mixed.
         🪪  method.nonObject
  80     Cannot call method where() on mixed.
         🪪  method.nonObject
  80     Cannot call method where() on mixed.
         🪪  method.nonObject
  80     Cannot call method where() on mixed.
         🪪  method.nonObject
  80     Cannot call method where() on mixed.
         🪪  method.nonObject
  80     Cannot call method where() on mixed.
         🪪  method.nonObject
  80     Cannot call method whereJsonContains() on mixed.
         🪪  method.nonObject
  80     Cannot call method withExists() on mixed.
         🪪  method.nonObject
  80     Cannot call method withExists() on mixed.
         🪪  method.nonObject
  80     Method Roster\Repositories\AvailabilityRepository::findForTimeSlotWithPartialOverlaps() should return Roster\Models\Availability|null but returns mixed.
         🪪  return.type
  83     Cannot call method where() on mixed.
         🪪  method.nonObject
  89     Cannot call method orWhere() on mixed.
         🪪  method.nonObject
  89     Cannot call method whereNull() on mixed.
         🪪  method.nonObject
  93     Cannot call method orWhere() on mixed.
         🪪  method.nonObject
  93     Cannot call method whereNull() on mixed.
         🪪  method.nonObject
  98     Cannot call method where() on mixed.
         🪪  method.nonObject
  98     Cannot call method where() on mixed.
         🪪  method.nonObject
  102    Cannot call method where() on mixed.
         🪪  method.nonObject
  102    Cannot call method where() on mixed.
         🪪  method.nonObject
  119    Method Roster\Repositories\AvailabilityRepository::delete() should return bool but returns bool|null.
         🪪  return.type
  127    Call to an undefined static method Roster\Models\Availability::whereIn().
         🪪  staticMethod.notFound
  127    Cannot call method delete() on mixed.
         🪪  method.nonObject
  135    Call to an undefined static method Roster\Models\Availability::find().
         🪪  staticMethod.notFound
  135    Method Roster\Repositories\AvailabilityRepository::findById() should return Roster\Models\Availability|null but returns mixed.
         🪪  return.type
  180    Parameter #1 $builder of method Roster\Repositories\AvailabilityRepository::applyDateFilters() expects Illuminate\Database\Eloquent\Builder,
         Illuminate\Database\Query\Builder given.
         🪪  argument.type
  195    Method Roster\Repositories\AvailabilityRepository::getAll() should return Illuminate\Support\Collection<int, Roster\Models\Availability> but returns Illuminate\Support\Colle
         ction<int, stdClass>.
         🪪  return.type
  221    Method Roster\Repositories\AvailabilityRepository::getAllForSchedulable() should return Illuminate\Support\Collection<int, Roster\Models\Availability> but returns Illuminate
         \Support\Collection<int, stdClass>.
         🪪  return.type
  239    Parameter #1 $builder of method Roster\Repositories\AvailabilityRepository::applyDateFilters() expects Illuminate\Database\Eloquent\Builder,
         Illuminate\Database\Query\Builder given.
         🪪  argument.type
  259    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  260    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  271    Argument of an invalid type mixed supplied for foreach, only iterables are supported.
         🪪  foreach.nonIterable
  294    Cannot call method where() on mixed.
         🪪  method.nonObject
  294    Cannot call method where() on mixed.
         🪪  method.nonObject
  298    Cannot call method orWhereNull() on mixed.
         🪪  method.nonObject
  298    Cannot call method where() on mixed.
         🪪  method.nonObject
  302    Cannot call method orWhereNull() on mixed.
         🪪  method.nonObject
  302    Cannot call method where() on mixed.
         🪪  method.nonObject
  314    Cannot call method where() on mixed.
         🪪  method.nonObject
  315    Cannot call method orWhere() on mixed.
         🪪  method.nonObject
  315    Cannot call method orWhereBetween() on mixed.
         🪪  method.nonObject
  315    Cannot call method whereBetween() on mixed.
         🪪  method.nonObject
  318    Cannot call method where() on mixed.
         🪪  method.nonObject
  318    Cannot call method where() on mixed.
         🪪  method.nonObject
  324    Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  329    Cannot call method where() on mixed.
         🪪  method.nonObject
  330    Cannot call method orWhere() on mixed.
         🪪  method.nonObject
  330    Cannot call method orWhereBetween() on mixed.
         🪪  method.nonObject
  330    Cannot call method whereBetween() on mixed.
         🪪  method.nonObject
  333    Cannot call method where() on mixed.
         🪪  method.nonObject
  333    Cannot call method where() on mixed.
         🪪  method.nonObject
  339    Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  394    Method Roster\Repositories\AvailabilityRepository::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  405    Parameter #1 $string of function strtolower expects string, mixed given.
         🪪  argument.type
  414    Method Roster\Repositories\AvailabilityRepository::buildBaseQuery() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  416    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  416    Call to an undefined static method Roster\Models\Availability::where().
         🪪  staticMethod.notFound
  416    Cannot call method where() on mixed.
         🪪  method.nonObject
  416    Method Roster\Repositories\AvailabilityRepository::buildBaseQuery() should return Illuminate\Database\Eloquent\Builder but returns mixed.
         🪪  return.type
  423    Method Roster\Repositories\AvailabilityRepository::applyTimeSlotFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify
         its types: TModel
         🪪  missingType.generics
  435    Method Roster\Repositories\AvailabilityRepository::applyDateFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  443    Method Roster\Repositories\AvailabilityRepository::applyDateRangeFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not
         specify its types: TModel
         🪪  missingType.generics
  464    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  464    Parameter #2 $haystack of function in_array expects array, mixed given.
         🪪  argument.type
  468    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  468    Parameter #1 $date of method Carbon\Carbon::lt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  472    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  472    Parameter #1 $date of method Carbon\Carbon::gt() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  484    Method Roster\Repositories\AvailabilityRepository::getAvailabilitiesWithConflictInfo() return type with generic class Illuminate\Support\Collection does not specify its
         types: TKey, TValue
         🪪  missingType.generics
  490    Parameter #1 $model of method Roster\Repositories\AvailabilityRepository::getForDateRange() expects Illuminate\Database\Eloquent\Model, object given.
         🪪  argument.type
  492    Call to an undefined method Illuminate\Support\Collection<int, Roster\Models\Availability>::load().
         🪪  method.notFound
  492    Method Roster\Repositories\AvailabilityRepository::getAvailabilitiesWithConflictInfo() should return Illuminate\Support\Collection&iterable<Roster\Models\Availability> but r
         eturns mixed.
         🪪  return.type
  502    Method Roster\Repositories\AvailabilityRepository::filterAvailabilitiesForDate() has parameter $availabilities with generic class Illuminate\Support\Collection but does not
         specify its types: TKey, TValue
         🪪  missingType.generics
  502    Method Roster\Repositories\AvailabilityRepository::filterAvailabilitiesForDate() return type with generic class Illuminate\Support\Collection does not specify its types:
         TKey, TValue
         🪪  missingType.generics
  505    Parameter #1 $callback of method Illuminate\Support\Collection<(int|string),mixed>::filter() expects (callable(mixed, int|string): bool)|null,
         Closure(Roster\Models\Availability): bool given.
         🪪  argument.type
         💡  Type #1 from the union: Type Roster\Models\Availability of parameter #1 $availability of passed callable needs to be same or wider than parameter type mixed of accepting
         callable.
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Repositories/ImpedimentRepository.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  20     Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  20     Method Roster\Repositories\ImpedimentRepository::create() should return Roster\Models\Impediment but returns mixed.
         🪪  return.type
  48     Method Roster\Repositories\ImpedimentRepository::delete() should return bool but returns bool|null.
         🪪  return.type
  56     Call to an undefined static method Roster\Models\Impediment::find().
         🪪  staticMethod.notFound
  56     Method Roster\Repositories\ImpedimentRepository::findById() should return Roster\Models\Impediment|null but returns mixed.
         🪪  return.type
  64     Method Roster\Repositories\ImpedimentRepository::getAll() should return Illuminate\Support\Collection<int, Illuminate\Database\Eloquent\Model> but returns Illuminate\Support
         \Collection<int, stdClass>.
         🪪  return.type
  77     Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  77     Cannot call method get() on mixed.
         🪪  method.nonObject
  77     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  77     Cannot call method where() on mixed.
         🪪  method.nonObject
  77     Cannot call method where() on mixed.
         🪪  method.nonObject
  77     Method Roster\Repositories\ImpedimentRepository::findForTimeSlot() should return Illuminate\Support\Collection<int, object> but returns mixed.
         🪪  return.type
  93     Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  93     Cannot call method where() on mixed.
         🪪  method.nonObject
  93     Cannot call method where() on mixed.
         🪪  method.nonObject
  98     Cannot call method where() on mixed.
         🪪  method.nonObject
  101    Cannot call method exists() on mixed.
         🪪  method.nonObject
  101    Method Roster\Repositories\ImpedimentRepository::hasOverlappingImpediments() should return bool but returns mixed.
         🪪  return.type
  107    Method Roster\Repositories\ImpedimentRepository::findOverlappingImpediments() return type with generic class Illuminate\Support\Collection does not specify its types: TKey,
         TValue
         🪪  missingType.generics
  113    Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  113    Cannot call method where() on mixed.
         🪪  method.nonObject
  120    Cannot call method where() on mixed.
         🪪  method.nonObject
  123    Cannot call method get() on mixed.
         🪪  method.nonObject
  123    Method Roster\Repositories\ImpedimentRepository::findOverlappingImpediments() should return Illuminate\Support\Collection but returns mixed.
         🪪  return.type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Repositories/ScheduleRepository.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  20     Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  20     Method Roster\Repositories\ScheduleRepository::create() should return Roster\Models\Schedule but returns mixed.
         🪪  return.type
  48     Method Roster\Repositories\ScheduleRepository::delete() should return bool but returns bool|null.
         🪪  return.type
  58     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  58     Cannot call method where() on mixed.
         🪪  method.nonObject
  62     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  72     Method Roster\Repositories\ScheduleRepository::getAll() should return Illuminate\Support\Collection<int, Illuminate\Database\Eloquent\Model> but returns Illuminate\Support\C
         ollection<int, stdClass>.
         🪪  return.type
  85     Call to an undefined static method Roster\Models\Schedule::where().
         🪪  staticMethod.notFound
  85     Cannot call method get() on mixed.
         🪪  method.nonObject
  85     Cannot call method orderBy() on mixed.
         🪪  method.nonObject
  85     Cannot call method where() on mixed.
         🪪  method.nonObject
  85     Cannot call method where() on mixed.
         🪪  method.nonObject
  85     Method Roster\Repositories\ScheduleRepository::findForTimeSlot() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns mixed.
         🪪  return.type
  101    Call to an undefined static method Roster\Models\Schedule::where().
         🪪  staticMethod.notFound
  101    Cannot call method where() on mixed.
         🪪  method.nonObject
  101    Cannot call method where() on mixed.
         🪪  method.nonObject
  106    Cannot call method where() on mixed.
         🪪  method.nonObject
  109    Cannot call method exists() on mixed.
         🪪  method.nonObject
  109    Method Roster\Repositories\ScheduleRepository::hasOverlappingSchedule() should return bool but returns mixed.
         🪪  return.type
  121    Call to an undefined static method Roster\Models\Schedule::where().
         🪪  staticMethod.notFound
  121    Cannot call method where() on mixed.
         🪪  method.nonObject
  128    Cannot call method where() on mixed.
         🪪  method.nonObject
  131    Cannot call method get() on mixed.
         🪪  method.nonObject
  131    Method Roster\Repositories\ScheduleRepository::findOverlappingSchedules() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns mixed.
         🪪  return.type
  149    Method Roster\Repositories\ScheduleRepository::getAllForSchedulable() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns Illuminate\Support
         \Collection<int, stdClass>.
         🪪  return.type
  168    Method Roster\Repositories\ScheduleRepository::getForDateRange() should return Illuminate\Support\Collection<int, Roster\Models\Schedule> but returns Illuminate\Support\Coll
         ection<int, stdClass>.
         🪪  return.type
  174    Method Roster\Repositories\ScheduleRepository::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  188    Method Roster\Repositories\ScheduleRepository::buildSchedulableQuery() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  190    Call to an undefined static method Roster\Models\Schedule::whereHas().
         🪪  staticMethod.notFound
  190    Method Roster\Repositories\ScheduleRepository::buildSchedulableQuery() should return Illuminate\Database\Eloquent\Builder but returns mixed.
         🪪  return.type
  191    Cannot call method where() on mixed.
         🪪  method.nonObject
  191    Cannot call method where() on mixed.
         🪪  method.nonObject
  201    Method Roster\Repositories\ScheduleRepository::applyCommonFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/RosterServiceProvider.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  105    Cannot call method make() on mixed.
         🪪  method.nonObject
  105    Parameter $validationService of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Services\ValidationServiceInterface, mixed given.
         🪪  argument.type
  106    Cannot call method make() on mixed.
         🪪  method.nonObject
  106    Parameter $availabilityRepository of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Repository\AvailabilityRepositoryInterface, mixed given.
         🪪  argument.type
  107    Cannot call method make() on mixed.
         🪪  method.nonObject
  107    Parameter $impedimentRepository of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Repository\ImpedimentRepositoryInterface, mixed given.
         🪪  argument.type
  108    Cannot call method make() on mixed.
         🪪  method.nonObject
  108    Parameter $scheduleRepository of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Repository\ScheduleRepositoryInterface, mixed given.
         🪪  argument.type
  109    Cannot call method make() on mixed.
         🪪  method.nonObject
  109    Parameter $slotFinder of class Roster\Services\ScheduleService constructor expects Roster\Contracts\Services\SlotFinderInterface, mixed given.
         🪪  argument.type
  115    Cannot call method make() on mixed.
         🪪  method.nonObject
  115    Parameter $availabilityValidator of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Services\AvailabilityValidatorInterface, mixed given.
         🪪  argument.type
  116    Cannot call method make() on mixed.
         🪪  method.nonObject
  116    Parameter $validationService of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Services\ValidationServiceInterface, mixed given.
         🪪  argument.type
  117    Cannot call method make() on mixed.
         🪪  method.nonObject
  117    Parameter $availabilityRepository of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Repository\AvailabilityRepositoryInterface, mixed given.
         🪪  argument.type
  118    Cannot call method make() on mixed.
         🪪  method.nonObject
  118    Parameter $availabilityMerger of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Services\AvailabilityMergerInterface, mixed given.
         🪪  argument.type
  119    Cannot call method make() on mixed.
         🪪  method.nonObject
  119    Parameter $slotFinder of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Services\SlotFinderInterface, mixed given.
         🪪  argument.type
  120    Cannot call method make() on mixed.
         🪪  method.nonObject
  120    Parameter $availabilityChecker of class Roster\Services\AvailabilityService constructor expects Roster\Contracts\Services\AvailabilityCheckerInterface, mixed given.
         🪪  argument.type
  126    Cannot call method make() on mixed.
         🪪  method.nonObject
  126    Parameter $validationService of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Services\ValidationServiceInterface, mixed given.
         🪪  argument.type
  127    Cannot call method make() on mixed.
         🪪  method.nonObject
  127    Parameter $availabilityRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\AvailabilityRepositoryInterface, mixed given.
         🪪  argument.type
  128    Cannot call method make() on mixed.
         🪪  method.nonObject
  128    Parameter $impedimentRepository of class Roster\Services\ImpedimentService constructor expects Roster\Contracts\Repository\ImpedimentRepositoryInterface, mixed given.
         🪪  argument.type
  144    Parameter $application of class Roster\Services\Core\ResourcePublisherService constructor expects Illuminate\Contracts\Foundation\Application, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/AvailabilityService.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  64     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  65     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  69     Parameter #1 $minutes of method Roster\Services\Core\AbstractEntityScopingService::throwMinimumDurationException() expects int, mixed given.
         🪪  argument.type
  77     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  78     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  98     Parameter #1 $model of method Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  105    Parameter #2 $model of method Roster\Contracts\Services\AvailabilityMergerInterface::mergeWithAdjacent() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  106    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  107    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  132    Cannot access property $start_time on Roster\Models\Availability|null.
         🪪  property.nonObject
  132    Cannot call method format() on mixed.
         🪪  method.nonObject
  133    Cannot access property $end_time on Roster\Models\Availability|null.
         🪪  property.nonObject
  133    Cannot call method format() on mixed.
         🪪  method.nonObject
  140    Parameter #1 $availability of method Roster\Services\AvailabilityService::prepareCheckData() expects Roster\Models\Availability, Roster\Models\Availability|null given.
         🪪  argument.type
  142    Parameter #1 $model of method Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  142    Parameter #2 $data of method Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping() expects array<string, mixed>, array given.
         🪪  argument.type
  214    Method Roster\Services\AvailabilityService::hasOverlapping() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  218    Parameter #1 $model of method Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  218    Parameter #2 $data of method Roster\Contracts\Services\AvailabilityCheckerInterface::hasOverlapping() expects array<string, mixed>, array given.
         🪪  argument.type
  221    Method Roster\Services\AvailabilityService::findOverlapping() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  221    Method Roster\Services\AvailabilityService::findOverlapping() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  224    Parameter #1 $data of method Roster\Contracts\Services\ValidationServiceInterface::parseAndValidateTimeRange() expects array<string, mixed>, array given.
         🪪  argument.type
  226    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findOverlapping() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  226    Parameter #2 $availabilityData of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findOverlapping() expects array<string, mixed>, array given.
         🪪  argument.type
  229    Method Roster\Services\AvailabilityService::findAdjacentAvailabilities() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  229    Method Roster\Services\AvailabilityService::findAdjacentAvailabilities() return type with generic class Illuminate\Support\Collection does not specify its types: TKey,
         TValue
         🪪  missingType.generics
  233    Parameter #1 $data of method Roster\Contracts\Services\AvailabilityMergerInterface::findAdjacentAvailabilities() expects array<string, mixed>, array given.
         🪪  argument.type
  233    Parameter #2 $model of method Roster\Contracts\Services\AvailabilityMergerInterface::findAdjacentAvailabilities() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  247    Parameter #1 $model of method Roster\Contracts\Services\AvailabilityCheckerInterface::isAvailableAt() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  254    Parameter #1 $model of method Roster\Contracts\Services\AvailabilityCheckerInterface::isAvailableForPeriod() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  257    Method Roster\Services\AvailabilityService::findSlotsInPeriod() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  267    Parameter #1 $model of method Roster\Contracts\Services\SlotFinderInterface::findSlotsInPeriod() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  276    Method Roster\Services\AvailabilityService::prepareCheckData() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  276    Method Roster\Services\AvailabilityService::prepareCheckData() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  279    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  280    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  281    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  281    Cannot call method format() on mixed.
         🪪  method.nonObject
  282    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  282    Cannot call method format() on mixed.
         🪪  method.nonObject
  285    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  286    Cannot call method format() on mixed.
         🪪  method.nonObject
  289    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  290    Cannot call method format() on mixed.
         🪪  method.nonObject
  293    Parameter #1 $data of method Roster\Contracts\Services\ValidationServiceInterface::parseAndValidateTimeRange() expects array<string, mixed>, array<mixed> given.
         🪪  argument.type
  298    Method Roster\Services\AvailabilityService::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  300    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::applyFilters() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/AbstractEntityScopingService.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  88     Method Roster\Services\Core\AbstractEntityScopingService::all() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  96     Method Roster\Services\Core\AbstractEntityScopingService::get() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  160    Method Roster\Services\Core\AbstractEntityScopingService::applyConfigurationRules() should return array<string, mixed> but returns array.
         🪪  return.type
  163    Method Roster\Services\Core\AbstractEntityScopingService::applyConfigurationRules() should return array<string, mixed> but returns array.
         🪪  return.type
  169    Method Roster\Services\Core\AbstractEntityScopingService::applyCreateConfigurationRules() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  169    Method Roster\Services\Core\AbstractEntityScopingService::applyCreateConfigurationRules() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  172    Parameter #1 $data of method Roster\Services\Core\AbstractEntityScopingService::applyEntitySpecificDefaults() expects array<string, mixed>, array given.
         🪪  argument.type
  180    Method Roster\Services\Core\AbstractEntityScopingService::applyUpdateConfigurationRules() has parameter $data with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  180    Method Roster\Services\Core\AbstractEntityScopingService::applyUpdateConfigurationRules() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  191    Method Roster\Services\Core\AbstractEntityScopingService::applyEntitySpecificDefaults() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  218    Cannot access offset 'enabled' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  222    Parameter #3 $entityConfig of method Roster\Services\Core\AbstractEntityScopingService::validateFutureDates() expects array<string, mixed>, mixed given.
         🪪  argument.type
  241    Possibly invalid array key type mixed.
         🪪  offsetAccess.invalidOffset
  246    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  246    Possibly invalid array key type mixed.
         🪪  offsetAccess.invalidOffset
  253    Parameter #2 $field of static method Roster\Exceptions\Messages\ErrorMessageFactory::pastDate() expects string, mixed given.
         🪪  argument.type
  271    Parameter #2 $minImpedimentMinutes of method Roster\Services\Core\AbstractEntityScopingService::validateDurationHook() expects int, mixed given.
         🪪  argument.type
  271    Parameter #3 $minScheduleMinutes of method Roster\Services\Core\AbstractEntityScopingService::validateDurationHook() expects int, mixed given.
         🪪  argument.type
  271    Parameter #4 $defaultDurationMinutes of method Roster\Services\Core\AbstractEntityScopingService::validateDurationHook() expects int, mixed given.
         🪪  argument.type
  281    Parameter #2 $maxDays of method Roster\Services\Core\AbstractEntityScopingService::validateMaxDaysHook() expects int, mixed given.
         🪪  argument.type
  285    Parameter #1 $timezone of method Roster\Services\Core\AbstractEntityScopingService::validateTimezoneHook() expects string, mixed given.
         🪪  argument.type
  320    Method Roster\Services\Core\AbstractEntityScopingService::getDateTimeFields() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  340    Possibly invalid array key type mixed.
         🪪  offsetAccess.invalidOffset
  367    Binary operation "." between mixed and string results in an error.
         🪪  binaryOp.invalid
  410    Method Roster\Services\Core\AbstractEntityScopingService::validateRequiredFields() has parameter $requiredFields with no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  414    Parameter #1 ...$arrays of function array_merge expects array, mixed given.
         🪪  argument.type
  417    Possibly invalid array key type mixed.
         🪪  offsetAccess.invalidOffset
  417    Possibly invalid array key type mixed.
         🪪  offsetAccess.invalidOffset
  419    Parameter #2 ...$values of function sprintf expects bool|float|int|string|null, mixed given.
         🪪  argument.type
  470    Parameter #1 $object_or_class of function method_exists expects object|string, mixed given.
         🪪  argument.type
  470    Parameter #1 $object_or_class of function property_exists expects object|string, mixed given.
         🪪  argument.type
  471    Cannot access property $id on mixed.
         🪪  property.nonObject
  471    Cannot call method getId() on mixed.
         🪪  method.nonObject
  472    Cannot cast mixed to int.
         🪪  cast.int
  507    Method Roster\Services\Core\AbstractEntityScopingService::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/AvailabilityMerger.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  47     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  55     Parameter #1 $ids of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::deleteMultiple() expects array<int>, list<mixed> given.
         🪪  argument.type
  88     Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Access to an undefined property object::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  89     Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  90     Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  91     Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  92     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  93     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  96     Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  96     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  100    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  100    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/AvailabilityValidator.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  32     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  33     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  42     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  43     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  63     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  63     Call to an undefined static method Roster\Models\Availability::where().
         🪪  staticMethod.notFound
  63     Cannot call method where() on mixed.
         🪪  method.nonObject
  63     Cannot call method where() on mixed.
         🪪  method.nonObject
  63     Cannot call method where() on mixed.
         🪪  method.nonObject
  68     Cannot call method where() on mixed.
         🪪  method.nonObject
  69     Argument of an invalid type mixed supplied for foreach, only iterables are supported.
         🪪  foreach.nonIterable
  70     Cannot call method orWhereJsonContains() on mixed.
         🪪  method.nonObject
  76     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  77     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  79     Cannot call method where() on mixed.
         🪪  method.nonObject
  79     Cannot call method where() on mixed.
         🪪  method.nonObject
  84     Cannot call method where() on mixed.
         🪪  method.nonObject
  87     Cannot call method exists() on mixed.
         🪪  method.nonObject
  87     Method Roster\Services\Core\AvailabilityValidator::hasOverlapping() should return bool but returns mixed.
         🪪  return.type
  101    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  101    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  101    Parameter #1 $existingStart of method Roster\Services\Core\AvailabilityValidator::timeOverlaps() expects Illuminate\Support\Carbon, mixed given.
         🪪  argument.type
  101    Parameter #2 $existingEnd of method Roster\Services\Core\AvailabilityValidator::timeOverlaps() expects Illuminate\Support\Carbon, mixed given.
         🪪  argument.type
  107    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  107    Parameter #1 $existingStartDate of method Roster\Services\Core\AvailabilityValidator::dateRangesOverlap() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  108    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  108    Parameter #2 $existingEndDate of method Roster\Services\Core\AvailabilityValidator::dateRangesOverlap() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  138    Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  138    Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  139    Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  139    Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  145    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  145    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  145    Parameter #1 $array of function array_intersect expects array, mixed given.
         🪪  argument.type
  145    Parameter #2 ...$arrays of function array_intersect expects array, mixed given.
         🪪  argument.type
  151    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  151    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  157    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  157    Parameter #1 $existingStartDate of method Roster\Services\Core\AvailabilityValidator::dateRangesOverlap() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  158    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  158    Parameter #2 $existingEndDate of method Roster\Services\Core\AvailabilityValidator::dateRangesOverlap() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  159    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  159    Parameter #3 $newStartDate of method Roster\Services\Core\AvailabilityValidator::dateRangesOverlap() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  160    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  160    Parameter #4 $newEndDate of method Roster\Services\Core\AvailabilityValidator::dateRangesOverlap() expects Illuminate\Support\Carbon|null, mixed given.
         🪪  argument.type
  166    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  166    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  166    Cannot call method eq() on mixed.
         🪪  method.nonObject
  170    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  170    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  170    Cannot call method eq() on mixed.
         🪪  method.nonObject
  193    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  193    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  193    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  193    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  194    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  194    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  194    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  194    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  200    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  200    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  201    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  201    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  202    Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  202    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  203    Parameter #1 $timestamp of static method Carbon\Carbon::createFromTimestamp() expects float|int|string, mixed given.
         🪪  argument.type
  206    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  206    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  207    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  207    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  208    Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  208    Cannot access property $timestamp on mixed.
         🪪  property.nonObject
  209    Parameter #1 $timestamp of static method Carbon\Carbon::createFromTimestamp() expects float|int|string, mixed given.
         🪪  argument.type
  212    Method Roster\Services\Core\AvailabilityValidator::mergeAdjacent() should return array{type: string, start_time: string, end_time: string, days: array<string>, start_date: s
         tring|null, end_date: string|null} but returns array{type: mixed, start_time: non-falsy-string, end_time: non-falsy-string, days: list<mixed>, start_date: non-falsy-string|n
         ull, end_date: non-falsy-string|null}.
         🪪  return.type
         💡  Offset 'type' (string) does not accept type mixed.
         💡  Offset 'days' (array<int|string, string>) does not accept type list<mixed>.
  213    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  214    Parameter #1 $timestamp of static method Carbon\Carbon::createFromTimestamp() expects float|int|string, mixed given.
         🪪  argument.type
  215    Parameter #1 $timestamp of static method Carbon\Carbon::createFromTimestamp() expects float|int|string, mixed given.
         🪪  argument.type
  216    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  216    Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  216    Parameter #1 ...$arrays of function array_merge expects array, mixed given.
         🪪  argument.type
  216    Parameter #2 ...$arrays of function array_merge expects array, mixed given.
         🪪  argument.type
  216    Unable to resolve the template type T in call to function array_values
         🪪  argument.templateType
         💡  See: https://phpstan.org/blog/solving-phpstan-error-unable-to-resolve-template-type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/SlotFinderService.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  39     Method Roster\Services\Core\SlotFinderService::findNextSlot() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  74     Method Roster\Services\Core\SlotFinderService::findNextSlot() should return array|Illuminate\Support\Carbon|null but returns mixed.
         🪪  return.type
  123    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  123    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  124    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  124    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  138    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  139    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  164    Method Roster\Services\Core\SlotFinderService::findFirstAvailablePeriod() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  242    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  247    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  247    Cannot call method format() on mixed.
         🪪  method.nonObject
  248    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  248    Cannot call method format() on mixed.
         🪪  method.nonObject
  319    Method Roster\Services\Core\SlotFinderService::getAvailableSlotsFromImpediments() has parameter $impediments with generic class Illuminate\Support\Collection but does not
         specify its types: TKey, TValue
         🪪  missingType.generics
  333    Access to an undefined property Roster\Models\Impediment::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  334    Access to an undefined property Roster\Models\Impediment::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  336    Cannot call method gt() on mixed.
         🪪  method.nonObject
  338    Cannot call method copy() on mixed.
         🪪  method.nonObject
  339    Cannot call method copy() on mixed.
         🪪  method.nonObject
  343    Cannot call method gt() on mixed.
         🪪  method.nonObject
  346    Cannot call method lt() on mixed.
         🪪  method.nonObject
  348    Cannot call method copy() on mixed.
         🪪  method.nonObject
  382    Method Roster\Services\Core\SlotFinderService::findSlotInAvailability() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  388    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  388    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  389    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  389    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  411    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  412    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  436    Access to an undefined property Roster\Models\Availability::$schedules.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  436    Cannot call method contains() on mixed.
         🪪  method.nonObject
  437    Anonymous function should return bool but returns mixed.
         🪪  return.type
  437    Cannot call method overlapsWith() on mixed.
         🪪  method.nonObject
  440    Access to an undefined property Roster\Models\Availability::$impediments.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  440    Cannot call method contains() on mixed.
         🪪  method.nonObject
  441    Anonymous function should return bool but returns mixed.
         🪪  return.type
  441    Cannot call method overlapsWith() on mixed.
         🪪  method.nonObject
  458    Method Roster\Services\Core\SlotFinderService::findFirstSlotInDailyAvailability() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  466    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  466    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  467    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  467    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  486    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  487    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  512    Access to an undefined property Roster\Models\Availability::$start_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  512    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
  513    Access to an undefined property Roster\Models\Availability::$end_time.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  513    Parameter #1 $date of method Carbon\Carbon::setTimeFrom() expects DateTimeInterface|string, mixed given.
         🪪  argument.type
 ------ ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/Core/ValidationService.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------
  112    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  112    Parameter #1 $timeZone of method Carbon\Carbon::setTimezone() expects DateTimeZone|int|string, mixed given.
         🪪  argument.type
  113    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  113    Parameter #1 $timeZone of method Carbon\Carbon::setTimezone() expects DateTimeZone|int|string, mixed given.
         🪪  argument.type
  133    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  134    Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/ImpedimentService.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  59     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  60     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  71     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  72     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  94     Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  105    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  106    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  112    Parameter #1 $json of function json_decode expects string, mixed given.
         🪪  argument.type
  118    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  118    Method Roster\Services\ImpedimentService::executeCreate() should return Roster\Models\Impediment but returns mixed.
         🪪  return.type
  133    Cannot access property $availability_id on Roster\Models\Impediment|null.
         🪪  property.nonObject
  139    Cannot access property $start_datetime on Roster\Models\Impediment|null.
         🪪  property.nonObject
  140    Cannot access property $end_datetime on Roster\Models\Impediment|null.
         🪪  property.nonObject
  155    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  159    Cannot access property $start_datetime on Roster\Models\Impediment|null.
         🪪  property.nonObject
  160    Cannot access property $end_datetime on Roster\Models\Impediment|null.
         🪪  property.nonObject
  163    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  167    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  167    Cannot access property $availability_id on Roster\Models\Impediment|null.
         🪪  property.nonObject
  168    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  173    Cannot access property $start_datetime on Roster\Models\Impediment|null.
         🪪  property.nonObject
  174    Cannot access property $end_datetime on Roster\Models\Impediment|null.
         🪪  property.nonObject
  180    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  193    Cannot call method update() on Roster\Models\Impediment|null.
         🪪  method.nonObject
  242    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  275    Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  275    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  276    Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  276    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  288    Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  288    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  288    Cannot call method find() on mixed.
         🪪  method.nonObject
  288    Cannot call method where() on mixed.
         🪪  method.nonObject
  288    Method Roster\Services\ImpedimentService::find() should return Roster\Models\Impediment|null but returns mixed.
         🪪  return.type
  289    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  302    Method Roster\Services\ImpedimentService::delete() should return bool but returns bool|null.
         🪪  return.type
  305    Method Roster\Services\ImpedimentService::between() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  322    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  328    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  328    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  331    Method Roster\Services\ImpedimentService::getAvailableTimeSlots() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  336    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  342    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  342    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::findForTimeSlot() expects int, mixed given.
         🪪  argument.type
  354    Parameter #3 $minimumMinutes of method Roster\Contracts\Services\ValidationServiceInterface::validateMinimumDuration() expects int, mixed given.
         🪪  argument.type
  362    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  365    Method Roster\Services\ImpedimentService::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  367    Call to an undefined static method Roster\Models\Impediment::where().
         🪪  staticMethod.notFound
  367    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  367    Cannot call method where() on mixed.
         🪪  method.nonObject
  368    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  370    Parameter #1 $builder of method Roster\Services\ImpedimentService::applyDateFilters() expects Illuminate\Database\Eloquent\Builder, mixed given.
         🪪  argument.type
  371    Parameter #1 $builder of method Roster\Services\ImpedimentService::applyTypeFilter() expects Illuminate\Database\Eloquent\Builder, mixed given.
         🪪  argument.type
  373    Method Roster\Services\ImpedimentService::applyFilters() should return Illuminate\Database\Eloquent\Builder but returns mixed.
         🪪  return.type
  376    Method Roster\Services\ImpedimentService::applyDateFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  387    Method Roster\Services\ImpedimentService::applyTypeFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Services/ScheduleService.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  66     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  67     Parameter #1 $time of static method Carbon\Carbon::parse() expects Carbon\Month|Carbon\WeekDay|DateTimeInterface|float|int|string|null, mixed given.
         🪪  argument.type
  92     Parameter #1 $availabilityId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::hasOverlappingSchedule() expects int, mixed given.
         🪪  argument.type
  103    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  118    Parameter #1 $json of function json_decode expects string, mixed given.
         🪪  argument.type
  143    Cannot access property $start_datetime on Roster\Models\Schedule|null.
         🪪  property.nonObject
  144    Cannot access property $end_datetime on Roster\Models\Schedule|null.
         🪪  property.nonObject
  161    Cannot access property $start_datetime on Roster\Models\Schedule|null.
         🪪  property.nonObject
  162    Cannot access property $end_datetime on Roster\Models\Schedule|null.
         🪪  property.nonObject
  165    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  165    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::hasOverlappingSchedule() expects int, mixed given.
         🪪  argument.type
  169    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  169    Parameter #1 $availabilityId of method Roster\Contracts\Repository\ImpedimentRepositoryInterface::hasOverlappingImpediments() expects int, mixed given.
         🪪  argument.type
  173    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  173    Cannot access property $availability_id on Roster\Models\Schedule|null.
         🪪  property.nonObject
  174    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  235    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  268    Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  268    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  269    Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  269    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  296    Method Roster\Services\ScheduleService::between() return type with generic class Illuminate\Support\Collection does not specify its types: TKey, TValue
         🪪  missingType.generics
  302    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  302    Parameter #1 $schedulableId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::getForDateRange() expects int, mixed given.
         🪪  argument.type
  303    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  303    Parameter #2 $schedulableType of method Roster\Contracts\Repository\ScheduleRepositoryInterface::getForDateRange() expects string, class-string<Illuminate\Database\Eloquent\
         Model>|false given.
         🪪  argument.type
  316    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlotWithPartialOverlaps() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  323    Access to an undefined property Roster\Models\Availability::$has_overlapping_schedules.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  324    Access to an undefined property Roster\Models\Availability::$has_overlapping_impediments.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  332    Parameter #1 $model of method Roster\Contracts\Services\SlotFinderInterface::isPeriodAvailable() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  335    Method Roster\Services\ScheduleService::findFirstAvailablePeriod() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  352    Parameter #1 $model of method Roster\Contracts\Services\SlotFinderInterface::findFirstAvailablePeriod() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  360    Method Roster\Services\ScheduleService::findNextAvailableSlot() return type has no value type specified in iterable type array.
         🪪  missingType.iterableValue
         💡  See: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
  371    Method Roster\Services\ScheduleService::findNextAvailableSlot() should return array|null but returns array|Illuminate\Support\Carbon|null.
         🪪  return.type
  371    Parameter #1 $model of method Roster\Contracts\Services\SlotFinderInterface::findNextSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  387    Parameter #1 $model of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects Illuminate\Database\Eloquent\Model,
         Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  387    Parameter #4 $type of method Roster\Contracts\Repository\AvailabilityRepositoryInterface::findForTimeSlot() expects string|null, mixed given.
         🪪  argument.type
  390    Method Roster\Services\ScheduleService::applyFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  393    Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  393    Parameter #1 $schedulableId of method Roster\Contracts\Repository\ScheduleRepositoryInterface::applyFilters() expects int, mixed given.
         🪪  argument.type
  394    Parameter #1 $object of function get_class expects object, Illuminate\Database\Eloquent\Model|null given.
         🪪  argument.type
  394    Parameter #2 $schedulableType of method Roster\Contracts\Repository\ScheduleRepositoryInterface::applyFilters() expects string, class-string<Illuminate\Database\Eloquent\Mod
         el>|false given.
         🪪  argument.type
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------
  Line   src/Traits/DateRangeOverlapTrait.php (in context of class Roster\Repositories\AvailabilityRepository)
 ------ -------------------------------------------------------------------------------------------------------
  33     Cannot call method lte() on Illuminate\Support\Carbon|null.
         🪪  method.nonObject
  34     Cannot call method gte() on Illuminate\Support\Carbon|null.
         🪪  method.nonObject
 ------ -------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------
  Line   src/Traits/DateRangeOverlapTrait.php (in context of class Roster\Services\Core\AvailabilityValidator)
 ------ -------------------------------------------------------------------------------------------------------
  33     Cannot call method lte() on Illuminate\Support\Carbon|null.
         🪪  method.nonObject
  34     Cannot call method gte() on Illuminate\Support\Carbon|null.
         🪪  method.nonObject
 ------ -------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/FilterableTrait.php (in context of class Roster\Services\AvailabilityService)
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  21     Method Roster\Services\AvailabilityService::applyDateFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  21     Method Roster\Services\AvailabilityService::applyDateFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  40     Method Roster\Services\AvailabilityService::applyTypeFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  40     Method Roster\Services\AvailabilityService::applyTypeFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  60     Method Roster\Services\AvailabilityService::applyDayFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  60     Method Roster\Services\AvailabilityService::applyDayFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  72     Method Roster\Services\AvailabilityService::applyStatusFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  72     Method Roster\Services\AvailabilityService::applyStatusFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  84     Method Roster\Services\AvailabilityService::applyReasonFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  84     Method Roster\Services\AvailabilityService::applyReasonFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  87     Binary operation "." between '%' and mixed results in an error.
         🪪  binaryOp.invalid
  96     Method Roster\Services\AvailabilityService::applyAvailabilityIdFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify
         its types: TModel
         🪪  missingType.generics
  96     Method Roster\Services\AvailabilityService::applyAvailabilityIdFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types:
         TModel
         🪪  missingType.generics
  108    Method Roster\Services\AvailabilityService::applySchedulableFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  108    Method Roster\Services\AvailabilityService::applySchedulableFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  111    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/FilterableTrait.php (in context of class Roster\Services\ImpedimentService)
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  60     Method Roster\Services\ImpedimentService::applyDayFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  60     Method Roster\Services\ImpedimentService::applyDayFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  72     Method Roster\Services\ImpedimentService::applyStatusFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  72     Method Roster\Services\ImpedimentService::applyStatusFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  84     Method Roster\Services\ImpedimentService::applyReasonFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  84     Method Roster\Services\ImpedimentService::applyReasonFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  87     Binary operation "." between '%' and mixed results in an error.
         🪪  binaryOp.invalid
  96     Method Roster\Services\ImpedimentService::applyAvailabilityIdFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  96     Method Roster\Services\ImpedimentService::applyAvailabilityIdFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  108    Method Roster\Services\ImpedimentService::applySchedulableFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  108    Method Roster\Services\ImpedimentService::applySchedulableFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  111    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   src/Traits/FilterableTrait.php (in context of class Roster\Services\ScheduleService)
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  21     Method Roster\Services\ScheduleService::applyDateFilters() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  21     Method Roster\Services\ScheduleService::applyDateFilters() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  40     Method Roster\Services\ScheduleService::applyTypeFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  40     Method Roster\Services\ScheduleService::applyTypeFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  60     Method Roster\Services\ScheduleService::applyDayFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  60     Method Roster\Services\ScheduleService::applyDayFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  72     Method Roster\Services\ScheduleService::applyStatusFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  72     Method Roster\Services\ScheduleService::applyStatusFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  84     Method Roster\Services\ScheduleService::applyReasonFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its types:
         TModel
         🪪  missingType.generics
  84     Method Roster\Services\ScheduleService::applyReasonFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  87     Binary operation "." between '%' and mixed results in an error.
         🪪  binaryOp.invalid
  96     Method Roster\Services\ScheduleService::applyAvailabilityIdFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  96     Method Roster\Services\ScheduleService::applyAvailabilityIdFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  108    Method Roster\Services\ScheduleService::applySchedulableFilter() has parameter $builder with generic class Illuminate\Database\Eloquent\Builder but does not specify its
         types: TModel
         🪪  missingType.generics
  108    Method Roster\Services\ScheduleService::applySchedulableFilter() return type with generic class Illuminate\Database\Eloquent\Builder does not specify its types: TModel
         🪪  missingType.generics
  111    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------
  Line   src/Traits/HasRoster.php (in context of class Roster\Tests\Unit\Services\TestSchedulable)
 ------ ---------------------------------------------------------------------------------------------------
  15     Method Roster\Tests\Unit\Services\TestSchedulable::schedules() has no return type specified.
         🪪  missingType.return
  23     Method Roster\Tests\Unit\Services\TestSchedulable::availabilities() has no return type specified.
         🪪  missingType.return
 ------ ---------------------------------------------------------------------------------------------------

 ------ ------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Feature/Facades/AvailabilityFacadeTest.php
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------
  31     Call to an undefined static method Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Facades/AvailabilityFacadeTest.php:23::create().
         🪪  staticMethod.notFound
  31     Property Tests\Feature\Facades\AvailabilityFacadeTest::$model (Illuminate\Database\Eloquent\Model) does not accept mixed.
         🪪  assign.propertyType
  39     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  39     Cannot access property $id on Illuminate\Database\Eloquent\Model|null.
         🪪  property.nonObject
  54     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  56     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  63     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  64     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  72     Cannot access property $id on mixed.
         🪪  property.nonObject
  72     Parameter #1 $id of method Roster\Services\AvailabilityService::find() expects int, mixed given.
         🪪  argument.type
  75     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  75     Cannot access property $id on mixed.
         🪪  property.nonObject
  80     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  81     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  89     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  90     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  102    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  103    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  108    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  109    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  117    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  118    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  131    Cannot access property $type on mixed.
         🪪  property.nonObject
  136    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  137    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ ------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Feature/Facades/ImpedimentFacadeTest.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------
  37     Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Facades/ImpedimentFacadeTest.php:31::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  40     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  40     Property Tests\Feature\Facades\ImpedimentFacadeTest::$availability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  51     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  51     Property Tests\Feature\Facades\ImpedimentFacadeTest::$julyAvailability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  75     Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  76     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  76     Access to an undefined property Roster\Models\Impediment::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  91     Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  92     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  92     Access to an undefined property Roster\Models\Impediment::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  119    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  119    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  122    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  122    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  123    Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  145    Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  146    Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  173    Cannot access property $reason on mixed.
         🪪  property.nonObject
  221    Cannot access property $reason on mixed.
         🪪  property.nonObject
  234    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  234    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  238    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  238    Parameter #1 $id of method Roster\Services\ImpedimentService::delete() expects int, mixed given.
         🪪  argument.type
  242    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  242    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  257    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  257    Parameter #1 $id of method Roster\Services\Core\AbstractEntityScopingService::update() expects int, mixed given.
         🪪  argument.type
  266    Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  267    Access to an undefined property Roster\Models\Impediment::$metadata.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ --------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Feature/Facades/ScheduleFacadeTest.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------
  33     Call to an undefined static method Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Facades/ScheduleFacadeTest.php:28::create().
         🪪  staticMethod.notFound
  33     Property Tests\Feature\Facades\ScheduleFacadeTest::$model (Illuminate\Database\Eloquent\Model) does not accept mixed.
         🪪  assign.propertyType
  39     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  39     Property Tests\Feature\Facades\ScheduleFacadeTest::$availability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  40     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  49     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  49     Property Tests\Feature\Facades\ScheduleFacadeTest::$trainingAvailability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  50     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  87     Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  89     Access to an undefined property Roster\Models\Schedule::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  104    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  105    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  105    Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  106    Access to an undefined property Roster\Models\Schedule::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  134    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  134    Parameter #1 $id of method Roster\Services\ScheduleService::find() expects int, mixed given.
         🪪  argument.type
  137    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  137    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  138    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  162    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  163    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  188    Cannot access property $title on mixed.
         🪪  property.nonObject
  213    Cannot access property $title on mixed.
         🪪  property.nonObject
  257    Cannot call method format() on mixed.
         🪪  method.nonObject
  258    Cannot call method format() on mixed.
         🪪  method.nonObject
  284    Cannot access property $title on mixed.
         🪪  property.nonObject
  298    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  298    Parameter #1 $id of method Roster\Services\ScheduleService::find() expects int, mixed given.
         🪪  argument.type
  302    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  302    Parameter #1 $id of method Roster\Services\ScheduleService::delete() expects int, mixed given.
         🪪  argument.type
  306    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  306    Parameter #1 $id of method Roster\Services\ScheduleService::find() expects int, mixed given.
         🪪  argument.type
  322    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  322    Parameter #1 $id of method Roster\Services\Core\AbstractEntityScopingService::update() expects int, mixed given.
         🪪  argument.type
  331    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  332    Access to an undefined property Roster\Models\Schedule::$description.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ --------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Feature/Services/AvailabilityServiceTest.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------
  29     Call to an undefined static method Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Services/AvailabilityServiceTest.php:24::create().
         🪪  staticMethod.notFound
  29     Property Tests\Feature\Services\AvailabilityServiceTest::$model (Illuminate\Database\Eloquent\Model) does not accept mixed.
         🪪  assign.propertyType
  48     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  48     Access to an undefined property Roster\Models\Availability::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  49     Access to an undefined property Roster\Models\Availability::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  50     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  51     Access to an undefined property Roster\Models\Availability::$days.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  53     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  71     Access to an undefined property Roster\Models\Availability::$start_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  71     Cannot call method format() on mixed.
         🪪  method.nonObject
  72     Access to an undefined property Roster\Models\Availability::$end_date.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  72     Cannot call method format() on mixed.
         🪪  method.nonObject
  97     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  98     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  106    Cannot access property $id on mixed.
         🪪  property.nonObject
  106    Parameter #1 $id of method Roster\Services\Core\AbstractEntityScopingService::update() expects int, mixed given.
         🪪  argument.type
  113    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  114    Cannot access property $type on mixed.
         🪪  property.nonObject
  115    Cannot access property $end_time on mixed.
         🪪  property.nonObject
  115    Cannot call method format() on mixed.
         🪪  method.nonObject
  120    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  121    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  129    Cannot access property $id on mixed.
         🪪  property.nonObject
  129    Parameter #1 $id of method Roster\Services\AvailabilityService::delete() expects int, mixed given.
         🪪  argument.type
  132    Cannot access property $id on mixed.
         🪪  property.nonObject
  137    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  138    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  146    Cannot access property $id on mixed.
         🪪  property.nonObject
  146    Parameter #1 $id of method Roster\Services\AvailabilityService::find() expects int, mixed given.
         🪪  argument.type
  149    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  149    Cannot access property $id on mixed.
         🪪  property.nonObject
  189    Call to method PHPUnit\Framework\Assert::assertIsArray() with array will always evaluate to true.
         🪪  method.alreadyNarrowedType
  192    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  192    Cannot call method format() on mixed.
         🪪  method.nonObject
  193    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  193    Cannot call method format() on mixed.
         🪪  method.nonObject
  194    Cannot access offset 'type' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Feature/Services/ImpedimentServiceTest.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------
  41     Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Services/ImpedimentServiceTest.php:35::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  45     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  45     Property Tests\Feature\Services\ImpedimentServiceTest::$availability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  57     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  57     Property Tests\Feature\Services\ImpedimentServiceTest::$julyAvailability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  85     Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  86     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  86     Access to an undefined property Roster\Models\Impediment::$schedulable_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  87     Access to an undefined property Roster\Models\Impediment::$schedulable_type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Access to an undefined property Roster\Models\Impediment::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  91     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  104    Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Services/ImpedimentServiceTest.php:98::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  107    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  128    Parameter #1 $availabilityOrData of method Roster\Services\ImpedimentService::create() expects array<string, mixed>|Roster\Models\Availability, mixed given.
         🪪  argument.type
  160    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  160    Parameter #1 $id of method Roster\Services\Core\AbstractEntityScopingService::update() expects int, mixed given.
         🪪  argument.type
  168    Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  169    Access to an undefined property Roster\Models\Impediment::$metadata.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  181    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  181    Parameter #1 $id of method Roster\Services\Core\AbstractEntityScopingService::update() expects int, mixed given.
         🪪  argument.type
  189    Access to an undefined property Roster\Models\Impediment::$start_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  189    Cannot call method format() on mixed.
         🪪  method.nonObject
  190    Access to an undefined property Roster\Models\Impediment::$end_datetime.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  190    Cannot call method format() on mixed.
         🪪  method.nonObject
  202    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  202    Parameter #1 $id of method Roster\Services\ImpedimentService::delete() expects int, mixed given.
         🪪  argument.type
  205    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  217    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  217    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  220    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  220    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  221    Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  264    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  264    Cannot call method format() on mixed.
         🪪  method.nonObject
  265    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  265    Cannot call method format() on mixed.
         🪪  method.nonObject
  268    Cannot access offset 'start' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  268    Cannot call method format() on mixed.
         🪪  method.nonObject
  269    Cannot access offset 'end' on mixed.
         🪪  offsetAccess.nonOffsetAccessible
  269    Cannot call method format() on mixed.
         🪪  method.nonObject
  297    PHPDoc tag @var for variable $impediments contains generic class Illuminate\Support\Collection but does not specify its types: TKey, TValue
         🪪  missingType.generics
  300    Cannot access property $reason on mixed.
         🪪  property.nonObject
  301    Cannot access property $reason on mixed.
         🪪  property.nonObject
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Feature/Services/ScheduleServiceTest.php
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------
  40     Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Services/ScheduleServiceTest.php:34::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  44     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  44     Property Tests\Feature\Services\ScheduleServiceTest::$availability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  53     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  53     Property Tests\Feature\Services\ScheduleServiceTest::$trainingAvailability (Roster\Models\Availability) does not accept mixed.
         🪪  assign.propertyType
  82     Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  83     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  83     Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  86     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  104    Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  105    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  128    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  128    Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  129    Access to an undefined property Roster\Models\Schedule::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  140    Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Feature/Services/ScheduleServiceTest.php:134::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  185    Parameter #1 $availabilityOrData of method Roster\Services\ScheduleService::create() expects array<string, mixed>|Roster\Models\Availability, null given.
         🪪  argument.type
  216    Call to function method_exists() with $this(Tests\Feature\Services\ScheduleServiceTest) and 'expectException' will always evaluate to true.
         🪪  function.alreadyNarrowedType
  219    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  222    Cannot access property $availability_id on mixed.
         🪪  property.nonObject
  254    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  255    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  258    Access to an undefined property Roster\Models\Schedule::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  271    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  272    Access to an undefined property Roster\Models\Schedule::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  275    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  287    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  288    Access to an undefined property Roster\Models\Schedule::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  296    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  297    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  304    Cannot access property $id on mixed.
         🪪  property.nonObject
  304    Parameter #1 $id of method Roster\Services\Core\AbstractEntityScopingService::update() expects int, mixed given.
         🪪  argument.type
  311    Cannot call method refresh() on mixed.
         🪪  method.nonObject
  312    Cannot access property $title on mixed.
         🪪  property.nonObject
  313    Cannot access property $description on mixed.
         🪪  property.nonObject
  318    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  319    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  326    Cannot access property $id on mixed.
         🪪  property.nonObject
  326    Parameter #1 $id of method Roster\Services\ScheduleService::delete() expects int, mixed given.
         🪪  argument.type
  329    Cannot access property $id on mixed.
         🪪  property.nonObject
  334    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  335    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  342    Cannot access property $id on mixed.
         🪪  property.nonObject
  342    Parameter #1 $id of method Roster\Services\ScheduleService::find() expects int, mixed given.
         🪪  argument.type
  345    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  345    Cannot access property $id on mixed.
         🪪  property.nonObject
  346    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  352    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  353    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  380    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  381    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  393    Cannot call method format() on mixed.
         🪪  method.nonObject
  394    Cannot call method format() on mixed.
         🪪  method.nonObject
  401    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  402    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  409    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  410    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  417    Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  418    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  429    PHPDoc tag @var for variable $schedules contains generic class Illuminate\Support\Collection but does not specify its types: TKey, TValue
         🪪  missingType.generics
  432    Cannot access property $title on mixed.
         🪪  property.nonObject
  433    Cannot access property $title on mixed.
         🪪  property.nonObject
 ------ -----------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Integration/ModelIntegrationTest.php
 ------ ----------------------------------------------------------------------------------------------------------------------------------
  31     Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Integration/ModelIntegrationTest.php:25::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  37     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  38     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  46     Cannot access property $schedulable on mixed.
         🪪  property.nonObject
  47     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  47     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  47     Cannot access property $schedulable on mixed.
         🪪  property.nonObject
  49     Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  50     Cannot access property $id on mixed.
         🪪  property.nonObject
  57     Cannot access property $schedules on mixed.
         🪪  property.nonObject
  57     Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertCount() expects Countable|iterable, mixed given.
         🪪  argument.type
  58     Cannot access property $id on mixed.
         🪪  property.nonObject
  58     Cannot access property $id on mixed.
         🪪  property.nonObject
  58     Cannot access property $schedules on mixed.
         🪪  property.nonObject
  58     Cannot call method first() on mixed.
         🪪  method.nonObject
  60     Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  61     Cannot access property $id on mixed.
         🪪  property.nonObject
  62     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  69     Cannot access property $impediments on mixed.
         🪪  property.nonObject
  69     Parameter #2 $haystack of method PHPUnit\Framework\Assert::assertCount() expects Countable|iterable, mixed given.
         🪪  argument.type
  70     Cannot access property $id on mixed.
         🪪  property.nonObject
  70     Cannot access property $id on mixed.
         🪪  property.nonObject
  70     Cannot access property $impediments on mixed.
         🪪  property.nonObject
  70     Cannot call method first() on mixed.
         🪪  method.nonObject
  75     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  76     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  84     Call to an undefined static method Roster\Models\Schedule::create().
         🪪  staticMethod.notFound
  85     Cannot access property $id on mixed.
         🪪  property.nonObject
  92     Cannot access property $availability on mixed.
         🪪  property.nonObject
  93     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  93     Cannot access property $availability on mixed.
         🪪  property.nonObject
  93     Cannot access property $id on mixed.
         🪪  property.nonObject
  95     Cannot access property $type on mixed.
         🪪  property.nonObject
  97     Cannot call method schedulable() on mixed.
         🪪  method.nonObject
  103    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  104    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  112    Call to an undefined static method Roster\Models\Impediment::create().
         🪪  staticMethod.notFound
  113    Cannot access property $id on mixed.
         🪪  property.nonObject
  114    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  121    Cannot access property $availability on mixed.
         🪪  property.nonObject
  122    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  122    Cannot access property $availability on mixed.
         🪪  property.nonObject
  122    Cannot access property $id on mixed.
         🪪  property.nonObject
  124    Cannot access property $schedulable on mixed.
         🪪  property.nonObject
  125    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  125    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  125    Cannot access property $schedulable on mixed.
         🪪  property.nonObject
 ------ ----------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Integration/RepositoryIntegrationTest.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------
  33     Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Integration/RepositoryIntegrationTest.php:27::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  42     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  43     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  57     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  57     Cannot access property $id on mixed.
         🪪  property.nonObject
  60     Cannot access property $id on Roster\Models\Availability|null.
         🪪  property.nonObject
  60     Cannot access property $id on mixed.
         🪪  property.nonObject
  68     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  69     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  77     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  78     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  86     Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  87     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  100    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  101    Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  106    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  107    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  127    Call to an undefined static method Roster\Models\Availability::create().
         🪪  staticMethod.notFound
  128    Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  145    Cannot access property $type on Roster\Models\Availability|null.
         🪪  property.nonObject
 ------ ---------------------------------------------------------------------------------------------------------------------------------------

 ------ -------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Integration/ServiceIntegrationTest.php
 ------ -------------------------------------------------------------------------------------------------------------------------------------
  41     Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Integration/ServiceIntegrationTest.php:35::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  66     Access to an undefined property Roster\Models\Availability::$type.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  77     Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  78     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  78     Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  88     Access to an undefined property Roster\Models\Impediment::$reason.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  89     Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  89     Access to an undefined property Roster\Models\Impediment::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  202    Cannot access property $start_time on mixed.
         🪪  property.nonObject
  202    Cannot call method format() on mixed.
         🪪  method.nonObject
  203    Cannot access property $end_time on mixed.
         🪪  property.nonObject
  203    Cannot call method format() on mixed.
         🪪  method.nonObject
  242    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  242    Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  243    Access to an undefined property Roster\Models\Availability::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  243    Access to an undefined property Roster\Models\Schedule::$availability_id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  298    Access to an undefined property Illuminate\Database\Eloquent\Model@anonymous/tests/Integration/ServiceIntegrationTest.php:292::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  344    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  344    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  345    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  345    Cannot access property $id on Roster\Models\Impediment|null.
         🪪  property.nonObject
  348    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  348    Parameter #1 $id of method Roster\Services\ImpedimentService::delete() expects int, mixed given.
         🪪  argument.type
  352    Access to an undefined property Roster\Models\Impediment::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  352    Parameter #1 $id of method Roster\Services\ImpedimentService::find() expects int, mixed given.
         🪪  argument.type
  378    Access to an undefined property Roster\Models\Schedule::$id.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  378    Parameter #1 $id of method Roster\Services\Core\AbstractEntityScopingService::update() expects int, mixed given.
         🪪  argument.type
  387    Access to an undefined property Roster\Models\Schedule::$title.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
  388    Access to an undefined property Roster\Models\Schedule::$description.
         🪪  property.notFound
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 ------ -------------------------------------------------------------------------------------------------------------------------------------

 ------ ------------------------------------
  Line   tests/TestCase.php
 ------ ------------------------------------
  35     Cannot call method set() on mixed.
         🪪  method.nonObject
  36     Cannot call method set() on mixed.
         🪪  method.nonObject
 ------ ------------------------------------

 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Commands/InstallRosterCommandTest.php
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
  66     Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  72     PHPDoc tag @var with type Symfony\Component\Console\Output\Output&Tests\Unit\Commands\OutputWithBuffer is not subtype of native type
         Symfony\Component\Console\Output\Output@anonymous/tests/Unit/Commands/InstallRosterCommandTest.php:72.
         🪪  varTag.nativeType
  90     Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  96     PHPDoc tag @var with type Symfony\Component\Console\Output\Output&Tests\Unit\Commands\OutputWithBuffer is not subtype of native type
         Symfony\Component\Console\Output\Output@anonymous/tests/Unit/Commands/InstallRosterCommandTest.php:96.
         🪪  varTag.nativeType
  115    Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  150    Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  153    PHPDoc tag @var with type Symfony\Component\Console\Output\Output&Tests\Unit\Commands\OutputWithBuffer is not subtype of native type
         Symfony\Component\Console\Output\Output@anonymous/tests/Unit/Commands/InstallRosterCommandTest.php:153.
         🪪  varTag.nativeType
  173    Cannot call method expectsOutput() on Illuminate\Testing\PendingCommand|int.
         🪪  method.nonObject
  195    Parameter #1 $laravel of method Illuminate\Console\Command::setLaravel() expects Illuminate\Contracts\Container\Container, Illuminate\Foundation\Application|null given.
         🪪  argument.type
  204    PHPDoc tag @var with type Symfony\Component\Console\Output\Output&Tests\Unit\Commands\OutputWithBuffer is not subtype of native type
         Symfony\Component\Console\Output\Output@anonymous/tests/Unit/Commands/InstallRosterCommandTest.php:204.
         🪪  varTag.nativeType
 ------ --------------------------------------------------------------------------------------------------------------------------------------------------------------------------

 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------
  Line   tests/Unit/Services/AvailabilityValidatorTest.php
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------
  188    Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
  188    Left side of && is always true.
         🪪  booleanAnd.leftAlwaysTrue
  188    Right side of && is always true.
         🪪  booleanAnd.rightAlwaysTrue
  221    Call to method PHPUnit\Framework\Assert::assertFalse() with false will always evaluate to false.
         🪪  method.impossibleType
  221    Left side of && is always false.
         🪪  booleanAnd.leftAlwaysFalse
  221    Right side of && is always true.
         🪪  booleanAnd.rightAlwaysTrue
  254    Call to method PHPUnit\Framework\Assert::assertFalse() with false will always evaluate to false.
         🪪  method.impossibleType
  254    Left side of && is always true.
         🪪  booleanAnd.leftAlwaysTrue
  254    Right side of && is always false.
         🪪  booleanAnd.rightAlwaysFalse
  281    Result of && is always true.
         🪪  booleanAnd.alwaysTrue
  281    Strict comparison using === between 1 and 1 will always evaluate to true.
         🪪  identical.alwaysTrue
  282    Strict comparison using === between 'TestModel' and 'TestModel' will always evaluate to true.
         🪪  identical.alwaysTrue
  283    Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
  288    Strict comparison using === between 'consultation' and 'consultation' will always evaluate to true.
         🪪  identical.alwaysTrue
  289    Call to method PHPUnit\Framework\Assert::assertTrue() with true will always evaluate to true.
         🪪  method.alreadyNarrowedType
  326    Result of && is always false.
         🪪  booleanAnd.alwaysFalse
  326    Strict comparison using === between 1 and 2 will always evaluate to false.
         🪪  identical.alwaysFalse
  328    Call to method PHPUnit\Framework\Assert::assertFalse() with false will always evaluate to false.
         🪪  method.impossibleType
  351    Strict comparison using === between 'consultation' and 'training' will always evaluate to false.
         🪪  identical.alwaysFalse
  352    Call to method PHPUnit\Framework\Assert::assertFalse() with false will always evaluate to false.
         🪪  method.impossibleType
  405    Instanceof between Illuminate\Support\Carbon and Illuminate\Support\Carbon will always evaluate to true.
         🪪  instanceof.alwaysTrue
  405    Instanceof between Illuminate\Support\Carbon and Illuminate\Support\Carbon will always evaluate to true.
         🪪  instanceof.alwaysTrue
  405    Result of || is always true.
         🪪  booleanOr.alwaysTrue
  406    Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
  407    Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
  411    Instanceof between Illuminate\Support\Carbon and Illuminate\Support\Carbon will always evaluate to true.
         🪪  instanceof.alwaysTrue
  411    Instanceof between Illuminate\Support\Carbon and Illuminate\Support\Carbon will always evaluate to true.
         🪪  instanceof.alwaysTrue
  411    Result of || is always true.
         🪪  booleanOr.alwaysTrue
  412    Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
  413    Ternary operator condition is always true.
         🪪  ternary.alwaysTrue
  422    Using nullsafe method call on non-nullable type Illuminate\Support\Carbon. Use -> instead.
         🪪  nullsafe.neverNull
  423    Using nullsafe method call on non-nullable type Illuminate\Support\Carbon. Use -> instead.
         🪪  nullsafe.neverNull
  436    Call to function method_exists() with Roster\Contracts\Services\AvailabilityValidatorInterface and 'hasOverlapping' will always evaluate to true.
         🪪  function.alreadyNarrowedType
 ------ ---------------------------------------------------------------------------------------------------------------------------------------------------

 [ERROR] Found 1018 errors

