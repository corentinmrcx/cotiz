<?php

namespace App\Services;

use App\Dto\LigneAdhesion;
use App\Models\Adhesion;
use App\Models\Saison;
use Illuminate\Support\Facades\DB;

class EnregistreurAdhesion
{
    public function __construct(
        private CalculateurCotisation $calculateur,
        private ValidateurImport $validateur,
    ) {}

    public function creer(LigneAdhesion $ligne, Saison $saison): Adhesion
    {
        return DB::transaction(function () use ($ligne, $saison) {
            $adhesion = $saison->adhesions()->create([
                'numero' => $saison->prochainNumero(),
                ...$this->attributs($ligne, $saison),
            ]);

            $this->remplacerDestinataires($adhesion, $ligne);

            return $adhesion;
        });
    }

    public function modifier(Adhesion $adhesion, LigneAdhesion $ligne): Adhesion
    {
        return DB::transaction(function () use ($adhesion, $ligne) {
            $adhesion->update($this->attributs($ligne, $adhesion->saison));
            $this->remplacerDestinataires($adhesion, $ligne);

            return $adhesion->refresh();
        });
    }

    private function attributs(LigneAdhesion $ligne, Saison $saison): array
    {
        return [
            'nom' => $ligne->nom,
            'prenom' => $ligne->prenom,
            'nb_adultes' => $ligne->nbAdultes,
            'nb_enfants_famille' => $ligne->nbEnfantsFamille,
            'nb_enfants_seuls' => $ligne->nbEnfantsSeuls,
            'cotisation_calculee' => $this->calculateur->calculer($saison, $ligne->nbAdultes, $ligne->nbEnfantsFamille, $ligne->nbEnfantsSeuls),
            'montant_encaisse' => $ligne->montantEncaisse,
            'mode_reglement' => $ligne->modeReglement,
            'date_reglement' => $ligne->dateReglement,
        ];
    }

    private function remplacerDestinataires(Adhesion $adhesion, LigneAdhesion $ligne): void
    {
        $adhesion->destinataires()->delete();

        foreach ($this->validateur->emailsValides($ligne) as $email) {
            $adhesion->destinataires()->create(['email' => $email]);
        }
    }
}
