<?php

declare(strict_types=1);

namespace Roster\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Models\Impediment as ImpedimentModel;
use Roster\Services\ImpedimentService;

/**
 * Facade providing static access to impediment management functionality.
 *
 * Offers a fluent interface for managing time blocks (impediments) within availabilities.
 * All operations are scoped to a specific schedulable entity.
 *
 * @method static ImpedimentService for(Model $schedulable) Scope operations to a specific schedulable entity
 * @method static ImpedimentModel create(AvailabilityModel $availability, array $data) Create a new impediment
 * @method static bool update(int $id, array $data) Update an existing impediment
 * @method static bool delete(int $id) Delete an impediment by ID
 * @method static ImpedimentModel|null find(int $id) Retrieve an impediment by ID
 * @method static Collection<int, ImpedimentModel> all() Get all impediments for current schedulable
 * @method static Collection<int, ImpedimentModel> get() Get filtered impediments for current schedulable
 * @method static ImpedimentService whereType(string $type) Filter by availability type
 * @method static Collection<int, ImpedimentModel> between(Carbon $start, Carbon $end) Get impediments within a date range
 * @method static bool isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null) Check if a time slot has impediments
 * @method static Collection<int, array<string, mixed>> getAvailableTimeSlots(Carbon $start, Carbon $end, ?string $type = null) Get available slots considering impediments
 * @method static ImpedimentService resetFilters() Clear all applied filters
 * @method static Model|null getSchedulable() Get the current schedulable entity
 *
 * @see \Roster\Services\ImpedimentService
 */
class Impediment extends Facade
{
    /**
     * Get the service container binding for the facade.
     *
     * @return string The service container binding identifier
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.impediment';
    }
}
