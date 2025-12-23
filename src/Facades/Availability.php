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
 * Facade providing static access to availability management functionality.
 *
 * Offers a fluent interface for managing recurring availability rules.
 * All operations are scoped to a specific schedulable entity.
 *
 * @method static AvailabilityService for(Model $schedulable) Scope operations to a specific schedulable entity
 * @method static AvailabilityModel create(array $data) Create a new availability rule
 * @method static bool update(int $id, array $data) Update an existing availability
 * @method static bool delete(int $id) Delete an availability by ID
 * @method static AvailabilityModel|null find(int $id) Retrieve an availability by ID
 * @method static Collection<int, AvailabilityModel> all() Get all availabilities for current schedulable
 * @method static Collection<int, AvailabilityModel> get() Get filtered availabilities for current schedulable
 * @method static AvailabilityService whereType(string $type) Filter by availability type
 * @method static AvailabilityService filterByDay(string $day) Filter by day of week
 * @method static bool isAvailableAt(Carbon $datetime) Check availability at a specific datetime
 * @method static bool isAvailableForPeriod(Carbon $start, Carbon $end, ?string $type = null) Check availability for a time period
 * @method static bool hasOverlapping(array $data, ?int $exceptId = null) Check if availability overlaps with existing ones
 * @method static Collection<int, AvailabilityModel> findOverlapping(array $data, ?int $exceptId = null) Find overlapping availabilities
 * @method static Collection<int, AvailabilityModel> findByType(array $data) Find availabilities adjacent to given data
 * @method static AvailabilityService resetFilters() Clear all applied filters
 * @method static Model|null getSchedulable() Get the current schedulable entity
 *
 * @see \Roster\Services\AvailabilityService
 */
class Availability extends Facade
{
    /**
     * Get the service container binding for the facade.
     *
     * @return string The service container binding identifier
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.availability';
    }
}
