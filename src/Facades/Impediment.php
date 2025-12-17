<?php
// ==== src/Facades/Impediment.php ====

namespace Roster\Facades;

use Illuminate\Support\Facades\Facade;
use Roster\Services\ImpedimentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

/**
 * @method static ImpedimentService for(Model $schedulable)
 * @method static \Roster\Models\Impediment create(array $data)
 * @method static bool update(int $id, array $data)
 * @method static bool delete(int $id)
 * @method static \Roster\Models\Impediment|null find(int $id)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Illuminate\Support\Collection get()
 * @method static ImpedimentService whereType(string $type)
 * @method static ImpedimentService whereStartDate(Carbon $date)
 * @method static ImpedimentService whereEndDate(Carbon $date)
 * @method static \Illuminate\Support\Collection between(Carbon $start, Carbon $end)
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
