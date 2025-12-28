<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Support\Facades\File;
use Mockery;
use RuntimeException;
use Roster\Commands\CacheRulesCommand;
use Roster\Domain\DTOs\CacheStats;
use Roster\Domain\Services\CacheRulesService;
use Tests\TestCase;

final class CacheRulesCommandTest extends TestCase
{
    /**
     * Path to the cache file used in tests.
     */
    private string $cacheFilePath;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheFilePath = storage_path('framework/cache/roster_rules.php');

        $this->deleteCacheFileIfExists();
    }

    /**
     * Clean up test environment.
     */
    protected function tearDown(): void
    {
        $this->deleteCacheFileIfExists();
        Mockery::close();

        parent::tearDown();
    }

    /**
     * Test that command can be instantiated with proper configuration.
     */
    public function test_command_can_be_instantiated(): void
    {
        // Arrange: Create command instance
        $cacheRulesCommand = new CacheRulesCommand();

        // Assert: Verify command properties
        $this->assertSame('roster:cache-rules', $cacheRulesCommand->getName());
        $this->assertSame('Manage Roster validation rules cache', $cacheRulesCommand->getDescription());

        // Assert: Verify command options
        $inputDefinition = $cacheRulesCommand->getDefinition();
        $this->assertTrue($inputDefinition->hasOption('clear'));
        $this->assertTrue($inputDefinition->hasOption('force'));
        $this->assertTrue($inputDefinition->hasOption('show'));
        $this->assertTrue($inputDefinition->hasOption('list'));
    }

    /**
     * Test successful generation of cache rules.
     */
    public function test_command_generates_cache_successfully(): void
    {
        // Arrange: Prepare mock service with cache stats
        $cacheStats = new CacheStats(
            path: $this->cacheFilePath,
            rulesCount: 1,
            sizeBytes: 123,
            generationTimeMs: 12.5
        );

        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('generate')->once()->andReturn($cacheStats);

        $this->app->instance(CacheRulesService::class, $mockService);

        // Act: Execute the command
        $this->artisan('roster:cache-rules')
            ->expectsOutput('📊 Cache stats:')
            ->assertExitCode(0);
    }

    /**
     * Test successful clearing of cache.
     */
    public function test_command_clears_cache_successfully(): void
    {
        // Arrange: Prepare mock service for clear operation
        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('clear')->once()->andReturn(null);

        $this->app->instance(CacheRulesService::class, $mockService);

        // Act: Execute the command with clear option
        $this->artisan('roster:cache-rules', ['--clear' => true])
            ->assertExitCode(0);
    }

    /**
     * Test force clear and regeneration of cache.
     */
    public function test_command_clears_and_regenerates_with_force(): void
    {
        // Arrange: Prepare mock service with cache stats for force clear
        $cacheStats = new CacheStats(
            path: $this->cacheFilePath,
            rulesCount: 1,
            sizeBytes: 123,
            generationTimeMs: 10
        );

        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('clear')->once()->andReturn($cacheStats);

        $this->app->instance(CacheRulesService::class, $mockService);

        // Act: Execute the command with clear and force options
        $this->artisan('roster:cache-rules', ['--clear' => true, '--force' => true])
            ->expectsOutput('📊 Cache stats:')
            ->assertExitCode(0);
    }

    /**
     * Test successful display of cache statistics.
     */
    public function test_command_shows_cache_successfully(): void
    {
        // Arrange: Prepare mock service with cache stats
        $cacheStats = new CacheStats(
            path: $this->cacheFilePath,
            rulesCount: 2,
            sizeBytes: 456,
            generationTimeMs: 0
        );

        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('show')->once()->andReturn($cacheStats);

        $this->app->instance(CacheRulesService::class, $mockService);

        // Act: Execute the command with show option
        $this->artisan('roster:cache-rules', ['--show' => true])
            ->expectsOutput('📊 Cache stats:')
            ->assertExitCode(0);
    }

    /**
     * Test successful display of rules in table format.
     */
    public function test_command_displays_rules_table(): void
    {
        // Arrange: Create cache file with test rules
        $rules = [
            'Test\RuleOne' => [
                'priority' => 100,
                'entities' => ['availability'],
                'operations' => ['create', 'update'],
            ],
            'Test\RuleTwo' => [
                'priority' => 90,
                'entities' => ['schedule'],
                'operations' => ['delete'],
            ],
        ];

        $this->createCacheFileWithRules($rules);

        // Arrange: Prepare partial mock service
        $partialMock = Mockery::mock(CacheRulesService::class)->makePartial();
        $partialMock->shouldAllowMockingProtectedMethods();

        $this->app->instance(CacheRulesService::class, $partialMock);

        // Act: Execute the command with list option
        $this->artisan('roster:cache-rules', ['--list' => true])
            ->expectsTable(
                ['No', 'Class', 'Priority', 'Entities', 'Operations'],
                [
                    [1, 'Test\RuleOne', 100, 'availability', 'create, update'],
                    [2, 'Test\RuleTwo', 90, 'schedule', 'delete'],
                ]
            )
            ->assertExitCode(0);
    }

    /**
     * Test failure when cache generation throws an exception.
     */
    public function test_command_fails_when_generation_fails(): void
    {
        // Arrange: Prepare mock service that throws exception
        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('generate')
            ->once()
            ->andThrow(new RuntimeException('Cache generation failed'));

        $this->app->instance(CacheRulesService::class, $mockService);

        // Act & Assert: Execute command and verify failure
        $this->artisan('roster:cache-rules')
            ->expectsOutput('Cache generation failed')
            ->assertExitCode(1);
    }

    /**
     * Test failure when cache clearing throws an exception.
     */
    public function test_command_fails_when_clear_fails(): void
    {
        // Arrange: Prepare mock service that throws exception on clear
        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('clear')
            ->once()
            ->andThrow(new RuntimeException('Cache clear failed'));

        $this->app->instance(CacheRulesService::class, $mockService);

        // Act & Assert: Execute command with clear option and verify failure
        $this->artisan('roster:cache-rules', ['--clear' => true])
            ->expectsOutput('Cache clear failed')
            ->assertExitCode(1);
    }

    /**
     * Delete the cache file if it exists.
     */
    private function deleteCacheFileIfExists(): void
    {
        if (File::exists($this->cacheFilePath)) {
            File::delete($this->cacheFilePath);
        }
    }

    /**
     * Create a cache file with the given rules.
     *
     * @param array<string, array{priority: int, entities: array<string>, operations: array<string>}> $rules
     */
    private function createCacheFileWithRules(array $rules): void
    {
        $content = '<?php return ' . var_export($rules, true) . ';';
        File::put($this->cacheFilePath, $content);
    }
}
