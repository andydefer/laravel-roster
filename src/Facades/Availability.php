<?php

namespace Roster\Facades;

use Illuminate\Support\Facades\Facade;
use Roster\Services\AvailabilityService;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static AvailabilityService for(Model $schedulable)
 * @method static \Roster\Models\Availability create(array $data)
 * @method static bool update(int $id, array $data)
 * @method static bool delete(int $id)
 * @method static \Roster\Models\Availability|null find(int $id)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Illuminate\Support\Collection get()
 * @method static AvailabilityService whereType(string $type)
 * @method static AvailabilityService whereDay(string $day)
 * @method static bool isAvailableAt(\Carbon\Carbon $datetime)
 * @method static \Carbon\Carbon|null nextAvailableSlot(\Carbon\Carbon $fromDate, int $durationMinutes = 60)
 * @method static array availableSlots(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, int $durationMinutes = 60, int $intervalMinutes = 30)
 * @method static AvailabilityService resetFilters()
 * @method static Model|null getSchedulable()
 */
class Availability extends Facade
{
    /**
     * Retourne l'alias du container pour le service
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.availability';
    }
}
