<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

/**
 * Interface for command output that provides a buffer for testing.
 *
 * This interface allows retrieving the buffered output from commands
 * for assertion in unit tests, ensuring that command output can be
 * properly verified without relying on external resources.
 */
interface OutputWithBuffer
{
    /**
     * Get the buffered output content.
     *
     * @return string The complete output captured during command execution
     */
    public function getOutput(): string;
}
