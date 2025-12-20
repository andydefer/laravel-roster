<?php

declare(strict_types=1);

namespace Roster\Commands;

use Roster\RosterServiceProvider;
use Illuminate\Console\Command;

/**
 * Command to install the Roster package.
 *
 * Publishes configuration and migration files, then runs database migrations
 * to set up the package in a Laravel application.
 */
class InstallRosterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roster:install {--force : Force publish without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Roster package';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('🚀 Installing Roster package...');

        if (!$this->shouldSkipConfirmation()) {
            $this->displayPublishingDetails();

            if (!$this->confirm('Continue?', true)) {
                $this->info('Installation cancelled.');
                return;
            }
        }

        $this->publishResources();
        $this->runMigrations();
        $this->displaySuccessMessage();
    }

    /**
     * Determine if confirmation should be skipped.
     *
     * @return bool
     */
    private function shouldSkipConfirmation(): bool
    {
        return (bool) $this->option('force');
    }

    /**
     * Display what will be published during installation.
     *
     * @return void
     */
    private function displayPublishingDetails(): void
    {
        $this->warn('📦 This will publish:');
        $this->line('   - Configuration (config/roster.php)');
        $this->line('   - Database migrations (roster_* tables)');
        $this->line('   - Routes (routes/roster.php)');
        $this->line('   - Views (resources/views/vendor/roster)');
    }

    /**
     * Publish package resources using vendor:publish command.
     *
     * @return void
     */
    private function publishResources(): void
    {
        $this->info('📤 Publishing resources...');

        $this->call('vendor:publish', [
            '--provider' => RosterServiceProvider::class,
            '--tag' => [
                'roster-config',
                'roster-migrations',
                'roster-routes',
                'roster-views'
            ],
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Run database migrations.
     *
     * @return void
     */
    private function runMigrations(): void
    {
        $this->info('📊 Running migrations...');
        $this->call('migrate');
    }

    /**
     * Display installation success message and next steps.
     *
     * @return void
     */
    private function displaySuccessMessage(): void
    {
        $this->newLine();
        $this->info('✅ Roster package installed successfully!');
        $this->line('');
        $this->line('📝 Next steps:');
        $this->line('   1. Review config/roster.php for configuration options');
        $this->line('   2. Add the HasRoster trait to your models');
        $this->line('   3. Use the facades: Availability::for($model)->create([...])');
        $this->line('   4. Check routes/roster.php for available API endpoints');
    }
}
