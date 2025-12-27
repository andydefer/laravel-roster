<?php

declare(strict_types=1);

namespace Roster\Validation\Exceptions;

use InvalidArgumentException;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Throwable;

/**
 * Exception thrown when entity validation fails.
 *
 * Provides structured access to validation violations with contextual information
 * about the operation and entity type involved in the validation failure.
 */
class ValidationFailedException extends InvalidArgumentException
{
    /**
     * Validation violations keyed by field name.
     *
     * @var array<string, string|array<string>>
     */
    private array $violations;

    /**
     * Type of operation that failed validation.
     */
    private OperationType $operationType;

    /**
     * Type of entity that failed validation.
     */
    private EntityType $entityType;

    /**
     * Creates a new validation failure exception.
     *
     * @param array<string, string|array<string>> $violations Validation violations
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
     * @return array<string, string|array<string>> Validation violations
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

        $firstField = array_key_first($this->violations);
        $firstMessage = $this->violations[$firstField];

        if (is_array($firstMessage)) {
            return $firstMessage[0] ?? null;
        }

        return $firstMessage;
    }

    /**
     * Converts exception to array representation.
     *
     * @return array{
     *     message: string,
     *     violations: array<string, string|array<string>>,
     *     operation: string,
     *     entity_type: string
     * } Array representation
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'violations' => $this->violations,
            'operation' => $this->operationType->value,
            'entity_type' => $this->entityType->value,
        ];
    }

    /**
     * Creates a validation exception from violations.
     *
     * @param array<string, string|array<string>> $violations Validation violations
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
     * @param array<string, string|array<string>> $violations Validation violations
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

        $formattedViolations = [];

        foreach ($violations as $field => $messages) {
            if (is_array($messages)) {
                foreach ($messages as $message) {
                    $formattedViolations[] = sprintf('%s → %s', $field, $message);
                }
            } else {
                $formattedViolations[] = sprintf('%s → %s', $field, $messages);
            }
        }

        return $baseMessage . ': ' . implode(' ; ', $formattedViolations);
    }
}
