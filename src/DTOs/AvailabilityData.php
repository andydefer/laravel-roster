<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Domain\Helpers\TimeSlotHelper;
use Roster\Models\Availability;
use Roster\Support\RosterMutationContext;

/**
 * Data Transfer Object for availability information.
 *
 * Provides structured, immutable access to availability data with validation,
 * transformation, and business logic methods for availability management.
 */
class AvailabilityData extends AbstractData
{
    private ?Availability $availability = null;

    /**
     * @param int|null $id Unique identifier of the availability
     * @param string|null $type Type/category of the availability
     * @param array|null $days Array of days when availability is active (e.g., ['monday', 'tuesday'])
     * @param Carbon|null $validityStart Start date of availability validity period
     * @param Carbon|null $validityEnd End date of availability validity period
     * @param Carbon|null $dailyStart Daily start time of availability
     * @param Carbon|null $dailyEnd Daily end time of availability
     * @param int|null $schedulableId ID of the associated schedulable entity
     * @param string|null $schedulableType Type of the associated schedulable entity
     */
    private function __construct(
        public readonly ?int $id,
        public readonly ?string $type = null,
        public readonly ?array $days = null,
        public readonly ?Carbon $validityStart = null,
        public readonly ?Carbon $validityEnd = null,
        public readonly ?Carbon $dailyStart = null,
        public readonly ?Carbon $dailyEnd = null,
        public readonly ?int $schedulableId = null,
        public readonly ?string $schedulableType = null
    ) {
        // Si c'est une mise à jour (avec ID), on charge l'entité existante
        if ($this->id !== null) {
            $this->loadExistingEntity();
        }
    }

    /**
     * Creates an AvailabilityData instance from raw array data.
     *
     * @param array{
     *     id?: int|null,
     *     type?: string|null,
     *     days?: array|string|null,
     *     validity_start?: string|null,
     *     validity_end?: string|null,
     *     daily_start?: string|null,
     *     daily_end?: string|null,
     *     schedulable_id?: int|null,
     *     schedulable_type?: string|null
     * } $data Raw availability data
     * @return self New immutable AvailabilityData instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            type: $data['type'] ?? null,
            days: isset($data['days']) ? (array) $data['days'] : [],
            validityStart: self::parseDateTime($data['validity_start'] ?? null),
            validityEnd: self::parseDateTime($data['validity_end'] ?? null),
            dailyStart: self::parseTime($data['daily_start'] ?? null),
            dailyEnd: self::parseTime($data['daily_end'] ?? null),
            schedulableId: $data['schedulable_id'] ?? null,
            schedulableType: $data['schedulable_type'] ?? null
        );
    }

    /**
     * Creates an AvailabilityData instance from an Availability Eloquent model.
     *
     * @param Availability $model Eloquent model instance
     * @return self New immutable AvailabilityData instance
     */
    public static function fromModel(Model $model): self
    {
        return new self(
            id: $model->id,
            type: $model->type,
            days: $model->days,
            validityStart: $model->validity_start,
            validityEnd: $model->validity_end,
            dailyStart: $model->daily_start,
            dailyEnd: $model->daily_end,
            schedulableId: $model->schedulable_id,
            schedulableType: $model->schedulable_type
        );
    }

    /**
     * Get the array data for this DTO with adjusted days if needed.
     *
     * @return array<string, mixed> Raw array data
     */
    protected function getArrayData(): array
    {
        // On ajuste automatiquement les jours
        $adjustedDays = $this->getAdjustedDays();


        return [
            'id' => $this->id,
            'type' => $this->type,
            'days' => $adjustedDays,
            'validity_start' => $this->validityStart?->format('Y-m-d H:i:s'),
            'validity_end' => $this->validityEnd?->format('Y-m-d H:i:s'),
            'daily_start' => $this->dailyStart?->format('H:i:s'),
            'daily_end' => $this->dailyEnd?->format('H:i:s'),
            'schedulable_id' => $this->schedulableId,
            'schedulable_type' => $this->schedulableType,
        ];
    }

    /**
     * Creates a new instance with updated days configuration.
     *
     * @param array|null $days New array of days when availability is active
     * @return self New instance with updated days
     */
    public function withDays(?array $days): self
    {
        $data = $this->toArray();
        $data['days'] = $days;

        return self::fromArray($data);
    }

    /**
     * Get the adjusted days using TimeSlotHelper.
     *
     * @return array<int, string> Array of valid days
     */
    private function getAdjustedDays(): array
    {
        // Déterminer si c'est une mise à jour (entité existante chargée)
        $isUpdate = $this->availability instanceof Availability;
        // Récupérer les données existantes si disponible
        $existingDays = $this->availability?->days;
        $existingValidityStart = $this->availability?->validity_start;
        $existingValidityEnd = $this->availability?->validity_end;

        // Utiliser TimeSlotHelper pour le calcul des jours ajustés
        return TimeSlotHelper::getAdjustedDays(
            requestedDays: $this->days,
            validityStart: $this->validityStart,
            validityEnd: $this->validityEnd,
            existingDays: $existingDays,
            existingValidityStart: $existingValidityStart,
            existingValidityEnd: $existingValidityEnd,
            isUpdate: $isUpdate
        );
    }

    /**
     * Load the existing entity if this is an update operation.
     * Utilise RosterMutationContext pour respecter les règles d'accès.
     */
    private function loadExistingEntity(): void
    {
        if (!$this->id) {
            return;
        }

        try {
            // Utiliser le contexte de mutation pour charger l'entité
            $this->availability = RosterMutationContext::allow(function () {
                return Availability::find($this->id);
            });
        } catch (Exception) {
            $this->availability = null;
        }
    }

    /**
     * Check if the availability has complete daily time information.
     *
     * @return bool True if both daily start and end times are set
     */
    public function hasDailyTimes(): bool
    {
        return $this->dailyStart instanceof Carbon && $this->dailyEnd instanceof Carbon;
    }

    /**
     * Check if the availability has a valid date range.
     *
     * @return bool True if validity dates form a valid range
     */
    public function hasValidDateRange(): bool
    {
        return $this->isValidDateRange($this->validityStart, $this->validityEnd);
    }

    /**
     * Check if a date range is valid.
     *
     * @param Carbon|null $start Start date
     * @param Carbon|null $end End date
     * @return bool True if both dates exist and form a valid range
     */
    private function isValidDateRange(?Carbon $start, ?Carbon $end): bool
    {
        return $start instanceof Carbon && $end instanceof Carbon && $start->lte($end);
    }

    /**
     * Check if this DTO represents an update operation.
     *
     * @return bool True if this is an update (has ID and existing entity was found)
     */
    public function isUpdateOperation(): bool
    {
        return $this->availability instanceof Availability;
    }

    /**
     * Get all days of the week in English lowercase.
     *
     * @return array<int, string> ['monday', 'tuesday', ...]
     */
    public static function getDaysOfWeek(): array
    {
        $daysOfWeek = [];
        for ($i = 0; $i < 7; $i++) {
            $daysOfWeek[] = strtolower(Carbon::now()->startOfWeek()->addDays($i)->format('l'));
        }

        return $daysOfWeek;
    }

    /**
     * Get the existing entity if this is an update operation.
     *
     * @return Availability|null The existing entity or null
     */
    public function getExistingEntity(): ?Availability
    {
        return $this->availability;
    }
}
