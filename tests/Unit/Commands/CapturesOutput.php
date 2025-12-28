<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

/**
 * Trait for capturing console output in command tests.
 *
 * This trait provides methods to capture and retrieve output written
 * by console commands during testing, allowing assertions on command output.
 */
trait CapturesOutput
{
    /**
     * Buffer containing captured output.
     */
    private string $buffer = '';

    /**
     * Write a message to the output buffer.
     *
     * @param string $message The message to write
     * @param bool $newline Whether to add a newline after the message
     */
    protected function doWrite(string $message, bool $newline): void
    {
        $this->buffer .= $message;

        if ($newline) {
            $this->buffer .= PHP_EOL;
        }
    }

    /**
     * Get the captured output.
     *
     * @return string The complete captured output
     */
    public function getOutput(): string
    {
        return $this->buffer;
    }
}
