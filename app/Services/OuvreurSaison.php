<?php

namespace App\Services;

use App\Models\Saison;
use Illuminate\Support\Facades\Storage;

class OuvreurSaison
{
    public function ouvrir(string $libelle, float $tarifAdulte, float $tarifEnfantFamille, float $tarifEnfantSeul): Saison
    {
        $saisonPrecedente = Saison::active();

        $saison = Saison::query()->create([
            'libelle' => $libelle,
            'tarif_adulte' => $tarifAdulte,
            'tarif_enfant_famille' => $tarifEnfantFamille,
            'tarif_enfant_seul' => $tarifEnfantSeul,
            'couleur' => $saisonPrecedente?->couleur ?? Saison::COULEUR_PAR_DEFAUT,
        ]);

        if ($saisonPrecedente !== null) {
            $saison->update(['logo' => $this->copierLogo($saisonPrecedente->logo, $saison)]);
        }

        $saison->activer();

        return $saison;
    }

    private function copierLogo(?string $cheminSource, Saison $saison): ?string
    {
        if ($cheminSource === null || ! Storage::disk('data')->exists($cheminSource)) {
            return null;
        }

        $extension = pathinfo($cheminSource, PATHINFO_EXTENSION);
        $destination = "visuels/saison-{$saison->id}-logo.{$extension}";

        Storage::disk('data')->copy($cheminSource, $destination);

        return $destination;
    }
}
