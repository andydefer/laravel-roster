<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Enums\ScheduleStatus;
use Roster\Models\Schedule;

/**
 * Data Transfer Object for schedule information.
 *
 * Provides an immutable, structured representation of schedule data with
 * validation, transformation, and business logic methods for schedule management.
 */
class ScheduleData extends AbstractData
{
    /**
     * @param int|null $id Schedule unique identifier
     * @param int|null $availabilityId Associated availability ID
     * @param string|null $title Title or name of the schedule
     * @param string|null $description Detailed description of the schedule
     * @param Carbon|null $startDatetime Schedule start date and time
     * @param Carbon|null $endDatetime Schedule end date and time
     * @param array<string, mixed>|null $metadata Additional metadata as key-value pairs
     * @param ScheduleStatus|null $status Current status of the schedule
     * @param int|null $schedulableId ID of the schedulable entity (e.g., User, Resource)
     * @param string|null $schedulableType Type of the schedulable entity (e.g., App\Models\User)
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $availabilityId,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?Carbon $startDatetime = null,
        public readonly ?Carbon $endDatetime = null,
        public readonly ?array $metadata = [],
        public readonly ?ScheduleStatus $status = ScheduleStatus::AVAILABLE,
        public readonly ?int $schedulableId = null,
        public readonly ?string $schedulableType = null
    ) {}

    /**
     * Create a ScheduleData instance from raw array data.
     *
     * @param array{
     *     id?: int|null,
     *     availability_id?: int|null,
     *     title?: string|null,
     *     description?: string|null,
     *     start_datetime?: string|Carbon|null,
     *     end_datetime?: string|Carbon|null,
     *     metadata?: array<string, mixed>|null,
     *     status?: ScheduleStatus|null,
     *     schedulable_id?: int|null,
     *     schedulable_type?: string|null
     * } $data Raw schedule data
     * @return self New immutable ScheduleData instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            availabilityId: $data['availability_id'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            startDatetime: self::parseDateTime($data['start_datetime'] ?? null),
            endDatetime: self::parseDateTime($data['end_datetime'] ?? null),
            metadata: $data['metadata'] ?? [],
            status: $data['status'] ?? ScheduleStatus::AVAILABLE,
            schedulableId: $data['schedulable_id'] ?? null,
            schedulableType: $data['schedulable_type'] ?? null
        );
    }

    /**
     * Create a ScheduleData instance from a Schedule Eloquent model.
     *
     * @param Schedule $schedule Eloquent model instance
     * @return self New immutable ScheduleData instance
     */
    public static function fromModel(Model $schedule): self
    {
        return new self(
            id: $schedule->id,
            availabilityId: $schedule->availability_id,
            title: $schedule->title,
            description: $schedule->description,
            startDatetime: self::parseDateTime($schedule->start_datetime),
            endDatetime: self::parseDateTime($schedule->end_datetime),
            metadata: $schedule->metadata ?? [],
            status: $schedule->status,
            schedulableId: $schedule->schedulable_id,
            schedulableType: $schedule->schedulable_type
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
            'title' => $this->title,
            'description' => $this->description,
            'start_datetime' => $this->startDatetime?->format('Y-m-d H:i:s'),
            'end_datetime' => $this->endDatetime?->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata,
            'status' => $this->status,
            'schedulable_id' => $this->schedulableId,
            'schedulable_type' => $this->schedulableType,
        ];
    }
}
