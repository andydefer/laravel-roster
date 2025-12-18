<?php

declare(strict_types=1);


namespace Roster;

use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;
use Roster\Services\ScheduleService;

class RosterManager
{
    protected ScheduleService $scheduleService;

    protected AvailabilityService $availabilityService;

    protected ImpedimentService $impedimentService;

    public function __construct(
        ScheduleService $scheduleService,
        AvailabilityService $availabilityService,
        ImpedimentService $impedimentService
    ) {
        $this->scheduleService = $scheduleService;
        $this->availabilityService = $availabilityService;
        $this->impedimentService = $impedimentService;
    }

    /**
     * Access schedule-related features.
     */
    public function schedules(): ScheduleService
    {
        return $this->scheduleService;
    }

    /**
     * Access availability-related features.
     */
    public function availabilities(): AvailabilityService
    {
        return $this->availabilityService;
    }

    /**
     * Access impediment-related features.
     */
    public function impediments(): ImpedimentService
    {
        return $this->impedimentService;
    }
}
