<?php

declare(strict_types=1);

namespace Roster\Contracts\Installation;

/**
 * Contract for installation steps in the Roster package.
 */
interface InstallationStepInterface
{
    /**
     * Execute the installation step.
     *
     * @return bool True if successful, false otherwise
     */
    public function execute(): bool;

    /**
     * Get the step name for display purposes.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the step description for user information.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Check if this step should be executed.
     *
     * @param array $context Installation context
     * @return bool
     */
    public function shouldExecute(array $context = []): bool;
}
