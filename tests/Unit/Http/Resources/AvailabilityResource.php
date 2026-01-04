<?php

declare(strict_types=1);

namespace Roster\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Roster\Models\Availability;

/**
 * Resource for serializing Availability models to JSON
 *
 * @property-read int $id
 * @property-read int $schedulable_id
 * @property-read string $schedulable_type
 * @property-read string $type
 * @property-read array $days
 * @property-read \DateTimeInterface|null $daily_start
 * @property-read \DateTimeInterface|null $daily_end
 * @property-read \DateTimeInterface|null $validity_start
 * @property-read \DateTimeInterface|null $validity_end
 * @property-read \DateTimeInterface|null $created_at
 * @property-read \DateTimeInterface|null $updated_at
 * @property-read \DateTimeInterface|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|null $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|null $impediments
 *
 * @mixin Availability
 */
final class AvailabilityResource extends JsonResource
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

            // Owner information
            'schedulable' => $this->formatSchedulableData(),

            // Core configuration
            'type' => $this->type,
            'days' => $this->days,

            // Daily window configuration
            'daily_start' => $this->formatTimeOrNull($this->daily_start),
            'daily_end' => $this->formatTimeOrNull($this->daily_end),
            'daily_duration_minutes' => $this->getDailyDurationMinutes(),

            // Validity window
            'validity_start' => $this->formatDateTimeToIso8601($this->validity_start),
            'validity_end' => $this->formatDateTimeToIso8601($this->validity_end),
            'validity_duration_days' => $this->getValidityDurationDays(),
            'has_unlimited_validity' => $this->hasUnlimitedValidity(),
            'is_validity_active' => $this->isValidityActive(),

            // Eager loaded relations
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'impediments' => ImpedimentResource::collection($this->whenLoaded('impediments')),

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
     * Format time to H:i:s format if not null
     *
     * @param \DateTimeInterface|null $time
     * @return string|null
     */
    private function formatTimeOrNull(?\DateTimeInterface $time): ?string
    {
        return $time?->format('H:i:s');
    }

    /**
     * Format datetime to ISO 8601 string if not null
     *
     * @param \DateTimeInterface|null $dateTime
     * @return string|null
     */
    private function formatDateTimeToIso8601(?\DateTimeInterface $dateTime): ?string
    {
        return $dateTime?->format('c'); // format('c') returns ISO 8601 date
    }
}
