<?php

namespace Database\Seeders;

use App\Enums\CleReglage;
use App\Models\Reglage;
use Illuminate\Database\Seeder;

class ReglageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->valeursParDefaut() as $cle => $valeur) {
            $cleReglage = CleReglage::from($cle);

            if (! Reglage::existe($cleReglage)) {
                Reglage::definir($cleReglage, $valeur);
            }
        }
    }

    /** @return array<string, ?string> */
    private function valeursParDefaut(): array
    {
        return [
            CleReglage::ExpediteurNom->value => null,
            CleReglage::ExpediteurEmail->value => null,
            CleReglage::SmtpHost->value => null,
            CleReglage::SmtpPort->value => '587',
            CleReglage::SmtpUsername->value => null,
            CleReglage::SmtpPassword->value => null,
            CleReglage::SmtpEncryption->value => 'tls',
            CleReglage::CopieCacheeActive->value => '1',
            CleReglage::MailObjet->value => 'Votre carte d\'adhérent {{saison}} — {{asso_nom}}',
            CleReglage::MailCorps->value => file_get_contents(__DIR__.'/textes/mail_corps.txt'),
            CleReglage::AssoNom->value => 'Foyer de Soudron',
            CleReglage::AssoEmailAffiche->value => 'contact@exemple.org',
            CleReglage::AssoAdresse->value => '1 Rue de l\'Église 51320 Soudron',
            CleReglage::DelaiEntreEnvois->value => '2',
        ];
    }
}
