<?php

declare(strict_types=1);

namespace Roster\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Roster\Models\Schedule;

/**
 * Trait for models that can be attached to schedules.
 *
 * This trait can be used by package users to add schedule attachment
 * capabilities to their models (e.g., Room, Payment, User, etc.).
 */
trait AttachableToSchedules
{
    /**
     * Polymorphic many-to-many relationship to schedules.
     *
     * @return MorphToMany<Schedule>
     */
    public function attachedSchedules(): MorphToMany
    {
        return $this->morphToMany(
            related: Schedule::class,
            name: 'linkable',
            table: 'roster_schedule_links',
            foreignPivotKey: 'linkable_id',
            relatedPivotKey: 'schedule_id',
            parentKey: 'id',
            relatedKey: 'id'
        )->withPivot('metadata')->withTimestamps();
    }

    /**
     * Check if this model is attached to a specific schedule.
     *
     * @param Schedule|int $schedule Schedule instance or ID
     * @return bool True if attached
     */
    public function isAttachedToSchedule(Schedule|int $schedule): bool
    {
        $scheduleId = $schedule instanceof Schedule ? $schedule->id : $schedule;

        return $this->attachedSchedules()
            ->where('roster_schedule_links.schedule_id', $scheduleId)
            ->exists();
    }

    /**
     * Get all schedules with link metadata.
     *
     * @return Collection<int, Schedule>
     */
    public function attachedSchedulesWithLinkMetadata(): Collection
    {
        return $this->attachedSchedules()
            ->withPivot('metadata', 'created_at', 'updated_at')
            ->get();
    }

    /**
     * Get schedules with specific metadata criteria.
     *
     * @param string $metadataKey Metadata key to search for
     * @param mixed $metadataValue Value to match
     * @return Collection<int, Schedule>
     */
    public function attachedSchedulesWithMetadata(string $metadataKey, mixed $metadataValue): Collection
    {
        return $this->attachedSchedules()
            ->wherePivot('metadata', 'LIKE', '%"' . $metadataKey . '":"' . $metadataValue . '"%')
            ->get();
    }

    /**
     * Attach this model to a schedule with optional metadata.
     *
     * Note: This is a convenience method that uses the underlying
     * Eloquent relationship. For context-aware operations within
     * the Roster package, use the service methods instead.
     *
     * @param Schedule|int $schedule Schedule instance or ID
     * @param array|null $metadata Optional metadata for the relationship
     * @return void
     */
    public function attachToSchedule(Schedule|int $schedule, ?array $metadata = null): void
    {
        $scheduleId = $schedule instanceof Schedule ? $schedule->id : $schedule;

        $this->attachedSchedules()->attach($scheduleId, [
            'metadata' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
        ]);
    }

    /**
     * Detach this model from a schedule.
     *
     * Note: This is a convenience method that uses the underlying
     * Eloquent relationship. For context-aware operations within
     * the Roster package, use the service methods instead.
     *
     * @param Schedule|int $schedule Schedule instance or ID
     * @return void
     */
    public function detachFromSchedule(Schedule|int $schedule): void
    {
        $scheduleId = $schedule instanceof Schedule ? $schedule->id : $schedule;

        $this->attachedSchedules()->detach($scheduleId);
    }

    /**
     * Sync this model's schedule attachments.
     *
     * Note: This is a convenience method that uses the underlying
     * Eloquent relationship. For context-aware operations within
     * the Roster package, use the service methods instead.
     *
     * @param array<Schedule|int> $schedules Schedules to attach
     * @param array|null $metadata Optional metadata for the relationships
     * @return void
     */
    public function syncSchedules(array $schedules, ?array $metadata = null): void
    {
        $scheduleIds = [];
        foreach ($schedules as $schedule) {
            $scheduleIds[] = $schedule instanceof Schedule ? $schedule->id : $schedule;
        }

        $syncData = [];
        foreach ($scheduleIds as $scheduleId) {
            $syncData[$scheduleId] = [
                'metadata' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
            ];
        }

        $this->attachedSchedules()->sync($syncData);
    }
}
