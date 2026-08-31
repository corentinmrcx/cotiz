<?php

namespace App\Enums;

enum StatutAdhesion: string
{
    case AEnvoyer = 'a_envoyer';
    case Envoye = 'envoye';
    case Echec = 'echec';

    public function libelle(): string
    {
        return match ($this) {
            self::AEnvoyer => 'À envoyer',
            self::Envoye => 'Envoyé',
            self::Echec => 'Échec',
        };
    }
}
