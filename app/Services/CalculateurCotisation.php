<?php

namespace App\Services;

use App\Models\Saison;

class CalculateurCotisation
{
    public function calculer(Saison $saison, int $nbAdultes, int $nbEnfantsFamille, int $nbEnfantsSeuls): float
    {
        return round(
            $nbAdultes * (float) $saison->tarif_adulte
            + $nbEnfantsFamille * (float) $saison->tarif_enfant_famille
            + $nbEnfantsSeuls * (float) $saison->tarif_enfant_seul,
            2
        );
    }
}
