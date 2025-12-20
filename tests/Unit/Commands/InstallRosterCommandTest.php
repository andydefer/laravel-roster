<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Roster\Commands\InstallRosterCommand;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Input\ArrayInput;
use Illuminate\Console\OutputStyle;
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
 * Unit tests for the InstallRosterCommand.
 */
class InstallRosterCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_can_be_instantiated(): void
    {
        $command = new InstallRosterCommand();

        $this->assertSame('roster:install', $command->getName());
        $this->assertSame('Install the Roster package', $command->getDescription());
        $this->assertStringContainsString('force', $command->getDefinition()->getOption('force')->getName());
    }

    public function test_displays_correct_publishing_details(): void
    {
        $command = new InstallRosterCommand();
        $command->setLaravel($this->app);

        $method = new \ReflectionMethod($command, 'displayPublishingDetails');
        $method->setAccessible(true);

        /** @var Output&OutputWithBuffer $output */
        $output = new class extends Output implements OutputWithBuffer {
            use CapturesOutput;
        };

        $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

        $method->invoke($command);

        $this->assertStringContainsString('Configuration (config/roster.php)', $output->getOutput());
        $this->assertStringContainsString('Database migrations (roster_* tables)', $output->getOutput());
        $this->assertStringContainsString('Routes (routes/roster.php)', $output->getOutput());
        $this->assertStringContainsString('Views (resources/views/vendor/roster)', $output->getOutput());
    }

    public function test_displays_success_message_with_next_steps(): void
    {
        $command = new InstallRosterCommand();
        $command->setLaravel($this->app);

        $method = new \ReflectionMethod($command, 'displaySuccessMessage');
        $method->setAccessible(true);

        /** @var Output&OutputWithBuffer $output */
        $output = new class extends Output implements OutputWithBuffer {
            use CapturesOutput;
        };

        $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

        $method->invoke($command);

        $this->assertStringContainsString('Roster package installed successfully!', $output->getOutput());
        $this->assertStringContainsString('Review config/roster.php', $output->getOutput());
        $this->assertStringContainsString('Add the HasRoster trait', $output->getOutput());
        $this->assertStringContainsString('Use the facades', $output->getOutput());
        $this->assertStringContainsString('Check routes/roster.php', $output->getOutput());
    }

    public function test_private_methods_return_correct_values(): void
    {
        $command = new InstallRosterCommand();
        $command->setLaravel($this->app);

        $method = new \ReflectionMethod($command, 'shouldSkipConfirmation');
        $method->setAccessible(true);

        $inputDefinition = new \Symfony\Component\Console\Input\InputDefinition([
            new \Symfony\Component\Console\Input\InputOption('force'),
        ]);

        $inputWithForce = new ArrayInput(['--force' => true], $inputDefinition);

        $reflection = new \ReflectionClass($command);
        $inputProperty = $reflection->getProperty('input');
        $inputProperty->setAccessible(true);
        $inputProperty->setValue($command, $inputWithForce);

        $this->assertTrue($method->invoke($command));

        $inputWithoutForce = new ArrayInput([], $inputDefinition);
        $inputProperty->setValue($command, $inputWithoutForce);

        $this->assertFalse($method->invoke($command));
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
        $output = new class extends Output implements OutputWithBuffer {
            use CapturesOutput;
        };

        $command->setOutput(new OutputStyle(new ArrayInput([], $command->getDefinition()), $output));

        $input = new ArrayInput([], $command->getDefinition());
        $reflection = new \ReflectionClass($command);
        $inputProperty = $reflection->getProperty('input');
        $inputProperty->setAccessible(true);
        $inputProperty->setValue($command, $input);

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

        $input = new ArrayInput([], $command->getDefinition());
        $reflection = new \ReflectionClass($command);
        $inputProperty = $reflection->getProperty('input');
        $inputProperty->setAccessible(true);
        $inputProperty->setValue($command, $input);

        /** @var Output&OutputWithBuffer $output */
        $output = new class extends Output implements OutputWithBuffer {
            use CapturesOutput;
        };

        $command->setOutput(new OutputStyle($input, $output));

        $command->handle();

        $this->assertStringContainsString('Installing Roster package', $output->getOutput());
    }
}
