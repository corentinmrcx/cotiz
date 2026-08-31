<?php

namespace App\Services;

use App\Enums\CleReglage;
use App\Models\Reglage;
use Illuminate\Support\Facades\Mail;

class ConfigurateurTransportSmtp
{
    public function appliquerReglages(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => $this->schemaPour(Reglage::valeur(CleReglage::SmtpEncryption)),
            'mail.mailers.smtp.host' => Reglage::valeur(CleReglage::SmtpHost),
            'mail.mailers.smtp.port' => (int) Reglage::valeur(CleReglage::SmtpPort, '587'),
            'mail.mailers.smtp.username' => Reglage::valeur(CleReglage::SmtpUsername),
            'mail.mailers.smtp.password' => Reglage::valeur(CleReglage::SmtpPassword),
            'mail.mailers.smtp.timeout' => 15,
            'mail.from.address' => Reglage::valeur(CleReglage::ExpediteurEmail),
            'mail.from.name' => Reglage::valeur(CleReglage::ExpediteurNom),
        ]);

        Mail::purge('smtp');
    }

    private function schemaPour(?string $encryption): string
    {
        return $encryption === 'ssl' ? 'smtps' : 'smtp';
    }
}
