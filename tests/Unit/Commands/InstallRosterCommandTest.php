<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Roster\Commands\InstallRosterCommand;
use Tests\TestCase;

/**
 * Unit tests for the InstallRosterCommand.
 */
class InstallRosterCommandTest extends TestCase
{
    use RefreshDatabase;

    private InstallRosterCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new InstallRosterCommand();
        $this->command->setLaravel($this->app);
    }

    /** @test */
    public function it_should_skip_confirmation_when_force_option_is_used(): void
    {
        // Test via reflection since method is private
        $method = new \ReflectionMethod($this->command, 'shouldSkipConfirmation');
        $method->setAccessible(true);

        $this->command->input = new \Symfony\Component\Console\Input\ArrayInput([
            '--force' => true
        ]);

        $this->assertTrue($method->invoke($this->command));
    }

    /** @test */
    public function it_should_not_skip_confirmation_when_force_option_is_not_used(): void
    {
        $method = new \ReflectionMethod($this->command, 'shouldSkipConfirmation');
        $method->setAccessible(true);

        $this->command->input = new \Symfony\Component\Console\Input\ArrayInput([]);

        $this->assertFalse($method->invoke($this->command));
    }

    /** @test */
    public function it_displays_correct_publishing_details(): void
    {
        $this->command->output = new \Symfony\Component\Console\Output\BufferedOutput();

        $method = new \ReflectionMethod($this->command, 'displayPublishingDetails');
        $method->setAccessible(true);

        $method->invoke($this->command);

        $output = $this->command->output->fetch();

        $this->assertStringContainsString('Configuration (config/roster.php)', $output);
        $this->assertStringContainsString('Database migrations (roster_* tables)', $output);
        $this->assertStringContainsString('Routes (routes/roster.php)', $output);
        $this->assertStringContainsString('Views (resources/views/vendor/roster)', $output);
    }

    /** @test */
    public function it_publishes_resources_successfully(): void
    {
        Artisan::shouldReceive('call')
            ->with('vendor:publish', \Mockery::any())
            ->once()
            ->andReturn(0);

        $this->command->output = new \Symfony\Component\Console\Output\BufferedOutput();

        $method = new \ReflectionMethod($this->command, 'publishResources');
        $method->setAccessible(true);

        $method->invoke($this->command);

        $output = $this->command->output->fetch();
        $this->assertStringContainsString('Publishing resources...', $output);
    }

    /** @test */
    public function it_runs_migrations_successfully(): void
    {
        Artisan::shouldReceive('call')
            ->with('migrate', [])
            ->once()
            ->andReturn(0);

        $this->command->output = new \Symfony\Component\Console\Output\BufferedOutput();

        $method = new \ReflectionMethod($this->command, 'runMigrations');
        $method->setAccessible(true);

        $method->invoke($this->command);

        $output = $this->command->output->fetch();
        $this->assertStringContainsString('Running migrations...', $output);
    }

    /** @test */
    public function it_displays_success_message_with_next_steps(): void
    {
        $this->command->output = new \Symfony\Component\Console\Output\BufferedOutput();

        $method = new \ReflectionMethod($this->command, 'displaySuccessMessage');
        $method->setAccessible(true);

        $method->invoke($this->command);

        $output = $this->command->output->fetch();

        $this->assertStringContainsString('Roster package installed successfully!', $output);
        $this->assertStringContainsString('Review config/roster.php', $output);
        $this->assertStringContainsString('Add the HasRoster trait', $output);
        $this->assertStringContainsString('Use the facades', $output);
        $this->assertStringContainsString('Check routes/roster.php', $output);
    }

    /** @test */
    public function it_handles_cancellation_gracefully(): void
    {
        $this->command->input = new \Symfony\Component\Console\Input\ArrayInput([]);
        $this->command->output = new \Symfony\Component\Console\Output\BufferedOutput();

        // Mock confirm to return false
        $this->command->confirm = function ($question, $default = false) {
            return false;
        };

        $this->command->handle();

        $output = $this->command->output->fetch();
        $this->assertStringContainsString('Installation cancelled.', $output);
    }

    /** @test */
    public function it_completes_full_installation_with_force_option(): void
    {
        $this->artisan('roster:install', ['--force' => true])
            ->expectsOutput('🚀 Installing Roster package...')
            ->expectsOutput('📤 Publishing resources...')
            ->expectsOutput('📊 Running migrations...')
            ->assertExitCode(0);
    }
}
