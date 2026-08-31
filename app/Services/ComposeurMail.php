<?php

namespace App\Services;

use App\Enums\CleReglage;
use App\Models\Adhesion;
use App\Models\Reglage;

class ComposeurMail
{
    public function objet(Adhesion $adhesion): string
    {
        return $this->remplacerVariables(Reglage::valeur(CleReglage::MailObjet, ''), $adhesion);
    }

    public function corps(Adhesion $adhesion): string
    {
        return $this->remplacerVariables(Reglage::valeur(CleReglage::MailCorps, ''), $adhesion);
    }

    /** @return array<string, string> */
    public function variables(Adhesion $adhesion): array
    {
        $saison = $adhesion->saison;

        return [
            'nom' => mb_strtoupper($adhesion->nom),
            'prenom' => $adhesion->prenom ?? '',
            'saison' => $saison->libelle,
            'cotisation' => FormateurMontant::euros($adhesion->cotisation_calculee),
            'nb_adultes' => (string) $adhesion->nb_adultes,
            'nb_enfants' => (string) $adhesion->nbEnfants(),
            'tarif_adulte' => FormateurMontant::euros($saison->tarif_adulte),
            'tarif_enfant_famille' => FormateurMontant::euros($saison->tarif_enfant_famille),
            'tarif_enfant_seul' => FormateurMontant::euros($saison->tarif_enfant_seul),
            'asso_nom' => Reglage::valeur(CleReglage::AssoNom, ''),
            'asso_email' => Reglage::valeur(CleReglage::AssoEmailAffiche, ''),
        ];
    }

    private function remplacerVariables(string $gabarit, Adhesion $adhesion): string
    {
        $texte = $gabarit;

        foreach ($this->variables($adhesion) as $variable => $valeur) {
            $texte = preg_replace('/\{\{\s*'.$variable.'\s*\}\}/', $valeur, $texte);
        }

        return preg_replace('/ {2,}/', ' ', $texte);
    }
}
