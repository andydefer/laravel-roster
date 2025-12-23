<?php

declare(strict_types=1);

namespace Roster\Validation;

class ValidationResult
{
    private bool $isValid;

    /**
     * @var mixed[]
     */
    private array $violations;

    public function __construct(bool $isValid = true, array $violations = [])
    {
        $this->isValid = $isValid;
        $this->violations = $violations;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return mixed[]
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return $this->violations !== [];
    }

    public function merge(ValidationResult $validationResult): self
    {
        return new self(
            $this->isValid && $validationResult->isValid(),
            array_merge($this->violations, $validationResult->getViolations())
        );
    }

    public static function valid(): self
    {
        return new self(true);
    }

    public static function invalid(array $violations): self
    {
        return new self(false, $violations);
    }

    /**
     * @return array<string, bool|mixed[]>
     */
    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'violations' => $this->violations,
        ];
    }
}
