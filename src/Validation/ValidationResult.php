<?php

declare(strict_types=1);

namespace Roster\Validation;

use Roster\Validation\DTOs\ViolationData;

/**
 * Result of a validation operation.
 *
 * Contains information about whether validation succeeded or failed,
 * along with any violations that were detected.
 */
class ValidationResult
{
    /**
     * Creates a new validation result.
     *
     * @param bool $success Whether validation succeeded
     * @param array<int, ViolationData> $violations List of validation violations
     */
    public function __construct(
        private bool $success,
        private array $violations = []
    ) {}

    /**
     * Gets all validation violations.
     *
     * @return array<int, ViolationData> List of violations
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Checks if there are any violations.
     *
     * @return bool True if there are violations, false otherwise
     */
    public function hasViolations(): bool
    {
        return $this->violations !== [];
    }

    /**
     * Gets the number of violations.
     *
     * @return int Number of violations
     */
    public function countViolations(): int
    {
        return count($this->violations);
    }

    /**
     * Converts the result to an array representation.
     *
     * @param bool $includeRuleDescriptions Whether to include rule descriptions
     * @return array{
     *     success: bool,
     *     violations: array<array{
     *         field: string,
     *         rule: string,
     *         message: string,
     *         rule_description?: string|null
     *     }>
     * } Array representation
     */
    public function toArray(bool $includeRuleDescriptions = false): array
    {
        $violationsArray = array_map(
            function (ViolationData $violation) use ($includeRuleDescriptions) {
                $data = [
                    'field' => $violation->getField(),
                    'rule' => $violation->getRule(),
                    'message' => $violation->getMessage(),
                ];

                if ($includeRuleDescriptions) {
                    $data['rule_description'] = $violation->getRuleDescription();
                }

                return $data;
            },
            $this->violations
        );

        return [
            'success' => $this->success,
            'violations' => $violationsArray,
        ];
    }

    /**
     * Creates a successful validation result.
     *
     * @return self Successful validation result
     */
    public function isValid(): bool
    {
        return $this->success;
    }



    /**
     * Creates a failed validation result with violations.
     *
     * @param array<int, ViolationData> $violations Validation violations
     * @return self Failed validation result
     */
    public static function failed(array $violations): self
    {
        return new self(false, $violations);
    }
}
