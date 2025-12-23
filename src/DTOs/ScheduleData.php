<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Illuminate\Support\Carbon;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Schedule;

class ScheduleData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $availabilityId,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?Carbon $startDatetime = null,
        public readonly ?Carbon $endDatetime = null,
        public readonly ?array $metadata = [],
        public readonly ScheduleStatus|string $status = ScheduleStatus::AVAILABLE,
        public readonly ?int $schedulableId = null,
        public readonly ?string $schedulableType = null
    ) {
        // Convertir ScheduleStatus en string si nécessaire
        if ($status instanceof ScheduleStatus) {
            // On ne peut pas modifier une propriété readonly directement
            // On va utiliser un workaround via reflection ou changer l'approche
            // Plutôt, on va gérer cela dans les getters et toArray()
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Gestion du statut : accepter string ou ScheduleStatus
        $status = $data['status'] ?? ScheduleStatus::AVAILABLE;

        // Si c'est une string, tenter de la convertir en enum
        if (is_string($status)) {
            try {
                $status = ScheduleStatus::from($status);
            } catch (\ValueError $e) {
                // Si la valeur n'est pas valide, utiliser la valeur par défaut
                $status = ScheduleStatus::AVAILABLE;
            }
        }

        // Si c'est déjà un enum, le garder tel quel
        if (!$status instanceof ScheduleStatus && !is_string($status)) {
            $status = ScheduleStatus::AVAILABLE;
        }

        // Traitement des metadata
        $metadata = $data['metadata'] ?? [];
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            $metadata = is_array($decoded) ? $decoded : [];
        }

        return new self(
            id: $data['id'] ?? null,
            availabilityId: $data['availability_id'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            startDatetime: isset($data['start_datetime']) ? Carbon::parse($data['start_datetime']) : null,
            endDatetime: isset($data['end_datetime']) ? Carbon::parse($data['end_datetime']) : null,
            metadata: $metadata,
            status: $status,
            schedulableId: $data['schedulable_id'] ?? null,
            schedulableType: $data['schedulable_type'] ?? null
        );
    }

    public static function fromModel(Schedule $schedule): self
    {
        return new self(
            id: $schedule->id,
            availabilityId: $schedule->availability_id,
            title: $schedule->title,
            description: $schedule->description,
            startDatetime: $schedule->start_datetime ? Carbon::parse($schedule->start_datetime) : null,
            endDatetime: $schedule->end_datetime ? Carbon::parse($schedule->end_datetime) : null,
            metadata: $schedule->metadata ?? [],
            status: $schedule->status,
            schedulableId: $schedule->schedulable_id,
            schedulableType: $schedule->schedulable_type
        );
    }

    /**
     * @return array<string, int|string|mixed[]|null>
     */
    public function toArray(): array
    {
        // Convertir le statut en string si c'est un enum
        $status = $this->status instanceof ScheduleStatus ? $this->status->value : $this->status;

        return array_filter([
            'id' => $this->id,
            'availability_id' => $this->availabilityId,
            'title' => $this->title,
            'description' => $this->description,
            'start_datetime' => $this->startDatetime?->format('Y-m-d H:i:s'),
            'end_datetime' => $this->endDatetime?->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata,
            'status' => $status,
            'schedulable_id' => $this->schedulableId,
            'schedulable_type' => $this->schedulableType,
        ], static fn($value) => $value !== null);
    }

    public function withSchedulableInfo(?int $schedulableId, ?string $schedulableType): self
    {
        return new self(
            id: $this->id,
            availabilityId: $this->availabilityId,
            title: $this->title,
            description: $this->description,
            startDatetime: $this->startDatetime,
            endDatetime: $this->endDatetime,
            metadata: $this->metadata,
            status: $this->status,
            schedulableId: $schedulableId,
            schedulableType: $schedulableType
        );
    }

    public function withAvailabilityId(int $availabilityId): self
    {
        return new self(
            id: $this->id,
            availabilityId: $availabilityId,
            title: $this->title,
            description: $this->description,
            startDatetime: $this->startDatetime,
            endDatetime: $this->endDatetime,
            metadata: $this->metadata,
            status: $this->status,
            schedulableId: $this->schedulableId,
            schedulableType: $this->schedulableType
        );
    }

    /**
     * Vérifie si le statut est valide
     */
    public function hasValidStatus(): bool
    {
        if ($this->status instanceof ScheduleStatus) {
            return true;
        }

        if (is_string($this->status)) {
            return in_array($this->status, ScheduleStatus::values(), true);
        }

        return false;
    }

    /**
     * Retourne le statut ou la valeur par défaut
     */
    public function getStatusOrDefault(): string
    {
        if ($this->status instanceof ScheduleStatus) {
            return $this->status->value;
        }

        if (is_string($this->status) && $this->hasValidStatus()) {
            return $this->status;
        }

        return ScheduleStatus::AVAILABLE->value;
    }

    /**
     * Get the status as string
     */
    public function getStatusAsString(): string
    {
        return $this->getStatusOrDefault();
    }
}
