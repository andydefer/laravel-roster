<?php

declare(strict_types=1);

namespace Roster\Commands;

use Illuminate\Console\Command;
use Roster\Domain\Services\RosterInstallerService;

final class InstallRosterCommand extends Command
{
    protected $signature = 'roster:install {--force : Force publish without confirmation}';

    protected $description = 'Install the Roster package';

    public function handle(RosterInstallerService $rosterInstallerService): int
    {
        $rosterInstallerService->install($this, (bool) $this->option('force'));
        return self::SUCCESS;
    }
}
