<?php

declare(strict_types=1);

namespace Roster\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Roster\Models\Schedule;

/**
 * Resource for serializing Schedule models to JSON
 *
 * @property-read int $id
 * @property-read int $availability_id
 * @property-read int $schedulable_id
 * @property-read string $schedulable_type
 * @property-read string $title
 * @property-read string|null $description
 * @property-read \DateTimeInterface|null $start_datetime
 * @property-read \DateTimeInterface|null $end_datetime
 * @property-read \Roster\Enums\ScheduleStatus $status
 * @property-read array|null $metadata
 * @property-read string $type
 * @property-read int $duration_minutes
 * @property-read \DateTimeInterface|null $created_at
 * @property-read \DateTimeInterface|null $updated_at
 * @property-read \DateTimeInterface|null $deleted_at
 *
 * @mixin Schedule
 */
final class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Relations / ownership
            'availability_id' => $this->availability_id,
            'schedulable' => $this->formatSchedulableData(),

            // Core data
            'title' => $this->title,
            'description' => $this->description,

            // Time data (timezone-aware cast already applied)
            'start_datetime' => $this->formatDateTimeToIso8601($this->start_datetime),
            'end_datetime' => $this->formatDateTimeToIso8601($this->end_datetime),

            // Status
            'status' => $this->status->value,

            // Metadata
            'metadata' => $this->metadata,

            // Computed / derived
            'type' => $this->type,
            'duration_minutes' => $this->duration_minutes,

            // State helpers
            'is_active' => $this->isActive(),
            'is_upcoming' => $this->isUpcoming(),
            'is_past' => $this->isPast(),

            // Timestamps
            'created_at' => $this->formatDateTimeToIso8601($this->created_at),
            'updated_at' => $this->formatDateTimeToIso8601($this->updated_at),
            'deleted_at' => $this->formatDateTimeToIso8601($this->deleted_at),
        ];
    }

    /**
     * Format schedulable data into consistent structure
     *
     * @return array<string, mixed>
     */
    private function formatSchedulableData(): array
    {
        return [
            'id' => $this->schedulable_id,
            'type' => $this->schedulable_type,
        ];
    }

    /**
     * Format datetime to ISO 8601 string if not null
     *
     * @param \DateTimeInterface|null $dateTime
     * @return string|null
     */
    private function formatDateTimeToIso8601(?\DateTimeInterface $dateTime): ?string
    {
        return $dateTime?->format('c');
    }
}
