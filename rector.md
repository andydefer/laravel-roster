# Rector Refactoring Report
*Generated: ven. 26 déc. 2025 16:10:38 WAT*


3 files with changes
====================

1) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/AvailabilityRepositoryInterface.php:15

    ---------- begin diff ----------
@@ @@
     /**
      * Get a query builder for availabilities of a schedulable resource.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param string|null $type Optional availability type filter
      * @return Builder<Availability> Query builder for availabilities
      */
-    public function findForSchedulable(Model $schedulable, ?string $type = null): Builder;
+    public function findForSchedulable(Model $model, ?string $type = null): Builder;

     /**
      * Get all availabilities for a schedulable resource within a date range.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $start Start date of the range
      * @param Carbon $end End date of the range
      * @param string|null $type Optional availability type filter
      * @return Collection<int, Availability> Collection of availabilities
      */
-    public function getForDateRange(Model $schedulable, Carbon $start, Carbon $end, ?string $type = null): Collection;
+    public function getForDateRange(Model $model, Carbon $start, Carbon $end, ?string $type = null): Collection;

     /**
      * Find a specific availability that covers a time slot.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $start Start time of the slot
      * @param Carbon $end End time of the slot
      * @param string|null $type Optional availability type filter
      * @return Availability|null The availability covering the slot, or null if not found
      */
-    public function getAvailabilityForTimeSlot(Model $schedulable, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
+    public function getAvailabilityForTimeSlot(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;

     /**
      * Get all availabilities for a specific date.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $date The date to check
      * @param string|null $type Optional availability type filter
      * @return Collection<int, Availability> Collection of availabilities for the date
      */
-    public function getForDate(Model $schedulable, Carbon $date, ?string $type = null): Collection;
+    public function getForDate(Model $model, Carbon $date, ?string $type = null): Collection;

     /**
      * Check if an availability is valid for a specific date.
@@ @@
     /**
      * Find an availability for a time slot with conflict detection information.
      *
-     * @param Model $schedulable The schedulable resource model
+     * @param Model $model The schedulable resource model
      * @param Carbon $start Start time of the slot
      * @param Carbon $end End time of the slot
      * @param string|null $type Optional availability type filter
      * @return Availability|null The availability with conflict information, or null if not found
      */
-    public function findForTimeSlotWithConflictInfo(Model $schedulable, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
+    public function findForTimeSlotWithConflictInfo(Model $model, Carbon $start, Carbon $end, ?string $type = null): ?Availability;
 }
    ----------- end diff -----------

Applied rules:
 * RenameParamToMatchTypeRector


2) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ImpedimentRepositoryInterface.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Contracts\Repository;

+use Roster\Models\Impediment;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@
      *
      * @param int $availabilityId The ID of the availability
      * @param Carbon $from Starting date for future impediments
-     * @return Collection<int, \Roster\Models\Impediment> Collection of future impediments
+     * @return Collection<int, Impediment> Collection of future impediments
      */
     public function getFutureImpediments(
         int $availabilityId,
    ----------- end diff -----------

Applied rules:


3) /home/andy-kani/pro/sites/packages/laravel-roster/src/Contracts/Repository/ScheduleRepositoryInterface.php:4

    ---------- begin diff ----------
@@ @@

 namespace Roster\Contracts\Repository;

+use Roster\Models\Schedule;
 use Illuminate\Database\Eloquent\Builder;
 use Illuminate\Support\Carbon;
 use Illuminate\Support\Collection;
@@ @@
      *
      * @param int $availabilityId The ID of the availability
      * @param Carbon $from Starting date for future schedules
-     * @return Collection<int, \Roster\Models\Schedule> Collection of future schedules
+     * @return Collection<int, Schedule> Collection of future schedules
      */
     public function getFutureSchedules(
         int $availabilityId,
@@ @@
      * @param Carbon $start Start date of the range
      * @param Carbon $end End date of the range
      * @param array<string, mixed> $filters Additional filters to apply
-     * @return Collection<int, \Roster\Models\Schedule> Collection of schedules
+     * @return Collection<int, Schedule> Collection of schedules
      */
     public function getForDateRange(
         int $schedulableId,
    ----------- end diff -----------

Applied rules:


 [OK] 3 files would have been changed (dry-run) by Rector                                                               

