<?php
// ==== src/RosterManager.php ====

namespace Roster;

use Roster\Services\ScheduleService;
use Roster\Services\AvailabilityService;
use Roster\Services\ImpedimentService;

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

    /**
     * Accès aux fonctionnalités Impediment
     */
    public function impediments(): ImpedimentService
    {
        return $this->impedimentService;
    }
}
