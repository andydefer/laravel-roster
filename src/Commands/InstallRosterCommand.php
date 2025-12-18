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
    protected $signature = 'roster:install';

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
        $this->info('Installing the Roster package...');

        $this->call('vendor:publish', [
            '--provider' => 'Vendor\Roster\RosterServiceProvider',
            '--tag' => 'roster-config',
        ]);

        $this->call('vendor:publish', [
            '--provider' => 'Vendor\Roster\RosterServiceProvider',
            '--tag' => 'roster-migrations',
        ]);

        $this->call('migrate');

        $this->info('Roster package installed successfully!');
    }
}
