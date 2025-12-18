<?php

declare(strict_types=1);

// ==== src/Facades/Schedule.php ====

namespace Roster\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Roster\Services\ScheduleService;

/**
 * @method static ScheduleService for(Model $schedulable)
 * @method static \Roster\Models\Schedule create(array $data)
 * @method static bool update(int $id, array $data)
 * @method static bool delete(int $id)
 * @method static \Roster\Models\Schedule|null find(int $id)
 * @method static Collection all()
 * @method static Collection get()
 * @method static ScheduleService whereType(string $type)
 * @method static ScheduleService whereStartDate(Carbon $date)
 * @method static ScheduleService whereEndDate(Carbon $date)
 * @method static ScheduleService whereStatus(string $status)
 * @method static Collection between(Carbon $start, Carbon $end)
 * @method static bool isTimeSlotAvailable(Carbon $start, Carbon $end, ?string $type = null)
 * @method static array|null findNextAvailableSlot(int $durationMinutes, ?string $type = null)
 * @method static ScheduleService resetFilters()
 * @method static Model|null getSchedulable()
 */
class Schedule extends Facade
{
    /**
     * Retourne l'alias du container pour le service
     */
    protected static function getFacadeAccessor(): string
    {
        return 'roster.schedule';
    }
}
