<?php

namespace Roster;

use Roster\Services\AvailabilityService;
use Roster\Services\ScheduleService;

class RosterManager
{
    protected ScheduleService $scheduleService;

    protected AvailabilityService $availabilityService;

    public function __construct(ScheduleService $scheduleService, AvailabilityService $availabilityService)
    {
        $this->scheduleService = $scheduleService;
        $this->availabilityService = $availabilityService;
    }

    /**
     * Accès aux fonctionnalités Schedule
     */
    public function schedules(): ScheduleService
    {
        return $this->scheduleService;
    }

    /**
     * Accès aux fonctionnalités Availability
     */
    public function availabilities(): AvailabilityService
    {
        return $this->availabilityService;
    }
}
