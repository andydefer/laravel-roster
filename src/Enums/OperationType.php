<?php

declare(strict_types=1);

namespace Roster\Enums;

enum OperationType: string
{
    case CREATE = 'create';
    case RETRIEVE = 'retrive';
    case UPDATE = 'update';
    case DELETE = 'delete';

    public function isWriteOperation(): bool
    {
        return in_array($this, [self::CREATE, self::UPDATE, self::DELETE]);
    }

    public function isReadOperation(): bool
    {
        return $this === self::RETRIEVE;
    }

    public function displayName(): string
    {
        return ucfirst($this->value);
    }
}
