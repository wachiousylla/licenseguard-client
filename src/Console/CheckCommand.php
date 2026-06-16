<?php

namespace LicenseGuard\Client\Console;

use Illuminate\Console\Command;
use LicenseGuard\Client\LicenseChecker;

class CheckCommand extends Command
{
    protected $signature = 'license:check {--fresh : Ignore le cache local}';

    protected $description = 'Vérifie l’état de la licence auprès du serveur central.';

    public function handle(LicenseChecker $checker): int
    {
        if ($this->option('fresh')) {
            $checker->flush();
        }

        $result = $checker->check();

        $this->line('Statut   : ' . ($result['status'] ?? 'inconnu'));
        $this->line('Autorisé : ' . (($result['allowed'] ?? false) ? 'OUI' : 'NON'));
        $this->line('Message  : ' . ($result['message'] ?? '—'));

        return ($result['allowed'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
