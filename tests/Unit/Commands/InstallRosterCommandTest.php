<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;
use Roster\Commands\InstallRosterCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Tests\TestCase;

interface OutputWithBuffer
{
    public function getOutput(): string;
}

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

final class InstallRosterCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['roster_availabilities', 'roster_schedules', 'roster_impediments'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        DB::table('migrations')->truncate();
    }

    public function test_command_can_be_instantiated(): void
    {
        $installRosterCommand = new InstallRosterCommand;

        $this->assertSame('roster:install', $installRosterCommand->getName());
        $this->assertSame('Install the Roster package', $installRosterCommand->getDescription());
        $this->assertStringContainsString('force', $installRosterCommand->getDefinition()->getOption('force')->getName());
    }

    public function test_displays_correct_publishing_details(): void
    {
        $installRosterCommand = new InstallRosterCommand;
        $installRosterCommand->setLaravel($this->app);

        $reflectionMethod = new ReflectionMethod($installRosterCommand, 'displayPublishingDetails');
        $reflectionMethod->setAccessible(true);

        /** @var Output&OutputWithBuffer $output */
        $output = new class extends Output implements OutputWithBuffer
        {
            use CapturesOutput;
        };

        $installRosterCommand->setOutput(new OutputStyle(new ArrayInput([]), $output));

        $reflectionMethod->invoke($installRosterCommand);

        $this->assertStringContainsString('Configuration (config/roster.php)', $output->getOutput());
        $this->assertStringContainsString('Database migrations (roster_* tables)', $output->getOutput());
    }

    public function test_displays_success_message_with_next_steps(): void
    {
        // On mocke le call() pour éviter l'erreur lors du test
        $installRosterCommand = $this->getMockBuilder(InstallRosterCommand::class)
            ->onlyMethods(['call'])
            ->getMock();

        $installRosterCommand->expects($this->once())
            ->method('call')
            ->with('roster:cache-rules', ['--force' => true])
            ->willReturn(0);

        $installRosterCommand->setLaravel($this->app);

        $reflectionMethod = new ReflectionMethod($installRosterCommand, 'displaySuccessMessage');
        $reflectionMethod->setAccessible(true);

        /** @var Output&OutputWithBuffer $output */
        $output = new class extends Output implements OutputWithBuffer
        {
            use CapturesOutput;
        };

        $installRosterCommand->setOutput(new OutputStyle(new ArrayInput([]), $output));

        $reflectionMethod->invoke($installRosterCommand);

        $this->assertStringContainsString('Roster package installed successfully!', $output->getOutput());
        $this->assertStringContainsString('Review config/roster.php', $output->getOutput());
        $this->assertStringContainsString('Add the HasRoster trait', $output->getOutput());
        $this->assertStringContainsString('Use the facades', $output->getOutput());
    }

    public function test_private_methods_return_correct_values(): void
    {
        $installRosterCommand = new InstallRosterCommand;
        $installRosterCommand->setLaravel($this->app);

        $reflectionMethod = new ReflectionMethod($installRosterCommand, 'shouldSkipConfirmation');
        $reflectionMethod->setAccessible(true);

        $inputDefinition = new InputDefinition([
            new InputOption('force'),
        ]);

        $inputWithForce = new ArrayInput(['--force' => true], $inputDefinition);

        $reflectionClass = new ReflectionClass($installRosterCommand);
        $reflectionProperty = $reflectionClass->getProperty('input');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($installRosterCommand, $inputWithForce);

        $this->assertTrue($reflectionMethod->invoke($installRosterCommand));

        $inputWithoutForce = new ArrayInput([], $inputDefinition);
        $reflectionProperty->setValue($installRosterCommand, $inputWithoutForce);

        $this->assertFalse($reflectionMethod->invoke($installRosterCommand));
    }

    public function test_handles_cancellation_gracefully(): void
    {
        $command = $this->getMockBuilder(InstallRosterCommand::class)
            ->onlyMethods(['confirm', 'call'])
            ->getMock();

        $command->expects($this->once())
            ->method('confirm')
            ->with('Continue?', true)
            ->willReturn(false);

        $command->setLaravel($this->app);

        /** @var Output&OutputWithBuffer $output */
        $output = new class extends Output implements OutputWithBuffer
        {
            use CapturesOutput;
        };

        $command->setOutput(new OutputStyle(new ArrayInput([], $command->getDefinition()), $output));

        $arrayInput = new ArrayInput([], $command->getDefinition());
        $reflectionClass = new ReflectionClass($command);
        $reflectionProperty = $reflectionClass->getProperty('input');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($command, $arrayInput);

        $command->handle();

        $this->assertStringContainsString('Installation cancelled.', $output->getOutput());
    }

    public function test_completes_successfully_with_force_option(): void
    {
        $this->artisan('roster:install', ['--force' => true])
            ->expectsOutput('🚀 Installing Roster package...')
            ->expectsOutput('📤 Publishing resources...')
            ->expectsOutput('📊 Running migrations...')
            ->assertExitCode(0);
    }

    public function test_it_skips_migrations_if_roster_migrations_already_exist(): void
    {
        DB::table('migrations')->insert([
            'migration' => '2024_01_01_000001_create_roster_availabilities_table',
            'batch' => 1,
        ]);

        Schema::create('roster_availabilities', function ($table): void {
            $table->id();
        });

        $this->artisan('roster:install', ['--force' => true])
            ->expectsOutput('🚀 Installing Roster package...')
            ->expectsOutput('📤 Publishing resources...')
            ->expectsOutput('⚠️ Roster tables already exist. Skipping migrations.')
            ->doesntExpectOutput('📊 Running migrations...')
            ->assertExitCode(0);
    }

    public function test_it_skips_migrations_if_roster_tables_exist(): void
    {
        Schema::create('roster_availabilities', function ($table): void {
            $table->id();
        });

        $this->artisan('roster:install', ['--force' => true])
            ->expectsOutput('🚀 Installing Roster package...')
            ->expectsOutput('📤 Publishing resources...')
            ->expectsOutput('⚠️ Roster tables already exist. Skipping migrations.')
            ->doesntExpectOutput('📊 Running migrations...')
            ->assertExitCode(0);
    }
}
