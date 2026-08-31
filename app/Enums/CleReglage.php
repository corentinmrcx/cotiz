<?php

namespace App\Enums;

enum CleReglage: string
{
    case ExpediteurNom = 'expediteur_nom';
    case ExpediteurEmail = 'expediteur_email';
    case SmtpHost = 'smtp_host';
    case SmtpPort = 'smtp_port';
    case SmtpUsername = 'smtp_username';
    case SmtpPassword = 'smtp_password';
    case SmtpEncryption = 'smtp_encryption';
    case CopieCacheeActive = 'copie_cachee_active';
    case MailObjet = 'mail_objet';
    case MailCorps = 'mail_corps';
    case AssoNom = 'asso_nom';
    case AssoEmailAffiche = 'asso_email_affiche';
    case AssoAdresse = 'asso_adresse';
    case DelaiEntreEnvois = 'delai_entre_envois';

    public function estSensible(): bool
    {
        return match ($this) {
            self::SmtpPassword => true,
            default => false,
        };
    }
}
