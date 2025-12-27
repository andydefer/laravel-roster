<?php

declare(strict_types=1);

namespace Roster\Validation;

/**
 * Represents the outcome of a validation operation.
 *
 * Encapsulates validation status and any detected violations,
 * providing a clean interface for validation result inspection
 * and combination.
 */
class ValidationResult
{
    /**
     * Validation success status.
     */
    private bool $isValid;

    /**
     * Detected validation violations.
     *
     * @var array<string, string> Field => Error message pairs
     */
    private array $violations;

    /**
     * Initializes a validation result.
     *
     * @param bool $isValid Whether validation succeeded
     * @param array<string, string> $violations Validation violations
     */
    public function __construct(bool $isValid = true, array $violations = [])
    {
        $this->isValid = $isValid;
        $this->violations = $violations;
    }

    /**
     * Checks if validation succeeded.
     *
     * @return bool True if validation passed without violations
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Retrieves validation violations.
     *
     * @return array<string, string> Field => Error message pairs
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Checks if any violations were detected.
     *
     * @return bool True if violations exist
     */
    public function hasViolations(): bool
    {
        return !empty($this->violations);
    }

    /**
     * Merges this result with another validation result.
     *
     * @param ValidationResult $validationResult Result to merge
     * @return ValidationResult New combined validation result
     */
    public function merge(ValidationResult $validationResult): self
    {
        return new self(
            $this->isValid && $validationResult->isValid(),
            array_merge($this->violations, $validationResult->getViolations())
        );
    }

    /**
     * Creates a successful validation result.
     *
     * @return ValidationResult Valid result with no violations
     */
    public static function valid(): self
    {
        return new self(true);
    }

    /**
     * Creates a failed validation result with violations.
     *
     * @param array<string, string> $violations Validation violations
     * @return ValidationResult Invalid result with violations
     */
    public static function invalid(array $violations): self
    {
        return new self(false, $violations);
    }

    /**
     * Converts the validation result to array representation.
     *
     * @return array{is_valid: bool, violations: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'violations' => $this->violations,
        ];
    }
}
