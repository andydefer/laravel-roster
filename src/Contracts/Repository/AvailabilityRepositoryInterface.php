<?php

declare(strict_types=1);

namespace Roster\Contracts\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Roster\Models\Availability;

interface AvailabilityRepositoryInterface
{
    public function findForSchedulable(Model $model, ?string $type = null): Builder;

    public function getForDateRange(Model $model, Carbon $start, Carbon $end, ?string $type = null): Collection;

    public function getAvailabilityForTimeSlot(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;

    public function getForDate(Model $model, Carbon $date, ?string $type = null): Collection;

    public function isAvailableOnDate(Availability $availability, Carbon $date): bool;

    public function findForTimeSlotWithConflictInfo(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
}
