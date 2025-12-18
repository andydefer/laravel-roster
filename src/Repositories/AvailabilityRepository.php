<?php

declare(strict_types=1);

namespace Roster\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Models\Availability;
use Roster\Services\Core\ValidationService;

class AvailabilityRepository
{
    protected ValidationService $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Find availability for a time slot.
     */
    public function findForTimeSlot(
        Model $model,
        Carbon $start,
        Carbon $end,
        ?string $type = null
    ): ?Availability {
        $this->validationService->validateTimeRange($start, $end);

        $query = Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->whereJsonContains('days', strtolower($start->englishDayOfWeek))
            ->where('start_time', '<=', $start->format('H:i:s'))
            ->where('end_time', '>=', $end->format('H:i:s'));

        if ($type) {
            $query->where('type', $type);
        }

        $query->where(function ($q) use ($start): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $start->toDateString());
        })->where(function ($q) use ($end): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $end->toDateString());
        });

        /** @var Availability|null $availability */
        $availability = $query->first();

        return $availability;
    }

    /**
     * Get availabilities for a specific date.
     *
     * @return Collection<int, Availability>
     */
    public function getForDate(
        Model $model,
        Carbon $date,
        ?string $type = null
    ): Collection {
        $query = Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->whereJsonContains('days', strtolower($date->englishDayOfWeek));

        if ($type) {
            $query->where('type', $type);
        }

        $query->where(function ($q) use ($date): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $date->toDateString());
        })->where(function ($q) use ($date): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $date->toDateString());
        });

        /** @var Collection<int, Availability> $availabilities */
        $availabilities = $query->orderBy('start_time')->get();

        return $availabilities;
    }

    /**
     * Check if schedulable is available at specific datetime.
     */
    public function isAvailableAt(
        Model $model,
        Carbon $datetime
    ): bool {
        $dayOfWeek = strtolower($datetime->englishDayOfWeek);
        $time = $datetime->format('H:i:s');

        $query = Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model))
            ->whereJsonContains('days', $dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time);

        $query->where(function ($q) use ($datetime): void {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', $datetime->toDateString());
        })->where(function ($q) use ($datetime): void {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', $datetime->toDateString());
        });

        return $query->exists();
    }

    /**
     * Find overlapping availabilities.
     *
     * @param array<string, mixed> $data
     * @return Collection<int, Availability>
     */
    public function findOverlapping(
        Model $model,
        array $data,
        ?int $exceptId = null
    ): Collection {
        ['start' => $startTime, 'end' => $endTime] = $this->validationService
            ->parseAndValidateTimeRange($data);

        $days = $data['days'] ?? [];
        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : null;
        $endDate = isset($data['end_date']) ? Carbon::parse($data['end_date']) : null;

        $query = Availability::where('schedulable_id', $model->id)
            ->where('schedulable_type', get_class($model));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        /** @var Collection<int, Availability> $allAvailabilities */
        $allAvailabilities = $query->get();

        return $allAvailabilities->filter(
            function (Availability $availability) use ($startTime, $endTime, $startDate, $endDate, $days): bool {
                if (!empty($days)) {
                    $commonDays = array_intersect($availability->days, $days);
                    if ($commonDays === []) {
                        return false;
                    }
                }

                return $this->overlaps($availability, $startTime, $endTime, $startDate, $endDate);
            }
        );
    }

    /**
     * Check if availability overlaps with given parameters.
     */
    private function overlaps(
        Availability $availability,
        Carbon $startTime,
        Carbon $endTime,
        ?Carbon $startDate,
        ?Carbon $endDate
    ): bool {
        // Convert availability times to Carbon for comparison
        $availabilityStart = Carbon::parse($availability->start_time->format('H:i:s'));
        $availabilityEnd = Carbon::parse($availability->end_time->format('H:i:s'));

        // Check time overlap
        if ($availabilityStart >= $endTime || $availabilityEnd <= $startTime) {
            return false;
        }

        // Check date overlap
        if ($startDate instanceof Carbon && $availability->end_date && $startDate > $availability->end_date) {
            return false;
        }

        return !($endDate instanceof Carbon && $availability->start_date && $endDate < $availability->start_date);
    }
}
