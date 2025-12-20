<?php

declare(strict_types=1);

namespace Roster\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Roster\Models\Availability;
use Roster\Services\ImpedimentService;

/**
 * @method static ImpedimentService for(Model $schedulable)
 * @method static \Roster\Models\Impediment create(Availability $availability, array $data) Create a new impediment with explicit availability
 * @method static bool update(int $id, array $data)
 * @method static bool delete(int $id)
 * @method static \Roster\Models\Impediment|null find(int $id)
 * @method static Collection all()
 * @method static Collection get()
 * @method static ImpedimentService whereType(string $type)
 * @method static ImpedimentService whereStartDate(Carbon $date)
 * @method static ImpedimentService whereEndDate(Carbon $date)
 * @method static Collection between(Carbon $start, Carbon $end)
 * @method static bool isTimeSlotBlocked(Carbon $start, Carbon $end, ?string $type = null)
 * @method static ImpedimentService resetFilters()
 * @method static Model|null getSchedulable()
 */
class Impediment extends Facade
{
    /**
     * Retourne l'alias du container pour le service
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.impediment';
    }
}
