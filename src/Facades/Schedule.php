<?php

declare(strict_types=1);

namespace Roster\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Services\ScheduleService;

/**
 * Facade providing static access to schedule management functionality.
 *
 * Offers a fluent interface for scheduling time slots within availability periods.
 * All operations are scoped to a specific schedulable entity.
 *
 * @method static ScheduleService for(Model $schedulable) Scope operations to a specific schedulable entity
 * @method static \Roster\Models\Schedule create(AvailabilityModel $availability, array $data) Create a new scheduled time slot
 * @method static bool update(int $id, array $data) Update an existing schedule
 * @method static bool delete(int $id) Delete a schedule by ID
 * @method static \Roster\Models\Schedule|null find(int $id) Retrieve a schedule by ID
 * @method static Collection<int, \Roster\Models\Schedule> all() Get all schedules for current schedulable
 * @method static Collection<int, \Roster\Models\Schedule> get() Get filtered schedules for current schedulable
 * @method static ScheduleService whereType(string $type) Filter by availability type
 * @method static Collection<int, \Roster\Models\Schedule> between(Carbon $start, Carbon $end) Get schedules within a date range
 * @method static bool isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null) Check if a time slot is available for scheduling
 * @method static bool isPeriodAvailable(Carbon $start, Carbon $end, ?string $type = null) Check if an entire time period is available
 * @method static array|null findFirstAvailablePeriod(Carbon $startDate, Carbon $endDate, int $durationMinutes, ?string $type = null) Find first available continuous period
 * @method static array|null findNextAvailableSlot(int $durationMinutes, ?string $type = null) Find the next available time slot
 * @method static ScheduleService resetFilters() Clear all applied filters
 * @method static Model|null getSchedulable() Get the current schedulable entity
 *
 * @see \Roster\Services\ScheduleService
 */
class Schedule extends Facade
{
    /**
     * Get the service container binding for the facade.
     *
     * @return string The service container binding identifier
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.schedule';
    }
}
