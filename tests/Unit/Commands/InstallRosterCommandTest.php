<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Mockery;
use Roster\Commands\InstallRosterCommand;
use Roster\Domain\Services\RosterInstallerService;
use Tests\TestCase;

final class InstallRosterCommandTest extends TestCase
{
    /**
     * Test that the command can be instantiated with correct properties.
     */
    public function test_command_can_be_instantiated(): void
    {
        // Arrange: Create command instance
        $installRosterCommand = new InstallRosterCommand;

        // Assert: Verify command name and description
        $this->assertSame('roster:install', $installRosterCommand->getName());
        $this->assertSame('Install the Roster package', $installRosterCommand->getDescription());
    }

    /**
     * Test that the command handles installation with force option.
     */
    public function test_handle_calls_installer_service_with_force_option(): void
    {
        // Arrange: Mock service expecting force flag
        $mockService = Mockery::mock(RosterInstallerService::class);
        $mockService->shouldReceive('install')
            ->once()
            ->withArgs(
                fn($command, $force): bool =>
                $command instanceof InstallRosterCommand && $force === true
            );

        // Act: Register mock and execute command with force option
        new InstallRosterCommand;
        $this->app->instance(RosterInstallerService::class, $mockService);

        // Assert: Command should execute successfully
        $this->artisan('roster:install', ['--force' => true])
            ->assertExitCode(0);
    }

    /**
     * Test that the command handles installation without force option.
     */
    public function test_handle_calls_installer_service_without_force_option(): void
    {
        // Arrange: Mock service expecting no force flag
        $mockService = Mockery::mock(RosterInstallerService::class);
        $mockService->shouldReceive('install')
            ->once()
            ->withArgs(
                fn($command, $force): bool =>
                $command instanceof InstallRosterCommand && $force === false
            );

        // Act: Register mock and execute command without force option
        new InstallRosterCommand;
        $this->app->instance(RosterInstallerService::class, $mockService);

        // Assert: Command should execute successfully
        $this->artisan('roster:install')
            ->assertExitCode(0);
    }
}
