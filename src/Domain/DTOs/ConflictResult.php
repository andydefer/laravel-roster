<?php

declare(strict_types=1);

namespace Roster\Domain\DTOs;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;

/**
 * Data Transfer Object representing conflict detection results.
 *
 * Used to encapsulate information about scheduling conflicts,
 * including conflicting schedules and impediments.
 */
class ConflictResult
{
    /**
     * @param bool $hasConflicts Whether any conflicts were detected
     * @param array<int, Model> $conflictingSchedules Array of conflicting schedule models
     * @param array<int, Model> $conflictingImpediments Array of conflicting impediment models
     * @param string|null $message Human-readable conflict description
     * @param CarbonInterval|null $overlapDuration Duration of the conflict overlap
     */
    public function __construct(
        public bool $hasConflicts = false,
        public array $conflictingSchedules = [],
        public array $conflictingImpediments = [],
        public ?string $message = null,
        public ?CarbonInterval $overlapDuration = null
    ) {}

    /**
     * Create a result indicating no conflicts found.
     *
     * @return self ConflictResult with no conflicts
     */
    public static function noConflict(): self
    {
        return new self(
            hasConflicts: false,
            conflictingSchedules: [],
            conflictingImpediments: []
        );
    }

    /**
     * Create a result indicating schedule conflicts.
     *
     * @param array<int, Model> $schedules Conflicting schedule models
     * @param string|null $message Custom conflict message
     * @return self ConflictResult with schedule conflicts
     */
    public static function scheduleConflict(array $schedules, ?string $message = null): self
    {
        return new self(
            hasConflicts: true,
            conflictingSchedules: $schedules,
            conflictingImpediments: [],
            message: $message ?? 'Conflict with existing schedule(s)'
        );
    }

    /**
     * Create a result indicating impediment conflicts.
     *
     * @param array<int, Model> $impediments Conflicting impediment models
     * @param string|null $message Custom conflict message
     * @return self ConflictResult with impediment conflicts
     */
    public static function impedimentConflict(array $impediments, ?string $message = null): self
    {
        return new self(
            hasConflicts: true,
            conflictingSchedules: [],
            conflictingImpediments: $impediments,
            message: $message ?? 'Conflict with existing impediment(s)'
        );
    }

    /**
     * Get total number of conflicting items.
     *
     * @return int Sum of conflicting schedules and impediments
     */
    public function getTotalConflicts(): int
    {
        return count($this->conflictingSchedules) + count($this->conflictingImpediments);
    }

    /**
     * Check if there are any schedule conflicts.
     *
     * @return bool True if schedule conflicts exist
     */
    public function hasScheduleConflicts(): bool
    {
        return $this->conflictingSchedules !== [];
    }

    /**
     * Check if there are any impediment conflicts.
     *
     * @return bool True if impediment conflicts exist
     */
    public function hasImpedimentConflicts(): bool
    {
        return $this->conflictingImpediments !== [];
    }

    /**
     * Get the ID of the first conflicting schedule.
     *
     * @return int|null Schedule ID or null if no schedule conflicts
     */
    public function getFirstScheduleId(): ?int
    {
        return $this->conflictingSchedules[0]->id ?? null;
    }

    /**
     * Get the ID of the first conflicting impediment.
     *
     * @return int|null Impediment ID or null if no impediment conflicts
     */
    public function getFirstImpedimentId(): ?int
    {
        return $this->conflictingImpediments[0]->id ?? null;
    }
}
