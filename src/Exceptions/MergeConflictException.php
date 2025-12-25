<?php

declare(strict_types=1);

namespace Roster\Exceptions;

use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Models\Availability;
use Roster\Validation\Exceptions\ValidationFailedException;

/**
 * Exception thrown when an availability merge would be dangerous due to existing dependencies.
 */
class MergeConflictException extends ValidationFailedException
{
    private int $existingAvailabilityId;

    private int $newAvailabilityId;

    /**
     * @var mixed[]
     */
    private array $dependencies;

    public function __construct(
        int $existingAvailabilityId,
        int $newAvailabilityId,
        array $dependencies = [],
        ?string $message = null
    ) {
        $this->existingAvailabilityId = $existingAvailabilityId;
        $this->newAvailabilityId = $newAvailabilityId;
        $this->dependencies = $dependencies;

        $message ??= $this->buildMessage($dependencies);

        parent::__construct(
            violations: ['merge' => $message],
            operation: OperationType::CREATE,
            entityType: EntityType::AVAILABILITY
        );
    }

    public static function fromAvailabilities(
        Availability $existing,
        ?Availability $new = null,
        array $dependencies = []
    ): self {
        return new self(
            $existing->id,
            $new?->id,
            $dependencies
        );
    }

    public function getExistingAvailabilityId(): int
    {
        return $this->existingAvailabilityId;
    }

    public function getNewAvailabilityId(): int
    {
        return $this->newAvailabilityId;
    }

    /**
     * @return mixed[]
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function hasDependencies(): bool
    {
        return $this->dependencies !== [];
    }

    /**
     * @param array<string, mixed> $dependencies
     */
    private function buildMessage(array $dependencies): string
    {
        if ($dependencies === []) {
            return 'Cannot merge availabilities due to unknown conflicts';
        }

        $messages = [];

        if (isset($dependencies['schedules_count']) && $dependencies['schedules_count'] > 0) {
            $messages[] = sprintf(
                '%d schedule(s) depend on existing availability',
                $dependencies['schedules_count']
            );
        }

        if (isset($dependencies['impediments_count']) && $dependencies['impediments_count'] > 0) {
            $messages[] = sprintf(
                '%d impediment(s) depend on existing availability',
                $dependencies['impediments_count']
            );
        }

        if (isset($dependencies['conflicting_days'])) {
            $messages[] = 'Conflicting days between existing and new availability';
        }

        return 'Cannot merge availabilities: ' . implode(', ', $messages);
    }

    /**
     * @return array<string, string|int|mixed[]>
     */
    public function toDetailedArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'existing_availability_id' => $this->existingAvailabilityId,
            'new_availability_id' => $this->newAvailabilityId,
            'dependencies' => $this->dependencies,
            'operation' => OperationType::CREATE->value,
            'entity_type' => EntityType::AVAILABILITY->value,
        ];
    }
}
