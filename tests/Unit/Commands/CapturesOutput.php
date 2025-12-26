<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

trait CapturesOutput
{
    private string $buffer = '';

    protected function doWrite(string $message, bool $newline): void
    {
        $this->buffer .= $message;
        if ($newline) {
            $this->buffer .= PHP_EOL;
        }
    }

    public function getOutput(): string
    {
        return $this->buffer;
    }
}
