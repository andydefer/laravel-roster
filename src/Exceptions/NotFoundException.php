<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Exception;

/**
 * Exception thrown when a requested resource is not found.
 */
class NotFoundException extends Exception
{
    /**
     * Default error message.
     */
    protected $message = 'Resource not found';

    /**
     * Default HTTP status code.
     */
    protected $code = 404;

    /**
     * Create a new NotFoundException instance.
     *
     * @param string $message Custom error message
     * @param int $code Custom HTTP status code
     * @param Exception|null $previous Previous exception
     */
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        if ($message === '') {
            $message = $this->message;
        }

        if ($code === 0) {
            $code = $this->code;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Create a NotFoundException for a specific entity.
     *
     * @param string $entityType Type of entity (e.g., 'Availability', 'Schedule')
     * @param int|string $identifier Entity ID or identifier
     * @param Exception|null $exception Previous exception
     */
    public static function forEntity(string $entityType, $identifier, ?Exception $exception = null): self
    {
        $message = sprintf('%s with ID %s not found', $entityType, (string)$identifier);

        return new self($message, 404, $exception);
    }

    /**
     * Create a NotFoundException for a schedulable entity.
     *
     * @param string $entityType Type of entity
     * @param int $entityId Entity ID
     * @param string $schedulableType Type of schedulable
     * @param int $schedulableId Schedulable ID
     */
    public static function forSchedulableEntity(
        string $entityType,
        int $entityId,
        string $schedulableType,
        int $schedulableId
    ): self {
        $message = sprintf(
            '%s with ID %d not found for %s with ID %d',
            $entityType,
            $entityId,
            $schedulableType,
            $schedulableId
        );

        return new self($message);
    }

    /**
     * Create a NotFoundException for a relationship.
     *
     * @param string $parentEntity Parent entity type
     * @param int $parentId Parent entity ID
     * @param string $childEntity Child entity type
     * @param int $childId Child entity ID
     */
    public static function forRelationship(
        string $parentEntity,
        int $parentId,
        string $childEntity,
        int $childId
    ): self {
        $message = sprintf(
            '%s with ID %d does not have a %s with ID %d',
            $parentEntity,
            $parentId,
            $childEntity,
            $childId
        );

        return new self($message);
    }

    /**
     * Create a NotFoundException for availability.
     *
     * @param int $availabilityId Availability ID
     */
    public static function forAvailability(int $availabilityId): self
    {
        return self::forEntity('Availability', $availabilityId);
    }

    /**
     * Create a NotFoundException for schedule.
     *
     * @param int $scheduleId Schedule ID
     */
    public static function forSchedule(int $scheduleId): self
    {
        return self::forEntity('Schedule', $scheduleId);
    }

    /**
     * Create a NotFoundException for impediment.
     *
     * @param int $impedimentId Impediment ID
     */
    public static function forImpediment(int $impedimentId): self
    {
        return self::forEntity('Impediment', $impedimentId);
    }

    /**
     * Create a NotFoundException when no schedulable is set.
     */
    public static function forMissingSchedulable(): self
    {
        return new self('No schedulable model set. Call for() method first.');
    }

    /**
     * Create a NotFoundException for time slot.
     *
     * @param string $start Start datetime
     * @param string $end End datetime
     */
    public static function forTimeSlot(string $start, string $end): self
    {
        $message = sprintf('No availability found for time slot %s to %s', $start, $end);
        return new self($message);
    }

    /**
     * Convert the exception to an array.
     *
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'type' => 'not_found',
        ];
    }

    /**
     * Check if the exception is for a specific entity type.
     *
     * @param string $entityType Entity type to check
     */
    public function isForEntity(string $entityType): bool
    {
        return stripos($this->getMessage(), $entityType) !== false;
    }

    /**
     * Get the entity type from the exception message.
     *
     * @return string|null Entity type or null if not found
     */
    public function getEntityType(): ?string
    {
        $message = $this->getMessage();

        // Try to extract entity type from common patterns
        if (preg_match('/^(\w+) with ID/', $message, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get the entity ID from the exception message.
     *
     * @return int|null Entity ID or null if not found
     */
    public function getEntityId(): ?int
    {
        $message = $this->getMessage();

        if (preg_match('/ID (\d+)/', $message, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    /**
     * Check if this is a not found exception (for type comparison).
     *
     * @return bool Always true for this exception type
     */
    public function isNotFound(): bool
    {
        return true;
    }
}
