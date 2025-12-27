<?php

declare(strict_types=1);

namespace Roster\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Roster\Enums\DaysOfWeek;
use Roster\Models\Availability;

/**
 * Data Transfer Object for availability information.
 *
 * Provides structured, immutable access to availability data with validation,
 * transformation, and business logic methods for availability management.
 */
class AvailabilityData extends AbstractData
{
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
            days: isset($data['days']) ? (array) $data['days'] : null,
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
     * @param Availability $availability Eloquent model instance
     * @return self New immutable AvailabilityData instance
     */
    public static function fromModel(Model $availability): self
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
     * Get the array data for this DTO.
     *
     * @return array<string, mixed> Raw array data
     */
    protected function getArrayData(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'days' => $this->days,
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

        return static::fromArray($data);
    }


    /**
     * Determines appropriate days based on validity period with automatic adjustment.
     *
     * @return array<int, string> Array of valid days for the current validity period
     */
    public function getAutoAdjustedDays(): array
    {
        if ($this->days !== null) {
            return $this->days;
        }

        if (!$this->shouldAutoAdjustDays()) {
            return DaysOfWeek::values();
        }

        return roster_days_in_period($this->validityStart, $this->validityEnd);
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
     * Determine if automatic day adjustment should be performed.
     *
     * @return bool True if days should be auto-adjusted
     */
    private function shouldAutoAdjustDays(): bool
    {
        return $this->hasValidDateRange()
            && roster_should_auto_adjust_days($this->validityStart, $this->validityEnd);
    }
}
