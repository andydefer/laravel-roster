<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/**
 * Bootstrap file for testing environment.
 *
 * This file is responsible for:
 *  - Loading Composer autoload
 *  - Defining constants needed for tests
 *
 * @package Roster\Tests
 */

if (!defined('TESTING')) {
    define('TESTING', true);
}
