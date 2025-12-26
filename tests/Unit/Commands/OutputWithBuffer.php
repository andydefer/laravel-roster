<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

interface OutputWithBuffer
{
    public function getOutput(): string;
}
