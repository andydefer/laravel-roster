<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Roster\RosterServiceProvider;
use Roster\Contracts\Validation\ValidatorInterface;

/**
 * Test suite for RosterServiceProvider service registration.
 *
 * Validates that all required services are properly registered
 * and bound in the Laravel service container.
 */
final class RosterServiceProviderTest extends TestCase
{
    /**
     * Test that the service provider registers and binds all required services.
     */
    public function test_service_provider_registers_and_binds_services(): void
    {
        // Arrange: Create service provider instance
        $provider = new RosterServiceProvider($this->app);

        // Act: Register and boot the service provider
        $provider->register();
        $provider->boot();

        // Assert: All required services should be bound in the container
        $this->assertTrue($this->app->bound(ValidatorInterface::class));
        $this->assertTrue($this->app->bound('roster.availability'));
        $this->assertTrue($this->app->bound('roster.schedule'));
        $this->assertTrue($this->app->bound('roster.impediment'));
    }
}
