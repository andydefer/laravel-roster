<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Models\Impediment;

/**
 * Data Transfer Object for impediment information.
 *
 * Provides an immutable, structured representation of impediment data with
 * validation, transformation, and business logic methods for impediment management.
 */
class ImpedimentDto extends AbstractDto
{
    /**
     * @param int|null $id Impediment unique identifier
     * @param int|null $availabilityId Associated availability ID
     * @param Carbon|null $startDatetime Impediment start date and time
     * @param Carbon|null $endDatetime Impediment end date and time
     * @param string|null $reason Reason for the impediment
     * @param array<string, mixed>|null $metadata Additional metadata as key-value pairs
     * @param int|null $schedulableId ID of the schedulable entity (e.g., User, Resource)
     * @param string|null $schedulableType Type of the schedulable entity (e.g., App\Models\User)
     */
    private function __construct(
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
     * Create an ImpedimentDto instance from raw array data.
     *
     * @param array{
     *     id?: int|null,
     *     availability_id?: int|null,
     *     start_datetime?: string|Carbon|null,
     *     end_datetime?: string|Carbon|null,
     *     reason?: string|null,
     *     metadata?: array<string, mixed>|null,
     *     schedulable_id?: int|null,
     *     schedulable_type?: string|null
     * } $data Raw impediment data
     * @return self New immutable ImpedimentDto instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            availabilityId: $data['availability_id'] ?? null,
            startDatetime: self::parseDateTime($data['start_datetime'] ?? null),
            endDatetime: self::parseDateTime($data['end_datetime'] ?? null),
            reason: $data['reason'] ?? null,
            metadata: $data['metadata'] ?? [],
            schedulableId: $data['schedulable_id'] ?? null,
            schedulableType: $data['schedulable_type'] ?? null
        );
    }

    /**
     * Create an ImpedimentDto instance from an Impediment Eloquent model.
     *
     * @param Impediment $model Eloquent model instance
     * @return self New immutable ImpedimentDto instance
     */
    public static function fromModel(Model $model): self
    {
        return new self(
            id: $model->id,
            availabilityId: $model->availability_id,
            startDatetime: self::parseDateTime($model->start_datetime),
            endDatetime: self::parseDateTime($model->end_datetime),
            reason: $model->reason,
            metadata: $model->metadata ?? [],
            schedulableId: $model->schedulable_id,
            schedulableType: $model->schedulable_type
        );
    }

    /**
     * Get the array data for this DTO.
     *
     * @return array<string, mixed> Raw array data
     */
    protected function getArrayData(): array
    {
        return [
            'id' => $this->id,
            'availability_id' => $this->availabilityId,
            'start_datetime' => $this->startDatetime?->format('Y-m-d H:i:s'),
            'end_datetime' => $this->endDatetime?->format('Y-m-d H:i:s'),
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'schedulable_id' => $this->schedulableId,
            'schedulable_type' => $this->schedulableType,
        ];
    }
}
