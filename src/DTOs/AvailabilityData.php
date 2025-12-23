<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Illuminate\Support\Carbon;
use Roster\Enums\DaysOfWeek;
use Roster\Models\Availability;

class AvailabilityData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $type = null,
        public readonly ?array $days = null,
        public readonly ?Carbon $validityStart = null,
        public readonly ?Carbon $validityEnd = null,
        public readonly ?Carbon $dailyStart = null,
        public readonly ?Carbon $dailyEnd = null,
        public readonly ?int $schedulableId = null,
        public readonly ?string $schedulableType = null
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            type: $data['type'] ?? null,
            days: isset($data['days']) ? (array)$data['days'] : null,
            validityStart: isset($data['validity_start']) ? Carbon::parse($data['validity_start']) : null,
            validityEnd: isset($data['validity_end']) ? Carbon::parse($data['validity_end']) : null,
            dailyStart: isset($data['daily_start']) ? Carbon::parse($data['daily_start']) : null,
            dailyEnd: isset($data['daily_end']) ? Carbon::parse($data['daily_end']) : null,
            schedulableId: $data['schedulable_id'] ?? null,
            schedulableType: $data['schedulable_type'] ?? null
        );
    }

    public static function fromModel(Availability $availability): self
    {
        return new self(
            id: $availability->id,
            type: $availability->type,
            days: $availability->days,
            validityStart: $availability->validity_start,
            validityEnd: $availability->validity_end,
            dailyStart: $availability->daily_start,
            dailyEnd: $availability->daily_end,
            schedulableId: $availability->schedulable_id,
            schedulableType: $availability->schedulable_type
        );
    }

    /**
     * @return array<string, int|string|mixed[]|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'type' => $this->type,
            'days' => $this->days,
            'validity_start' => $this->validityStart?->format('Y-m-d'),
            'validity_end' => $this->validityEnd?->format('Y-m-d'),
            'daily_start' => $this->dailyStart?->format('H:i:s'),
            'daily_end' => $this->dailyEnd?->format('H:i:s'),
            'schedulable_id' => $this->schedulableId,
            'schedulable_type' => $this->schedulableType,
        ], static fn(int|string|array|null $value): bool => $value !== null);
    }


    public function withSchedulableInfo(?int $schedulableId, ?string $schedulableType): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            days: $this->days,
            validityStart: $this->validityStart,
            validityEnd: $this->validityEnd,
            dailyStart: $this->dailyStart,
            dailyEnd: $this->dailyEnd,
            schedulableId: $schedulableId,
            schedulableType: $schedulableType
        );
    }

    public function withDaysInfo(?array $days): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            days: $days ?? $this->days,
            validityStart: $this->validityStart,
            validityEnd: $this->validityEnd,
            dailyStart: $this->dailyStart,
            dailyEnd: $this->dailyEnd,
            schedulableId: $this->schedulableId,
            schedulableType: $this->schedulableType
        );
    }

    public function withAvailabilityId(?int $availabilityId): self
    {
        return new self(
            id: $availabilityId,
            type: $this->type,
            days: $this->days,
            validityStart: $this->validityStart,
            validityEnd: $this->validityEnd,
            dailyStart: $this->dailyStart,
            dailyEnd: $this->dailyEnd,
            schedulableId: $this->schedulableId,
            schedulableType: $this->schedulableType
        );
    }

    /**
     * Filtre automatiquement les jours existants en fonction des nouvelles dates de validité
     * Utilisé pour les mises à jour où l'utilisateur ne fournit pas explicitement de jours
     */
    public function withAutoFilteredDaysForUpdate(
        array $existingDays,
        ?Carbon $existingValidityStart,
        ?Carbon $existingValidityEnd
    ): self {
        // Si l'utilisateur fournit explicitement des jours, on ne fait rien
        if ($this->days !== null) {
            return $this;
        }

        // Déterminer les dates de validité à utiliser après mise à jour
        $newValidityStart = $this->validityStart ?? $existingValidityStart;
        $newValidityEnd = $this->validityEnd ?? $existingValidityEnd;

        // Si aucune date de validité n'est fournie ou si les dates n'ont pas changé, retourner tel quel
        if (!$newValidityStart instanceof Carbon || !$newValidityEnd instanceof Carbon) {
            return $this->withDaysInfo($existingDays);
        }

        // Vérifier si les dates ont changé
        $startChanged = $this->validityStart instanceof Carbon;
        $endChanged = $this->validityEnd instanceof Carbon;
        $datesChanged = $startChanged || $endChanged;

        // Si les dates n'ont pas changé, retourner les jours existants
        if (!$datesChanged) {
            return $this->withDaysInfo($existingDays);
        }

        // Filtrer les jours existants pour ne garder que ceux dans la nouvelle période
        $filteredDays = roster_get_valid_days_in_period($existingDays, $newValidityStart, $newValidityEnd);

        return $this->withDaysInfo($filteredDays);
    }

    /**
     * Détermine les jours automatiquement basés sur la période de validité
     */
    public function getAutoAdjustedDays(): array
    {
        // Si les jours sont déjà fournis, les utiliser
        if ($this->days !== null) {
            return $this->days;
        }

        // Si pas de dates de validité, utiliser tous les jours
        if (!$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
            return DaysOfWeek::values();
        }

        // Utiliser le helper pour déterminer si on doit ajuster
        if (!roster_should_auto_adjust_days($this->validityStart, $this->validityEnd)) {
            return DaysOfWeek::values();
        }

        // Utiliser le helper pour obtenir les jours dans la période
        return roster_days_in_period($this->validityStart, $this->validityEnd);
    }

    /**
     * Filtre les jours pour ne garder que ceux dans la période actuelle
     */
    public function filterDaysByCurrentPeriod(?array $existingDays = null): array
    {
        $daysToFilter = $existingDays ?? $this->days ?? [];

        if ($daysToFilter === [] || !$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
            return $daysToFilter;
        }

        // Utiliser le helper pour filtrer les jours
        return roster_get_valid_days_in_period($daysToFilter, $this->validityStart, $this->validityEnd);
    }

    /**
     * Vérifie si les jours sont valides
     */
    public function hasValidDays(): bool
    {
        if (!is_array($this->days) || $this->days === []) {
            return false;
        }

        $validDays = DaysOfWeek::values();
        foreach ($this->days as $day) {
            if (!in_array($day, $validDays, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne les jours ou la valeur par défaut ajustée
     */
    public function getDaysOrDefault(): array
    {
        if ($this->days !== null) {
            return $this->days;
        }

        return $this->getAutoAdjustedDays();
    }

    /**
     * Vérifie si un jour spécifique est dans la période
     */
    public function isDayInPeriod(string $day): bool
    {
        if (!$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
            return false;
        }

        return roster_is_day_in_period($day, $this->validityStart, $this->validityEnd);
    }

    /**
     * Calcule la durée de la période en jours
     */
    public function getPeriodDurationInDays(): ?int
    {
        if (!$this->validityStart instanceof Carbon || !$this->validityEnd instanceof Carbon) {
            return null;
        }

        return roster_period_duration_in_days($this->validityStart, $this->validityEnd);
    }
}
