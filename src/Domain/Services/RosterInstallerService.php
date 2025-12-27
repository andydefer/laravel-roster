<?php

namespace Roster\Domain\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Roster\RosterServiceProvider;

/**
 * Service for installing and setting up the Roster package.
 *
 * Handles the complete installation process including resource publishing,
 * database migrations, and post-installation steps.
 */
class RosterInstallerService
{
    /** @var array<int, string> Core tables required by the Roster package */
    private const CORE_TABLES = [
        'roster_availabilities',
        'roster_schedules',
        'roster_impediments',
    ];

    /**
     * Execute the complete Roster package installation process.
     *
     * @param Command $command Console command instance for user interaction
     * @param bool $force Skip confirmation prompts when true
     */
    public function install(Command $command, bool $force = false): void
    {
        $command->info('🚀 Installing Roster package...');

        if (! $this->shouldProceedWithInstallation($command, $force)) {
            return;
        }

        $this->publishResources($command, $force);
        $this->handleDatabaseMigrations($command);

        $this->displaySuccessMessage($command);
        $this->generateValidationRulesCache($command);
    }

    /**
     * Check if installation should proceed based on user confirmation.
     *
     * @param Command $command Console command instance
     * @param bool $force Skip confirmation when true
     * @return bool True if installation should proceed
     */
    private function shouldProceedWithInstallation(Command $command, bool $force): bool
    {
        if ($force) {
            return true;
        }

        $command->warn('📦 This will publish:');
        $command->line('   - Configuration (config/roster.php)');
        $command->line('   - Database migrations (roster_* tables)');

        if (! $command->confirm('Continue?', true)) {
            $command->info('Installation cancelled.');
            return false;
        }

        return true;
    }

    /**
     * Publish package resources to the application.
     *
     * @param Command $command Console command instance
     * @param bool $force Overwrite existing files when true
     */
    private function publishResources(Command $command, bool $force): void
    {
        $command->info('📤 Publishing resources...');

        $command->call('vendor:publish', [
            '--provider' => RosterServiceProvider::class,
            '--tag' => ['roster-config', 'roster-migrations'],
            '--force' => $force,
        ]);
    }

    /**
     * Handle database migrations based on existing tables.
     *
     * @param Command $command Console command instance
     */
    private function handleDatabaseMigrations(Command $command): void
    {
        if ($this->hasCoreTables()) {
            $command->warn('⚠️ Roster tables already exist. Skipping migrations.');
            return;
        }

        $command->info('📊 Running migrations...');
        $command->call('migrate');
    }

    /**
     * Check if any core Roster tables already exist in the database.
     *
     * @return bool True if any core table exists
     */
    private function hasCoreTables(): bool
    {
        foreach (self::CORE_TABLES as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display installation success message with next steps.
     *
     * @param Command $command Console command instance
     */
    private function displaySuccessMessage(Command $command): void
    {
        $command->newLine();
        $command->info('✅ Roster package installed successfully!');
        $command->line('📝 Next steps:');
        $command->line('   1. Review config/roster.php for configuration options');
        $command->line('   2. Add the HasRoster trait to your models');
        $command->line('   3. Use the facades: Availability::for($model)->create([...])');
    }

    /**
     * Generate validation rules cache after installation.
     *
     * @param Command $command Console command instance
     */
    private function generateValidationRulesCache(Command $command): void
    {
        $command->info('🔄 Generating validation rules cache...');
        $command->call('roster:cache-rules', ['--force' => true]);
    }
}
