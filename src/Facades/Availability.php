<?php

declare(strict_types=1);

// ==== src/Facades/Availability.php ====

namespace Roster\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Roster\Models\Availability as AvailabilityModel;
use Roster\Services\AvailabilityService;

/**
 * @method static AvailabilityService for(Model $schedulable)
 * @method static AvailabilityModel create(array $data)
 * @method static bool update(int $id, array $data)
 * @method static bool delete(int $id)
 * @method static AvailabilityModel|null find(int $id)
 * @method static Collection<int, AvailabilityModel> all()
 * @method static Collection<int, AvailabilityModel> get()
 * @method static AvailabilityService whereType(string $type)
 * @method static AvailabilityService whereDay(string $day)
 * @method static bool isAvailableAt(Carbon $datetime)
 * @method static Carbon|null nextAvailableSlot(Carbon $fromDate, int $durationMinutes = 60)
 * @method static array<array{start: Carbon, end: Carbon, type: string, availability_id: int}> availableSlots(Carbon $startDate, Carbon $endDate, int $durationMinutes = 60, int $intervalMinutes = 30)
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
