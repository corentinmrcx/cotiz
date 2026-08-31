<?php

namespace App\Services;

use App\Dto\LigneAdhesion;
use App\Dto\VerdictLigne;
use App\Enums\NiveauVerdict;
use App\Models\Saison;

class ValidateurImport
{
    public function __construct(private CalculateurCotisation $calculateur) {}

    public function valider(LigneAdhesion $ligne, Saison $saison): VerdictLigne
    {
        $cotisation = $this->calculateur->calculer($saison, $ligne->nbAdultes, $ligne->nbEnfantsFamille, $ligne->nbEnfantsSeuls);
        $rejets = $this->motifsDeRejet($ligne);

        if ($rejets !== []) {
            return new VerdictLigne($ligne, NiveauVerdict::Rejet, $rejets, $cotisation);
        }

        $avertissements = $this->motifsDAvertissement($ligne, $saison, $cotisation);

        return new VerdictLigne(
            $ligne,
            $avertissements === [] ? NiveauVerdict::Valide : NiveauVerdict::Avertissement,
            $avertissements,
            $cotisation,
        );
    }

    /** @return string[] */
    private function motifsDeRejet(LigneAdhesion $ligne): array
    {
        $motifs = [];

        if ($ligne->nom === '') {
            $motifs[] = 'Le nom est vide.';
        }

        if ($this->emailsValides($ligne) === []) {
            $motifs[] = 'Aucune adresse mail valide.';
        }

        if ($ligne->effectifTotal() === 0) {
            $motifs[] = 'Tous les effectifs sont à zéro.';
        }

        return $motifs;
    }

    /** @return string[] */
    private function motifsDAvertissement(LigneAdhesion $ligne, Saison $saison, float $cotisation): array
    {
        $motifs = [];

        if ($ligne->montantEncaisse !== null && abs($ligne->montantEncaisse - $cotisation) >= 0.01) {
            $motifs[] = sprintf(
                'Montant encaissé %s € différent de la cotisation calculée %s €.',
                FormateurMontant::euros($ligne->montantEncaisse),
                FormateurMontant::euros($cotisation),
            );
        }

        $emailsInvalides = array_diff($ligne->emails, $this->emailsValides($ligne));

        if ($emailsInvalides !== []) {
            $motifs[] = 'Adresse ignorée car invalide : '.implode(', ', $emailsInvalides).'.';
        }

        if ($this->homonymeExiste($ligne, $saison)) {
            $motifs[] = 'Un homonyme existe déjà dans la saison.';
        }

        return $motifs;
    }

    /** @return string[] */
    public function emailsValides(LigneAdhesion $ligne): array
    {
        return array_values(array_filter($ligne->emails, fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false));
    }

    private function homonymeExiste(LigneAdhesion $ligne, Saison $saison): bool
    {
        return $saison->adhesions()
            ->whereRaw('lower(nom) = ?', [mb_strtolower($ligne->nom)])
            ->whereRaw('lower(coalesce(prenom, \'\')) = ?', [mb_strtolower($ligne->prenom ?? '')])
            ->exists();
    }
}
