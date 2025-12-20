<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use ReflectionMethod;
use Roster\Commands\InstallRosterCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Tests\TestCase;

/**
 * Interface pour capturer le buffer de sortie.
 */
interface OutputWithBuffer
{
    public function getOutput(): string;
}

/**
 * Trait réutilisable pour capturer le output.
 */
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

/**
 * Tests unitaires pour la commande d'installation du package Roster.
 */
final class InstallRosterCommandTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertStringContainsString('Routes (routes/roster.php)', $output->getOutput());
        $this->assertStringContainsString('Views (resources/views/vendor/roster)', $output->getOutput());
    }

    public function test_displays_success_message_with_next_steps(): void
    {
        $installRosterCommand = new InstallRosterCommand;
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
        $this->assertStringContainsString('Check routes/roster.php', $output->getOutput());
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
            ->onlyMethods(['confirm'])
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

    public function test_asks_for_confirmation_without_force_option(): void
    {
        $command = $this->getMockBuilder(InstallRosterCommand::class)
            ->onlyMethods(['confirm', 'call'])
            ->getMock();

        $command->expects($this->once())
            ->method('confirm')
            ->with('Continue?', true)
            ->willReturn(true);

        $command->expects($this->exactly(2))
            ->method('call')
            ->willReturn(0);

        $command->setLaravel($this->app);

        $arrayInput = new ArrayInput([], $command->getDefinition());
        $reflectionClass = new ReflectionClass($command);
        $reflectionProperty = $reflectionClass->getProperty('input');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($command, $arrayInput);

        /** @var Output&OutputWithBuffer $output */
        $output = new class extends Output implements OutputWithBuffer
        {
            use CapturesOutput;
        };

        $command->setOutput(new OutputStyle($arrayInput, $output));

        $command->handle();

        $this->assertStringContainsString('Installing Roster package', $output->getOutput());
    }
}
