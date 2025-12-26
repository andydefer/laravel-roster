<?php

declare(strict_types=1);

namespace Roster\Domain\DTOs;

use Carbon\CarbonInterval;

class ConflictResult
{
    public function __construct(
        public bool $hasConflicts = false,
        public array $conflictingSchedules = [],
        public array $conflictingImpediments = [],
        public ?string $message = null,
        public ?CarbonInterval $overlapDuration = null
    ) {}

    public static function noConflict(): self
    {
        return new self(false, [], []);
    }

    public static function scheduleConflict(array $schedules, ?string $message = null): self
    {
        return new self(
            hasConflicts: true,
            conflictingSchedules: $schedules,
            conflictingImpediments: [],
            message: $message ?? 'Conflict with existing schedule(s)'
        );
    }

    public static function impedimentConflict(array $impediments, ?string $message = null): self
    {
        return new self(
            hasConflicts: true,
            conflictingSchedules: [],
            conflictingImpediments: $impediments,
            message: $message ?? 'Conflict with existing impediment(s)'
        );
    }

    public function getTotalConflicts(): int
    {
        return count($this->conflictingSchedules) + count($this->conflictingImpediments);
    }

    public function hasScheduleConflicts(): bool
    {
        return $this->conflictingSchedules !== [];
    }

    public function hasImpedimentConflicts(): bool
    {
        return $this->conflictingImpediments !== [];
    }

    public function getFirstScheduleId(): ?int
    {
        return $this->conflictingSchedules[0]->id ?? null;
    }

    public function getFirstImpedimentId(): ?int
    {
        return $this->conflictingImpediments[0]->id ?? null;
    }
}
