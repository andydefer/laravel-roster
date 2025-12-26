<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Mockery;
use Roster\Commands\InstallRosterCommand;
use Roster\Domain\Services\RosterInstallerService;
use Tests\TestCase;

final class InstallRosterCommandTest extends TestCase
{
    public function test_command_can_be_instantiated(): void
    {
        $command = new InstallRosterCommand;
        $this->assertSame('roster:install', $command->getName());
        $this->assertSame('Install the Roster package', $command->getDescription());
    }

    public function test_handle_calls_installer_service_with_force_option(): void
    {
        $mockService = Mockery::mock(RosterInstallerService::class);
        $mockService->shouldReceive('install')
            ->once()
            ->withArgs(function ($command, $force) {
                return $command instanceof InstallRosterCommand && $force === true;
            });

        $command = new InstallRosterCommand;
        $this->app->instance(RosterInstallerService::class, $mockService);

        $this->artisan('roster:install', ['--force' => true])
            ->assertExitCode(0);
    }

    public function test_handle_calls_installer_service_without_force_option(): void
    {
        $mockService = Mockery::mock(RosterInstallerService::class);
        $mockService->shouldReceive('install')
            ->once()
            ->withArgs(function ($command, $force) {
                return $command instanceof InstallRosterCommand && $force === false;
            });

        $command = new InstallRosterCommand;
        $this->app->instance(RosterInstallerService::class, $mockService);

        $this->artisan('roster:install')
            ->assertExitCode(0);
    }
}
