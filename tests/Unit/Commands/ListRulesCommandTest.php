<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Mockery;
use Roster\Commands\ListRulesCommand;
use Roster\Enums\EntityType;
use Roster\Enums\OperationType;
use Roster\Validation\RuleScanner;
use Roster\Validation\Validator;
use Roster\Contracts\Validation\RuleInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tests\TestCase;

/**
 * Test suite for ListRulesCommand functionality.
 */
final class ListRulesCommandTest extends TestCase
{
    /**
     * Clean up Mockery after each test.
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
        $command = new ListRulesCommand();

        // Assert: Verify command properties
        $this->assertSame('roster:list-rules', $command->getName());
        $this->assertSame('List Roster validation rules with names and descriptions', $command->getDescription());

        // Assert: Verify command options
        $inputDefinition = $command->getDefinition();
        $this->assertTrue($inputDefinition->hasArgument('entity'));
        $this->assertTrue($inputDefinition->hasOption('operation'));
        $this->assertTrue($inputDefinition->hasOption('simple'));
        $this->assertTrue($inputDefinition->hasOption('count'));
        $this->assertTrue($inputDefinition->hasOption('details'));
    }

    /**
     * Test that the command has correct input arguments definition.
     */
    public function test_command_has_correct_arguments(): void
    {
        // Arrange: Create command instance and get its definition
        $command = new ListRulesCommand();
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
        $command = new ListRulesCommand();
        $definition = $command->getDefinition();
        $options = $definition->getOptions();

        // Assert: Verify all expected options exist
        $expectedOptions = [
            'operation',
            'simple',
            'count',
            'details',
        ];

        foreach ($expectedOptions as $optionName) {
            $this->assertArrayHasKey($optionName, $options, sprintf("Option '%s' is missing", $optionName));
        }

        // Assert: Verify custom options have correct configurations
        $operationOption = $options['operation'];
        $this->assertInstanceOf(InputOption::class, $operationOption);
        $this->assertSame('Filter by operation type (create, update, delete)', $operationOption->getDescription());
        $this->assertFalse($operationOption->isValueRequired());

        $simpleOption = $options['simple'];
        $this->assertInstanceOf(InputOption::class, $simpleOption);
        $this->assertSame('Display in simple list format (no table)', $simpleOption->getDescription());
        $this->assertFalse($simpleOption->acceptValue());

        $countOption = $options['count'];
        $this->assertInstanceOf(InputOption::class, $countOption);
        $this->assertSame('Show only the count of rules', $countOption->getDescription());
        $this->assertFalse($countOption->acceptValue());

        $verboseOption = $options['details'];
        $this->assertInstanceOf(InputOption::class, $verboseOption);
        $this->assertSame('Show additional details like priority and supported operations', $verboseOption->getDescription());
        $this->assertFalse($verboseOption->acceptValue());
    }

    /**
     * Test successful execution without entity argument.
     */
    public function test_command_executes_successfully_without_entity(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock scanner
        $mockRuleScanner->shouldReceive('scan')->andReturn([]);

        // Create a mock rule implementing RuleInterface
        $mockRule = Mockery::mock(RuleInterface::class);
        $mockRule->shouldReceive('getName')->andReturn('Test Rule');
        $mockRule->shouldReceive('getDescription')->andReturn('Test rule description');
        $mockRule->shouldReceive('getPriority')->andReturn(100);

        // Mock validator to return rules for all entities and all operations
        foreach (EntityType::cases() as $entityType) {
            foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
                $mockValidator->shouldReceive('getRulesFor')
                    ->with($operation, $entityType)
                    ->andReturn([$mockRule]);
            }
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command without entity argument
        $this->artisan('roster:list-rules')
            ->expectsOutput('📋 All validation rules in Roster system')
            ->expectsOutput('📊 Scanner detected: 0 rules')
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

        // Create a mock rule
        $mockRule = Mockery::mock(RuleInterface::class);
        $mockRule->shouldReceive('getName')->andReturn('Test Rule');
        $mockRule->shouldReceive('getDescription')->andReturn('Test rule description');
        $mockRule->shouldReceive('getPriority')->andReturn(100);

        // Mock validator for availability entity for all operations
        foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
            $mockValidator->shouldReceive('getRulesFor')
                ->with($operation, EntityType::AVAILABILITY)
                ->andReturn([$mockRule]);
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command with entity argument
        $this->artisan('roster:list-rules', ['entity' => 'availability'])
            ->expectsOutput('📋 Listing validation rules for: availability')
            ->expectsOutput('📊 Entity Type: availability')
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
        $mockRule = Mockery::mock(RuleInterface::class);
        $mockRule->shouldReceive('getName')->andReturn('Test Rule');
        $mockRule->shouldReceive('getDescription')->andReturn('Test rule description');
        $mockRule->shouldReceive('getPriority')->andReturn(100);

        // Mock validator for create operation only (avec l'option --operation)
        $mockValidator->shouldReceive('getRulesFor')
            ->with(OperationType::CREATE, EntityType::AVAILABILITY)
            ->andReturn([$mockRule]);

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command with operation filter
        $this->artisan('roster:list-rules', [
            'entity' => 'availability',
            '--operation' => 'create'
        ])->assertExitCode(0);
    }

    /**
     * Test successful execution with simple option.
     */
    public function test_command_executes_successfully_with_simple_option(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule
        $mockRule = Mockery::mock(RuleInterface::class);
        $mockRule->shouldReceive('getName')->andReturn('Test Rule');
        $mockRule->shouldReceive('getDescription')->andReturn('Test rule description');
        $mockRule->shouldReceive('getPriority')->andReturn(100);

        // Mock validator for all operations (sans filtre d'opération)
        foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
            $mockValidator->shouldReceive('getRulesFor')
                ->with($operation, EntityType::AVAILABILITY)
                ->andReturn([$mockRule]);
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command with simple option
        $this->artisan('roster:list-rules', [
            'entity' => 'availability',
            '--simple' => true
        ])->assertExitCode(0);
    }

    /**
     * Test successful execution with count option.
     */
    public function test_command_executes_successfully_with_count_option(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule
        $mockRule = Mockery::mock(RuleInterface::class);
        $mockRule->shouldReceive('getName')->andReturn('Test Rule');
        $mockRule->shouldReceive('getDescription')->andReturn('Test rule description');
        $mockRule->shouldReceive('getPriority')->andReturn(100);

        // Mock validator - 1 rule pour chaque opération (CREATE, UPDATE, DELETE)
        // Quand on utilise --count sans --operation, la commande vérifie toutes les opérations
        foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
            $mockValidator->shouldReceive('getRulesFor')
                ->with($operation, EntityType::AVAILABILITY)
                ->andReturn([$mockRule]);
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command with count option
        $this->artisan('roster:list-rules', [
            'entity' => 'availability',
            '--count' => true
        ])->expectsOutput("Found 3 validation rule(s) for entity 'availability'")
            ->assertExitCode(0);
    }

    /**
     * Test successful execution with details option.
     */
    public function test_command_executes_successfully_with_verbose_option(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule
        $mockRule = Mockery::mock(RuleInterface::class);
        $mockRule->shouldReceive('getName')->andReturn('Test Rule');
        $mockRule->shouldReceive('getDescription')->andReturn('Test rule description');
        $mockRule->shouldReceive('getPriority')->andReturn(100);

        // Mock validator for all operations (avec --details mais sans --operation)
        foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
            $mockValidator->shouldReceive('getRulesFor')
                ->with($operation, EntityType::AVAILABILITY)
                ->andReturn([$mockRule]);
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command with details option
        $this->artisan('roster:list-rules', [
            'entity' => 'availability',
            '--details' => true
        ])->assertExitCode(0);
    }

    /**
     * Test command handles no rules found gracefully.
     */
    public function test_command_handles_no_rules_found(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock scanner
        $mockRuleScanner->shouldReceive('scan')->andReturn([]);

        // Mock validator with no rules
        foreach (EntityType::cases() as $entityType) {
            foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
                $mockValidator->shouldReceive('getRulesFor')
                    ->with($operation, $entityType)
                    ->andReturn([]);
            }
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command
        $this->artisan('roster:list-rules')
            ->expectsOutput('No validation rules found in the system.')
            ->assertExitCode(0);
    }

    /**
     * Test command handles invalid entity type gracefully.
     */
    public function test_command_handles_invalid_entity_type(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Mock validator for availability (default fallback) pour toutes les opérations
        foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
            $mockValidator->shouldReceive('getRulesFor')
                ->with($operation, EntityType::AVAILABILITY)
                ->andReturn([]);
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command with invalid entity
        $this->artisan('roster:list-rules', ['entity' => 'invalid_entity'])
            ->assertExitCode(0);
    }

    /**
     * Test command with count option and no entity.
     */
    public function test_command_executes_successfully_with_count_and_no_entity(): void
    {
        // Arrange: Prepare mock services
        $mockRuleScanner = Mockery::mock(RuleScanner::class);
        $mockValidator = Mockery::mock(Validator::class);

        // Create a mock rule
        $mockRule = Mockery::mock(RuleInterface::class);
        $mockRule->shouldReceive('getName')->andReturn('Test Rule');
        $mockRule->shouldReceive('getDescription')->andReturn('Test rule description');
        $mockRule->shouldReceive('getPriority')->andReturn(100);

        // Mock validator - 1 rule pour chaque entité et chaque opération
        foreach (EntityType::cases() as $entityType) {
            foreach ([OperationType::CREATE, OperationType::UPDATE, OperationType::DELETE] as $operation) {
                $mockValidator->shouldReceive('getRulesFor')
                    ->with($operation, $entityType)
                    ->andReturn([$mockRule]);
            }
        }

        $this->app->instance(RuleScanner::class, $mockRuleScanner);
        $this->app->instance(Validator::class, $mockValidator);

        // Act: Execute the command with count option and no entity
        $this->artisan('roster:list-rules', ['--count' => true])
            ->expectsOutput("Found 9 validation rule(s) in total")
            ->assertExitCode(0);
    }
}
