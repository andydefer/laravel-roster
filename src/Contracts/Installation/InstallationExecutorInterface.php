<?php

declare(strict_types=1);

namespace Roster\Contracts\Installation;

/**
 * Interface for installation executors.
 */
interface InstallationExecutorInterface
{
    /**
     * Execute all installation steps.
     *
     * @param array $context Installation context
     * @return array Results from each step
     */
    public function executeSteps(array $context = []): array;

    /**
     * Add an installation step.
     *
     * @param InstallationStepInterface $step
     * @return self
     */
    public function addStep(InstallationStepInterface $step): self;

    /**
     * Get all registered steps.
     *
     * @return array<InstallationStepInterface>
     */
    public function getSteps(): array;
}
