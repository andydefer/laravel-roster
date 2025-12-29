<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Mockery;
use RuntimeException;
use Roster\Commands\DebugRulesCommand;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\RuleScanner;
use Roster\Validation\Validator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tests\TestCase;

/**
 * Test suite for DebugRulesCommand functionality.
 */
final class DebugRulesCommandTest extends TestCase
{
    /**
     * Clean up Mockery after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that command can be instantiated with proper configuration.
     */
    public function test_command_can_be_instantiated(): void
    {
        // Arrange: Create command instance
        $debugRulesCommand = new DebugRulesCommand();

        // Assert: Verify command properties
        $this->assertSame('roster:debug-rules', $debugRulesCommand->getName());
        $this->assertSame('Debug Roster validation rules for entities', $debugRulesCommand->getDescription());

        // Assert: Verify command options
        $inputDefinition = $debugRulesCommand->getDefinition();
        $this->assertTrue($inputDefinition->hasArgument('entity'));
        $this->assertTrue($inputDefinition->hasOption('operation'));
        $this->assertTrue($inputDefinition->hasOption('property'));
        $this->assertTrue($inputDefinition->hasOption('show-methods'));
        $this->assertTrue($inputDefinition->hasOption('show-source'));
        $this->assertTrue($inputDefinition->hasOption('details'));
    }

    /**
     * Test that the command has correct input arguments definition.
     */
    public function test_command_has_correct_arguments(): void
    {
        // Arrange: Create command instance and get its definition
        $command = new DebugRulesCommand();
        $definition = $command->getDefinition();
        $arguments = $definition->getArguments();

        // Assert: Verify entity argument exists and is optional
        $this->assertCount(1, $arguments);
        $this->assertArrayHasKey('entity', $arguments);

        $entityArgument = $arguments['entity'];
        $this->assertInstanceOf(InputArgument::class, $entityArgument);
        $this->assertFalse($entityArgument->isRequired(), 'Entity argument should not be required');
        $this->assertSame('Entity class name or entity type (availability, schedule, impediment)', $entityArgument->getDescription());
    }

    /**
     * Test that the command has all expected options.
     */
    public function test_command_has_correct_options(): void
    {
        // Arrange: Create command instance and get its definition
        $command = new DebugRulesCommand();
        $definition = $command->getDefinition();
        $options = $definition->getOptions();

        // Assert: Verify all expected options exist
        $expectedOptions = [
            'operation',
            'property',
            'show-methods',
            'show-source',
            'details',
        ];

        foreach ($expectedOptions as $optionName) {
            $this->assertArrayHasKey($optionName, $options, "Option '$optionName' is missing");
        }

        // Assert: Verify custom options have correct configurations
        $operationOption = $options['operation'];
        $this->assertInstanceOf(InputOption::class, $operationOption);
        $this->assertSame('Filter by operation type (create, update, delete)', $operationOption->getDescription());
        $this->assertFalse($operationOption->isValueRequired());

        $propertyOption = $options['property'];
        $this->assertInstanceOf(InputOption::class, $propertyOption);
        $this->assertSame('Filter by specific property name', $propertyOption->getDescription());
        $this->assertFalse($propertyOption->isValueRequired());

        $showMethodsOption = $options['show-methods'];
        $this->assertInstanceOf(InputOption::class, $showMethodsOption);
        $this->assertSame('Display validation method details', $showMethodsOption->getDescription());
        $this->assertFalse($showMethodsOption->acceptValue());

        $showSourceOption = $options['show-source'];
        $this->assertInstanceOf(InputOption::class, $showSourceOption);
        $this->assertSame('Display rule source code location', $showSourceOption->getDescription());
        $this->assertFalse($showSourceOption->acceptValue());

        $detailsOption = $options['details'];
        $this->assertInstanceOf(InputOption::class, $detailsOption);
        $this->assertSame('Show all details including rule priorities and dependencies', $detailsOption->getDescription());
        $this->assertFalse($detailsOption->acceptValue());
    }

    /**
     * Test successful execution without entity argument.
     */
    public function test_command_executes_successfully_without_entity(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock scanner methods
        $mockRuleScanner->shouldReceive('scan')->andReturn([]);
        $mockRuleScanner->shouldReceive('instantiateRules')->andReturn([]);

        // Mock validator methods for all entities and operations
        foreach (EntityType::cases() as $entityType) {
            foreach (OperationType::cases() as $operation) {
                $mockValidator->shouldReceive('getRulesFor')
                    ->with($operation, $entityType)
                    ->andReturn([]);
            }
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act: Execute the command without entity argument
        $this->artisan('roster:debug-rules')
            ->assertExitCode(0);
    }

    /**
     * Test successful execution with entity argument.
     */
    public function test_command_executes_successfully_with_entity(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule with all required methods
        $mockRule = Mockery::mock();
        $mockRule->shouldReceive('getName')->andReturn('TestRule');
        $mockRule->shouldReceive('getPriority')->andReturn(100);
        $mockRule->shouldReceive('supports')->andReturn(true);
        $mockRule->shouldReceive('getProperties')->andReturn(['days', 'start_time', 'end_time']);

        // Mock validator for availability entity - ALL operations including RETRIEVE
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::CREATE, EntityType::AVAILABILITY)
            ->andReturn([$mockRule]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::UPDATE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::DELETE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::RETRIEVE, EntityType::AVAILABILITY)
            ->andReturn([]);

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act: Execute the command with entity argument
        $this->artisan('roster:debug-rules', ['entity' => 'availability'])
            ->assertExitCode(0);
    }

    /**
     * Test successful execution with operation filter.
     */
    public function test_command_executes_successfully_with_operation_filter(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule
        $mockRule = Mockery::mock();
        $mockRule->shouldReceive('getName')->andReturn('TestRule');
        $mockRule->shouldReceive('getPriority')->andReturn(100);
        $mockRule->shouldReceive('supports')->andReturn(true);
        $mockRule->shouldReceive('getProperties')->andReturn(['days', 'start_time', 'end_time']);

        // Mock validator for create operation only - mais la commande peut appeler RETRIEVE
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::CREATE, EntityType::AVAILABILITY)
            ->andReturn([$mockRule]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::RETRIEVE, EntityType::AVAILABILITY)
            ->andReturn([]);

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act: Execute the command with operation filter
        $this->artisan('roster:debug-rules', [
            'entity' => 'availability',
            '--operation' => 'create'
        ])->assertExitCode(0);
    }

    /**
     * Test successful execution with property filter.
     */
    public function test_command_executes_successfully_with_property_filter_simple(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule with properties
        $mockRule = Mockery::mock();
        $mockRule->shouldReceive('getName')->andReturn('TestRule');
        $mockRule->shouldReceive('getPriority')->andReturn(100);
        $mockRule->shouldReceive('supports')->andReturn(true);
        $mockRule->shouldReceive('getProperties')->andReturn(['days', 'start_time', 'end_time']);

        // Mock validator for availability entity - ALL operations
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::CREATE, EntityType::AVAILABILITY)
            ->andReturn([$mockRule]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::UPDATE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::DELETE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::RETRIEVE, EntityType::AVAILABILITY)
            ->andReturn([]);

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act: Execute the command with property filter
        $this->artisan('roster:debug-rules', [
            'entity' => 'availability',
            '--property' => 'days'
        ])->assertExitCode(0);
    }

    /**
     * Test successful execution with show-methods option.
     */
    public function test_command_executes_successfully_with_show_methods(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule
        $mockRule = Mockery::mock();
        $mockRule->shouldReceive('getName')->andReturn('TestRule');
        $mockRule->shouldReceive('getPriority')->andReturn(100);
        $mockRule->shouldReceive('supports')->andReturn(true);
        $mockRule->shouldReceive('getProperties')->andReturn(['days', 'start_time', 'end_time']);

        // Mock validator for availability entity - ALL operations
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::CREATE, EntityType::AVAILABILITY)
            ->andReturn([$mockRule]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::UPDATE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::DELETE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::RETRIEVE, EntityType::AVAILABILITY)
            ->andReturn([]);

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act: Execute the command with show-methods option
        $this->artisan('roster:debug-rules', [
            'entity' => 'availability',
            '--show-methods' => true
        ])->assertExitCode(0);
    }

    /**
     * Test successful execution with verbose option.
     */
    public function test_command_executes_successfully_with_verbose(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock scanner for verbose output
        $mockRuleScanner->shouldReceive('scan')->andReturn([]);
        $mockRuleScanner->shouldReceive('instantiateRules')->andReturn([]);

        // Mock validator for all entities and operations
        foreach (EntityType::cases() as $entityType) {
            foreach (OperationType::cases() as $operation) {
                $mockValidator->shouldReceive('getRulesFor')
                    ->with($operation, $entityType)
                    ->andReturn([]);
            }
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act: Execute the command with verbose option
        $this->artisan('roster:debug-rules', ['--verbose' => true])
            ->assertExitCode(0);
    }

    /**
     * Test graceful handling of invalid entity type.
     */
    public function test_command_handles_invalid_entity_type(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock validator for availability entity (default fallback) - ALL operations
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::CREATE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::UPDATE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::DELETE, EntityType::AVAILABILITY)
            ->andReturn([]);
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::RETRIEVE, EntityType::AVAILABILITY)
            ->andReturn([]);

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act: Execute the command with invalid entity
        $this->artisan('roster:debug-rules', ['entity' => 'invalid_entity'])
            ->assertExitCode(0);
    }

    /**
     * Test failure when scanner throws an exception.
     */
    public function test_command_fails_when_scanner_fails(): void
    {
        // Arrange: Prepare mock services with exception
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock scanner to throw exception
        $mockRuleScanner->shouldReceive('scan')
            ->andThrow(new RuntimeException('Scanner failed'));

        // Mock validator for all entities and operations (optional but good practice)
        foreach (EntityType::cases() as $entityType) {
            foreach (OperationType::cases() as $operation) {
                $mockValidator->shouldReceive('getRulesFor')
                    ->with($operation, $entityType)
                    ->andReturn([]);
            }
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act & Assert: Execute command and verify failure
        $this->artisan('roster:debug-rules')
            ->assertExitCode(1);
    }

    /**
     * Test failure when validator throws an exception.
     */
    public function test_command_fails_when_validator_fails(): void
    {
        // Arrange: Prepare mock services with exception
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock scanner
        $mockRuleScanner->shouldReceive('scan')->andReturn([]);
        $mockRuleScanner->shouldReceive('instantiateRules')->andReturn([]);

        // Mock validator to throw exception for ALL operations
        $mockValidator->shouldReceive('getRulesFor')
            ->andThrow(new RuntimeException('Validator failed'));

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Mock config to prevent null pointer exception
        config(['roster.cache.cache_file' => null]);

        // Act & Assert: Execute command and verify failure
        $this->artisan('roster:debug-rules')
            ->assertExitCode(1);
    }
}
