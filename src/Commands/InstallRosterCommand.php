<?php

declare(strict_types=1);

namespace Roster\Commands;

use Illuminate\Console\Command;

class InstallRosterCommand extends Command
{
    protected $signature = 'roster:install';

    protected $description = 'Installer le package Roster';

    public function handle(): void
    {
        $this->info('Installation du package Roster...');

        // Publier la configuration
        $this->call('vendor:publish', [
            '--provider' => 'Vendor\Roster\RosterServiceProvider',
            '--tag' => 'roster-config',
        ]);

        // Publier les migrations
        $this->call('vendor:publish', [
            '--provider' => 'Vendor\Roster\RosterServiceProvider',
            '--tag' => 'roster-migrations',
        ]);

        // Exécuter les migrations
        $this->call('migrate');

        $this->info('Package Roster installé avec succès!');
    }
}
