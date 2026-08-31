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
        ]);

        if ($saisonPrecedente !== null) {
            $saison->update([
                'visuel_recto' => $this->copierVisuel($saisonPrecedente->visuel_recto, $saison, 'recto'),
                'visuel_verso' => $this->copierVisuel($saisonPrecedente->visuel_verso, $saison, 'verso'),
            ]);
        }

        $saison->activer();

        return $saison;
    }

    private function copierVisuel(?string $cheminSource, Saison $saison, string $face): ?string
    {
        if ($cheminSource === null || ! Storage::disk('data')->exists($cheminSource)) {
            return null;
        }

        $extension = pathinfo($cheminSource, PATHINFO_EXTENSION);
        $destination = "visuels/saison-{$saison->id}-{$face}.{$extension}";

        Storage::disk('data')->copy($cheminSource, $destination);

        return $destination;
    }
}
