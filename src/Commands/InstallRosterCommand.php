<?php

declare(strict_types=1);

namespace Roster\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Roster\RosterServiceProvider;

/**
 * Command to install the Roster package.
 *
 * This command publishes the package's resources (config, migrations)
 * and runs migrations if necessary.
 */
class InstallRosterCommand extends Command
{
    /** @var string The console command signature. */
    protected $signature = 'roster:install {--force : Force publish without confirmation}';

    /** @var string The console command description. */
    protected $description = 'Install the Roster package';

    /** @var array<string> Tables managed by the Roster package. */
    private const ROSTER_TABLES = [
        'roster_availabilities',
        'roster_schedules',
        'roster_impediments',
    ];

    /**
     * Execute the console command.
     *
     * @return int Returns Command::SUCCESS on success.
     */
    public function handle(): int
    {
        $this->info('🚀 Installing Roster package...');

        if (! $this->shouldSkipConfirmation()) {
            $this->displayPublishingDetails();

            if (! $this->confirm('Continue?', true)) {
                $this->info('Installation cancelled.');
                return self::SUCCESS;
            }
        }

        $this->publishResources();

        if ($this->rosterTablesAlreadyExist()) {
            $this->warn('⚠️ Roster tables already exist. Skipping migrations.');
        } else {
            $this->info('📊 Running migrations...');
            $this->call('migrate');
        }

        $this->displaySuccessMessage();

        return self::SUCCESS;
    }

    /**
     * Determine if confirmation should be skipped.
     *
     * @return bool True if the --force option is used, false otherwise.
     */
    private function shouldSkipConfirmation(): bool
    {
        return (bool) $this->option('force');
    }

    /**
     * Display details about what will be published.
     */
    private function displayPublishingDetails(): void
    {
        $this->warn('📦 This will publish:');
        $this->line('   - Configuration (config/roster.php)');
        $this->line('   - Database migrations (roster_* tables)');
    }

    /**
     * Publish the package resources.
     */
    private function publishResources(): void
    {
        $this->info('📤 Publishing resources...');

        $this->call('vendor:publish', [
            '--provider' => RosterServiceProvider::class,
            '--tag' => [
                'roster-config',
                'roster-migrations',
            ],
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Check if any Roster table already exists in the database.
     *
     * @return bool True if any roster table exists, false otherwise.
     */
    private function rosterTablesAlreadyExist(): bool
    {
        foreach (self::ROSTER_TABLES as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display the success message with next steps.
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
        $this->info('🔄 Generating validation rules cache...');
        $this->call('roster:cache-rules', ['--force' => true]);
    }
}
