<?php

declare(strict_types=1);

namespace Roster\Validation\Exceptions;

use Throwable;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use InvalidArgumentException;

class ValidationFailedException extends InvalidArgumentException
{
    /**
     * @var array<string, mixed>
     */
    private array $violations;

    private OperationType $operationType;

    private EntityType $entityType;

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
     * @return array<string, mixed>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function getOperation(): OperationType
    {
        return $this->operationType;
    }

    public function getEntityType(): EntityType
    {
        return $this->entityType;
    }

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
     * @return array<string, mixed>
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

    public static function fromViolations(
        array $violations,
        OperationType $operationType,
        EntityType $entityType
    ): self {
        return new self($violations, $operationType, $entityType);
    }

    /**
     * Build a human-readable message including violations.
     */
    private function buildMessage(
        array $violations,
        OperationType $operationType,
        EntityType $entityType
    ): string {
        $base = sprintf(
            '%s validation failed for %s',
            $operationType->displayName(),
            $entityType->displayName()
        );

        if ($violations === []) {
            return $base;
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

        return $base . ': ' . implode(' ; ', $formattedViolations);
    }
}
