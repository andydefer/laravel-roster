<?php

declare(strict_types=1);

namespace Roster\Validation\Exceptions;

use InvalidArgumentException;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\DTOs\ViolationData;
use Throwable;

/**
 * Exception thrown when entity validation fails.
 *
 * Provides structured access to validation violations with contextual information
 * about the operation and entity type involved in the validation failure.
 */
class ValidationFailedException extends InvalidArgumentException
{
    /** @var array<int, ViolationData> Validation violations */
    private array $violations;

    /** Type of operation that failed validation. */
    private OperationType $operationType;

    /** Type of entity that failed validation. */
    private EntityType $entityType;

    /**
     * Creates a new validation failure exception.
     *
     * @param array<int, ViolationData> $violations Validation violations
     * @param OperationType $operation Operation type that failed
     * @param EntityType $entityType Entity type that failed validation
     * @param string|null $message Custom exception message
     * @param int $code Exception code (defaults to 422 Unprocessable Entity)
     * @param Throwable|null $previous Previous exception
     */
    public function __construct(
        array $violations,
        OperationType $operation,
        EntityType $entityType,
        ?string $message = null,
        int $code = 422,
        ?Throwable $previous = null
    ) {
        $this->violations = $violations;
        $this->operationType = $operation;
        $this->entityType = $entityType;

        $message ??= $this->buildMessage($violations, $operation, $entityType);

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets all validation violations.
     *
     * @return array<int, ViolationData> Validation violations
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Gets the operation type that failed.
     *
     * @return OperationType Failed operation type
     */
    public function getOperation(): OperationType
    {
        return $this->operationType;
    }

    /**
     * Gets the entity type that failed validation.
     *
     * @return EntityType Failed entity type
     */
    public function getEntityType(): EntityType
    {
        return $this->entityType;
    }

    /**
     * Gets the first violation message.
     *
     * @return string|null First violation message or null if no violations
     */
    public function getFirstViolation(): ?string
    {
        if ($this->violations === []) {
            return null;
        }

        $firstViolation = $this->violations[0];
        return $firstViolation->getMessage();
    }

    /**
     * Converts exception to array representation.
     *
     * @return array{
     *     message: string,
     *     violations: array<array{
     *         field: string,
     *         rule: string,
     *         message: string
     *     }>,
     *     operation: string,
     *     entity_type: string
     * } Array representation
     */
    public function toArray(): array
    {
        $violationsArray = array_map(
            fn(ViolationData $violation): array => [
                'field' => $violation->getField(),
                'rule' => $violation->getRule(),
                'message' => $violation->getMessage(),
            ],
            $this->violations
        );

        return [
            'message' => $this->getMessage(),
            'violations' => $violationsArray,
            'operation' => $this->operationType->value,
            'entity_type' => $this->entityType->value,
        ];
    }

    /**
     * Converts exception to array representation with detailed rule descriptions.
     *
     * @return array{
     *     message: string,
     *     violations: array<array{
     *         field: string,
     *         rule: string,
     *         message: string,
     *         rule_description: string|null
     *     }>,
     *     operation: string,
     *     entity_type: string
     * } Array representation
     */
    public function toDetailedArray(): array
    {
        $violationsArray = array_map(
            fn(ViolationData $violation): array => $violation->toArray(),
            $this->violations
        );

        return [
            'message' => $this->getMessage(),
            'violations' => $violationsArray,
            'operation' => $this->operationType->value,
            'entity_type' => $this->entityType->value,
        ];
    }

    /**
     * Gets violations grouped by field with rule descriptions.
     *
     * @return array<string, array<array{
     *     rule: string,
     *     message: string,
     *     description: string|null
     * }>> Violations grouped by field
     */
    public function getViolationsWithDescriptions(): array
    {
        $grouped = [];

        foreach ($this->violations as $violation) {
            $field = $violation->getField();

            if (!isset($grouped[$field])) {
                $grouped[$field] = [];
            }

            $grouped[$field][] = [
                'rule' => $violation->getRule(),
                'message' => $violation->getMessage(),
                'description' => $violation->getRuleDescription(),
            ];
        }

        return $grouped;
    }

    /**
     * Formats error messages with rule descriptions for logging/debugging.
     *
     * @param bool $includeDescriptions Whether to include rule descriptions
     * @return string Formatted error message
     */
    public function getFormattedMessage(bool $includeDescriptions = false): string
    {
        $baseMessage = sprintf(
            '%s validation failed for %s',
            $this->operationType->displayName(),
            $this->entityType->displayName()
        );

        if ($this->violations === []) {
            return $baseMessage;
        }

        $messageLines = [$baseMessage . ':'];

        foreach ($this->violations as $violation) {
            $line = sprintf(
                '- [%s] %s: %s',
                $violation->getRule(),
                $violation->getField(),
                $violation->getMessage()
            );

            $messageLines[] = $line;

            if ($includeDescriptions && $violation->hasRuleDescription()) {
                $description = str_replace("\n", "\n  ", $violation->getRuleDescription());
                $messageLines[] = "  Description: " . $description;
                $messageLines[] = "";
            }
        }

        return implode("\n", $messageLines);
    }

    /**
     * Creates a validation exception from violations.
     *
     * @param array<int, ViolationData> $violations Validation violations
     * @param OperationType $operationType Operation type
     * @param EntityType $entityType Entity type
     * @return self New validation exception
     */
    public static function fromViolations(
        array $violations,
        OperationType $operationType,
        EntityType $entityType
    ): self {
        return new self(
            violations: $violations,
            operation: $operationType,
            entityType: $entityType
        );
    }

    /**
     * Builds a human-readable error message.
     *
     * @param array<int, ViolationData> $violations Validation violations
     * @param OperationType $operationType Operation type
     * @param EntityType $entityType Entity type
     * @return string Formatted error message
     */
    private function buildMessage(
        array $violations,
        OperationType $operationType,
        EntityType $entityType
    ): string {
        $baseMessage = sprintf(
            '%s validation failed for %s',
            $operationType->displayName(),
            $entityType->displayName()
        );

        if ($violations === []) {
            return $baseMessage;
        }

        $latestViolations = $this->keepLatestViolationPerField($violations);

        $messages = array_map(
            fn(ViolationData $violation): string => $violation->getMessage(),
            $latestViolations
        );

        return $baseMessage . ': ' . implode(' ; ', $messages);
    }

    /**
     * Keep only the latest violation per field.
     *
     * @param array<int, mixed> $violations
     * @return array<int, ViolationData>
     *
     * @throws InvalidArgumentException If an element is not a ViolationData instance
     */
    private function keepLatestViolationPerField(array $violations): array
    {
        $latest = [];

        foreach ($violations as $violation) {
            if (!$violation instanceof ViolationData) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected instance of ViolationData, got %s',
                        is_object($violation) ? get_class($violation) : gettype($violation)
                    )
                );
            }

            $latest[$violation->getField()] = $violation;
        }

        return array_values($latest);
    }
}
