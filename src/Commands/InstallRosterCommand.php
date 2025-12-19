<?php

declare(strict_types=1);

namespace Roster\Commands;

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
     */
    public function handle(): void
    {
        $this->info('🚀 Installing Roster package...');

        if (!$this->option('force')) {
            $this->warn('📦 This will publish:');
            $this->line('   - Configuration (config/roster.php)');
            $this->line('   - Database migrations (roster_* tables)');
            $this->line('   - Routes (routes/roster.php)');
            $this->line('   - Views (resources/views/vendor/roster)');

            if (!$this->confirm('Continue?', true)) {
                $this->info('Installation cancelled.');
                return;
            }
        }

        $this->info('📤 Publishing resources...');

        // Publier tout avec le tag roster
        $this->call('vendor:publish', [
            '--provider' => 'Roster\RosterServiceProvider',
            '--tag' => ['roster-config', 'roster-migrations', 'roster-routes', 'roster-views'],
            '--force' => $this->option('force'),
        ]);

        $this->info('📊 Running migrations...');
        $this->call('migrate');

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
