<?php

declare(strict_types=1);

namespace Roster\Validation\DTOs;

/**
 * Data Transfer Object for validation violation information.
 *
 * Encapsulates details about a specific validation failure including
 * the field, rule, message, and optional rule description.
 */
class ViolationData
{
    public function __construct(
        private readonly string $field,
        private readonly string $message,
        private readonly ?string $rule = null,
        private readonly ?string $ruleDescription = null,
    ) {}

    /**
     * Gets the field name where the violation occurred.
     *
     * @return string Field name
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Gets the name of the rule that triggered the violation.
     *
     * @return string Rule name, defaults to 'unknown' if not specified
     */
    public function getRule(): string
    {
        return $this->rule ?? 'unknown';
    }

    /**
     * Gets the violation message explaining the failure.
     *
     * @return string Violation message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Gets the detailed description of the rule that was violated.
     *
     * @return string|null Rule description or null if not available
     */
    public function getRuleDescription(): ?string
    {
        return $this->ruleDescription;
    }

    /**
     * Checks if a rule description is available.
     *
     * @return bool True if rule description exists and is not empty
     */
    public function hasRuleDescription(): bool
    {
        return $this->ruleDescription !== null && $this->ruleDescription !== '';
    }

    /**
     * Converts the violation data to an array representation.
     *
     * @return array{
     *     field: string,
     *     rule: string,
     *     message: string,
     *     rule_description: string|null
     * } Array representation
     */
    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'rule' => $this->rule,
            'message' => $this->message,
            'rule_description' => $this->ruleDescription,
        ];
    }
}
