<?php

namespace App\Services;

use App\Dto\DonneesCarte;
use App\Enums\CleReglage;
use App\Models\Adhesion;
use App\Models\Reglage;
use Illuminate\Support\Facades\Storage;

class RenduCarte
{
    public function html(Adhesion $adhesion): string
    {
        return view('cartes.gabarit', [
            'carte' => $this->donnees($adhesion),
            'police' => $this->policeEnDataUri(),
            'coordonnees' => file_get_contents(resource_path('cartes/gabarit.css')),
            'largeur' => config('cotiz.carte.largeur'),
            'hauteur' => config('cotiz.carte.hauteur'),
            'espacement' => config('cotiz.carte.espacement_faces'),
        ])->render();
    }

    public function donnees(Adhesion $adhesion): DonneesCarte
    {
        $saison = $adhesion->saison;

        return new DonneesCarte(
            saison: str_replace('-', ' - ', $saison->libelle),
            nomComplet: $adhesion->nomComplet(),
            nbAdultes: $adhesion->nb_adultes,
            nbEnfants: $adhesion->nbEnfants(),
            cotisation: FormateurMontant::euros($adhesion->cotisation_calculee),
            tarifAdulte: FormateurMontant::euros($saison->tarif_adulte),
            tarifEnfantFamille: FormateurMontant::euros($saison->tarif_enfant_famille),
            tarifEnfantSeul: FormateurMontant::euros($saison->tarif_enfant_seul),
            assoNom: Reglage::valeur(CleReglage::AssoNom, ''),
            assoEmail: Reglage::valeur(CleReglage::AssoEmailAffiche, ''),
            assoAdresse: Reglage::valeur(CleReglage::AssoAdresse, ''),
            couleur: $saison->couleur,
            logo: $this->visuelEnDataUri($saison->logo),
        );
    }

    private function visuelEnDataUri(?string $chemin): ?string
    {
        if ($chemin === null || ! Storage::disk('data')->exists($chemin)) {
            return null;
        }

        return $this->dataUri(Storage::disk('data')->mimeType($chemin), Storage::disk('data')->get($chemin));
    }

    private function policeEnDataUri(): string
    {
        return $this->dataUri('font/ttf', file_get_contents(public_path('fonts/Montserrat-Variable.ttf')));
    }

    private function dataUri(string $mime, string $contenu): string
    {
        return 'data:'.$mime.';base64,'.base64_encode($contenu);
    }
}
