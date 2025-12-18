<?php

declare(strict_types=1);

namespace Roster\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Services\AvailabilityService;

/**
 * Facade to access the AvailabilityService.
 *
 * This provides a simple static interface to manage
 * recurring availabilities for any schedulable model.
 *
 * @method static AvailabilityService for(Model $schedulable) Scope the service to a specific schedulable.
 * @method static AvailabilityModel create(array $data) Create a new availability with validation.
 * @method static bool update(int $id, array $data) Update an existing availability.
 * @method static bool delete(int $id) Delete an availability by ID.
 * @method static AvailabilityModel|null find(int $id) Find an availability by ID.
 * @method static Collection<int, AvailabilityModel> all() Get all availabilities for the current schedulable.
 * @method static Collection<int, AvailabilityModel> get() Get all availabilities with applied filters.
 * @method static AvailabilityService whereType(string $type) Filter by availability type.
 * @method static AvailabilityService whereDay(string $day) Filter by day of the week.
 * @method static bool isAvailableAt(Carbon $datetime) Check if the schedulable is available at a given datetime.
 * @method static Carbon|null nextAvailableSlot(Carbon $fromDate, int $durationMinutes = 60) Get the next available slot.
 * @method static array<array{start: Carbon, end: Carbon, type: string, availability_id: int}> availableSlots(
 *     Carbon $startDate,
 *     Carbon $endDate,
 *     int $durationMinutes = 60,
 *     int $intervalMinutes = 30
 * ) Get all available slots in a period.
 * @method static AvailabilityService resetFilters() Clear all applied filters.
 * @method static Model|null getSchedulable() Get the current schedulable model.
 */
class Availability extends Facade
{
    /**
     * Get the service container binding for the facade.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.availability';
    }
}
