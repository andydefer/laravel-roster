<?php

namespace Roster\Domain\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Roster\RosterServiceProvider;


class RosterInstallerService
{
    private const ROSTER_TABLES = [
        'roster_availabilities',
        'roster_schedules',
        'roster_impediments',
    ];

    public function install(Command $command, bool $force = false): void
    {
        $command->info('🚀 Installing Roster package...');

        if (! $force) {
            $command->warn('📦 This will publish:');
            $command->line('   - Configuration (config/roster.php)');
            $command->line('   - Database migrations (roster_* tables)');

            if (! $command->confirm('Continue?', true)) {
                $command->info('Installation cancelled.');
                return;
            }
        }

        $this->publishResources($command, $force);

        if ($this->rosterTablesExist()) {
            $command->warn('⚠️ Roster tables already exist. Skipping migrations.');
        } else {
            $command->info('📊 Running migrations...');
            $command->call('migrate');
        }

        $command->newLine();
        $command->info('✅ Roster package installed successfully!');
        $command->line('📝 Next steps:');
        $command->line('   1. Review config/roster.php for configuration options');
        $command->line('   2. Add the HasRoster trait to your models');
        $command->line('   3. Use the facades: Availability::for($model)->create([...])');

        $command->info('🔄 Generating validation rules cache...');
        $command->call('roster:cache-rules', ['--force' => true]);
    }

    private function publishResources(Command $command, bool $force): void
    {
        $command->info('📤 Publishing resources...');

        $command->call('vendor:publish', [
            '--provider' => RosterServiceProvider::class,
            '--tag' => ['roster-config', 'roster-migrations'],
            '--force' => $force,
        ]);
    }

    private function rosterTablesExist(): bool
    {
        foreach (self::ROSTER_TABLES as $table) {
            if (Schema::hasTable($table)) {
                return true;
            }
        }

        return false;
    }
}
