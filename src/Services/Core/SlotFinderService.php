<?php

declare(strict_types=1);

namespace Roster\Services\Core;

use Illuminate\Support\Collection;
use Roster\Models\Impediment;
use Illuminate\Support\Carbon;
use Roster\Models\Availability;

class SlotFinderService
{
    protected ValidationService $validationService;

    public function __construct(
        ValidationService $validationService
    ) {
        $this->validationService = $validationService;
    }

    /**
     * Get available time slots from impediments.
     *
     * @param Collection<int, Impediment> $impediments Collection of Impediment objects
     *
     * @return Collection<int, array{start: Carbon, end: Carbon}>
     */
    public function getAvailableSlotsFromImpediments(
        Availability $availability,
        Carbon $start,
        Carbon $end,
        Collection $impediments
    ): Collection {
        if ($impediments->isEmpty()) {
            return collect([['start' => $start, 'end' => $end]]);
        }

        $availableSlots = collect();
        $currentTime = $start->copy();

        /** @var Impediment $impediment */
        foreach ($impediments as $impediment) {
            $impStart = $impediment->start_datetime;
            $impEnd = $impediment->end_datetime;

            // Si l'impediment commence après le temps courant, il y a un créneau disponible
            if ($impStart->gt($currentTime)) {
                $availableSlots->push([
                    'start' => $currentTime->copy(),
                    'end' => $impStart->copy(),
                ]);
            }

            // Mettre à jour le temps courant à la fin de l'impediment
            $currentTime = $currentTime->gt($impEnd) ? $currentTime : $impEnd;
        }

        // Vérifier s'il reste du temps après le dernier impediment
        if ($currentTime->lt($end)) {
            $availableSlots->push([
                'start' => $currentTime->copy(),
                'end' => $end->copy(),
            ]);
        }

        return $availableSlots;
    }
}
