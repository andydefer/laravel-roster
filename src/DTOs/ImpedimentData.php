<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Illuminate\Support\Carbon;
use Roster\Models\Impediment;

class ImpedimentData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $availabilityId = null,
        public readonly ?Carbon $startDatetime = null,
        public readonly ?Carbon $endDatetime = null,
        public readonly ?string $reason = null,
        public readonly ?array $metadata = [],
        public readonly ?int $schedulableId = null,
        public readonly ?string $schedulableType = null
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Traitement des metadata
        $metadata = $data['metadata'] ?? [];
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            $metadata = is_array($decoded) ? $decoded : [];
        }

        return new self(
            id: $data['id'] ?? null,
            availabilityId: $data['availability_id'] ?? null,
            startDatetime: isset($data['start_datetime']) ? Carbon::parse($data['start_datetime']) : null,
            endDatetime: isset($data['end_datetime']) ? Carbon::parse($data['end_datetime']) : null,
            reason: $data['reason'] ?? null,
            metadata: $metadata,
            schedulableId: $data['schedulable_id'] ?? null,
            schedulableType: $data['schedulable_type'] ?? null
        );
    }

    public static function fromModel(Impediment $impediment): self
    {
        return new self(
            id: $impediment->id,
            availabilityId: $impediment->availability_id,
            startDatetime: $impediment->start_datetime ? Carbon::parse($impediment->start_datetime) : null,
            endDatetime: $impediment->end_datetime ? Carbon::parse($impediment->end_datetime) : null,
            reason: $impediment->reason,
            metadata: $impediment->metadata ?? [],
            schedulableId: $impediment->schedulable_id,
            schedulableType: $impediment->schedulable_type
        );
    }

    /**
     * @return array<string, int|string|mixed[]|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'availability_id' => $this->availabilityId,
            'start_datetime' => $this->startDatetime?->format('Y-m-d H:i:s'),
            'end_datetime' => $this->endDatetime?->format('Y-m-d H:i:s'),
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'schedulable_id' => $this->schedulableId,
            'schedulable_type' => $this->schedulableType,
        ], static fn(int|string|array|null $value): bool => $value !== null);
    }

    public function withSchedulableInfo(?int $schedulableId, ?string $schedulableType): self
    {
        return new self(
            id: $this->id,
            availabilityId: $this->availabilityId,
            startDatetime: $this->startDatetime,
            endDatetime: $this->endDatetime,
            reason: $this->reason,
            metadata: $this->metadata,
            schedulableId: $schedulableId,
            schedulableType: $schedulableType
        );
    }

    public function withAvailabilityId(?int $availabilityId): self
    {
        return new self(
            id: $this->id,
            availabilityId: $availabilityId,
            startDatetime: $this->startDatetime,
            endDatetime: $this->endDatetime,
            reason: $this->reason,
            metadata: $this->metadata,
            schedulableId: $this->schedulableId,
            schedulableType: $this->schedulableType
        );
    }
}
