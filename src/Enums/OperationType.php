<?php

declare(strict_types=1);

namespace Roster\Enums;

/**
 * Enumeration of supported CRUD operation types.
 *
 * Defines the four basic data operations with utility methods
 * to categorize operations and provide human-readable representations.
 */
enum OperationType: string
{
    case CREATE = 'create';
    case RETRIEVE = 'retrieve';
    case UPDATE = 'update';
    case DELETE = 'delete';

    /**
     * Determines if this operation modifies data.
     *
     * @return bool True for CREATE, UPDATE, or DELETE operations
     */
    public function isWriteOperation(): bool
    {
        return in_array($this, [self::CREATE, self::UPDATE, self::DELETE]);
    }

    /**
     * Determines if this operation only reads data.
     *
     * @return bool True for RETRIEVE operations
     */
    public function isReadOperation(): bool
    {
        return $this === self::RETRIEVE;
    }

    /**
     * Provides a human-readable display name for the operation.
     *
     * @return string Capitalized operation name
     */
    public function displayName(): string
    {
        return ucfirst($this->value);
    }
}
