<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery;
use Roster\Commands\CacheRulesCommand;
use Roster\Domain\DTOs\CacheStats;
use Roster\Domain\Services\CacheRulesService;
use Tests\TestCase;

final class CacheRulesCommandTest extends TestCase
{
    private string $cacheFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheFilePath = storage_path('framework/cache/roster_rules.php');

        if (File::exists($this->cacheFilePath)) {
            File::delete($this->cacheFilePath);
        }
    }

    protected function tearDown(): void
    {
        if (File::exists($this->cacheFilePath)) {
            File::delete($this->cacheFilePath);
        }

        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test that the command can be instantiated and has the correct signature.
     */
    public function test_command_can_be_instantiated(): void
    {
        $command = new CacheRulesCommand;

        $this->assertSame('roster:cache-rules', $command->getName());
        $this->assertSame('Manage Roster validation rules cache', $command->getDescription());

        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('clear'));
        $this->assertTrue($definition->hasOption('force'));
        $this->assertTrue($definition->hasOption('show'));
    }

    /**
     * Test that the command executes successfully when generating cache.
     */
    public function test_command_generates_cache_successfully(): void
    {
        $mockStats = new CacheStats(
            path: $this->cacheFilePath,
            rulesCount: 1,
            sizeBytes: 123,
            generationTimeMs: 12.5
        );

        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('generate')->once()->andReturn($mockStats);

        $this->app->instance(CacheRulesService::class, $mockService);

        $this->artisan('roster:cache-rules')
            ->expectsOutput('📊 Cache stats:')
            ->assertExitCode(0);
    }

    /**
     * Test that the command clears cache successfully.
     */
    public function test_command_clears_cache_successfully(): void
    {
        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('clear')->once()->andReturn(null);

        $this->app->instance(CacheRulesService::class, $mockService);

        $this->artisan('roster:cache-rules', ['--clear' => true])
            ->assertExitCode(0);
    }

    /**
     * Test that the command clears cache and regenerates when force option is used.
     */
    public function test_command_clears_and_regenerates_with_force(): void
    {
        $mockStats = new CacheStats(
            path: $this->cacheFilePath,
            rulesCount: 1,
            sizeBytes: 123,
            generationTimeMs: 10
        );

        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('clear')->once()->andReturn($mockStats);

        $this->app->instance(CacheRulesService::class, $mockService);

        $this->artisan('roster:cache-rules', ['--clear' => true, '--force' => true])
            ->expectsOutput('📊 Cache stats:')
            ->assertExitCode(0);
    }

    /**
     * Test that the command shows cache successfully.
     */
    public function test_command_shows_cache_successfully(): void
    {
        $mockStats = new CacheStats(
            path: $this->cacheFilePath,
            rulesCount: 2,
            sizeBytes: 456,
            generationTimeMs: 0
        );

        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('show')->once()->andReturn($mockStats);

        $this->app->instance(CacheRulesService::class, $mockService);

        $this->artisan('roster:cache-rules', ['--show' => true])
            ->expectsOutput('📊 Cache stats:')
            ->assertExitCode(0);
    }

    /**
     * Test failure when cache generation fails.
     */
    public function test_command_fails_when_generation_fails(): void
    {
        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('generate')->once()->andThrow(new \RuntimeException('Cache generation failed'));

        $this->app->instance(CacheRulesService::class, $mockService);

        $this->artisan('roster:cache-rules')
            ->expectsOutput('Cache generation failed')
            ->assertExitCode(1);
    }

    /**
     * Test failure when cache clearing fails.
     */
    public function test_command_fails_when_clear_fails(): void
    {
        $mockService = Mockery::mock(CacheRulesService::class);
        $mockService->shouldReceive('clear')->once()->andThrow(new \RuntimeException('Cache clear failed'));

        $this->app->instance(CacheRulesService::class, $mockService);

        $this->artisan('roster:cache-rules', ['--clear' => true])
            ->expectsOutput('Cache clear failed')
            ->assertExitCode(1);
    }
}
