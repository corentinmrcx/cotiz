<?php

namespace App\Livewire\Forms;

use App\Enums\CleReglage;
use App\Models\Reglage;
use Livewire\Form;

class ReglagesForm extends Form
{
    public string $expediteur_nom = '';

    public string $expediteur_email = '';

    public string $smtp_host = '';

    public string $smtp_port = '587';

    public string $smtp_username = '';

    public string $smtp_password = '';

    public string $smtp_encryption = 'tls';

    public bool $copie_cachee_active = true;

    public string $mail_objet = '';

    public string $mail_corps = '';

    public string $asso_nom = '';

    public string $asso_email_affiche = '';

    public string $asso_adresse = '';

    public string $delai_entre_envois = '2';

    public function rules(): array
    {
        return [
            'expediteur_nom' => ['required', 'string', 'max:100'],
            'expediteur_email' => ['required', 'email'],
            'smtp_host' => ['required', 'string'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string'],
            'smtp_password' => ['nullable', 'string'],
            'smtp_encryption' => ['required', 'in:tls,ssl,aucun'],
            'copie_cachee_active' => ['boolean'],
            'mail_objet' => ['required', 'string', 'max:200'],
            'mail_corps' => ['required', 'string'],
            'asso_nom' => ['required', 'string', 'max:100'],
            'asso_email_affiche' => ['required', 'email'],
            'asso_adresse' => ['required', 'string', 'max:200'],
            'delai_entre_envois' => ['required', 'integer', 'between:0,60'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'expediteur_nom' => 'nom de l\'expéditeur',
            'expediteur_email' => 'adresse de l\'expéditeur',
            'smtp_host' => 'serveur SMTP',
            'smtp_port' => 'port SMTP',
            'smtp_username' => 'identifiant SMTP',
            'smtp_password' => 'mot de passe SMTP',
            'smtp_encryption' => 'chiffrement',
            'mail_objet' => 'objet du mail',
            'mail_corps' => 'corps du mail',
            'asso_nom' => 'nom de l\'association',
            'asso_email_affiche' => 'adresse affichée',
            'asso_adresse' => 'adresse postale',
            'delai_entre_envois' => 'délai entre envois',
        ];
    }

    public function charger(): void
    {
        foreach (Reglage::tous() as $cle => $valeur) {
            if ($cle === CleReglage::CopieCacheeActive->value) {
                $this->copie_cachee_active = $valeur === '1';

                continue;
            }

            $this->{$cle} = $valeur ?? '';
        }
    }

    public function enregistrer(): void
    {
        $this->validate();

        foreach (CleReglage::cases() as $cle) {
            Reglage::definir($cle, $this->valeurAEnregistrer($cle));
        }
    }

    private function valeurAEnregistrer(CleReglage $cle): string
    {
        if ($cle === CleReglage::CopieCacheeActive) {
            return $this->copie_cachee_active ? '1' : '0';
        }

        if ($cle === CleReglage::SmtpPassword) {
            return str_replace(' ', '', $this->smtp_password);
        }

        return (string) $this->{$cle->value};
    }
}
