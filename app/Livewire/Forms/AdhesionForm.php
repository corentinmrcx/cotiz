<?php

namespace App\Livewire\Forms;

use App\Dto\LigneAdhesion;
use App\Models\Adhesion;
use Carbon\CarbonImmutable;
use Livewire\Form;

class AdhesionForm extends Form
{
    public string $nom = '';

    public string $prenom = '';

    public string $emails = '';

    public int $nb_adultes = 0;

    public int $nb_enfants_famille = 0;

    public int $nb_enfants_seuls = 0;

    public ?float $montant_encaisse = null;

    public string $mode_reglement = '';

    public string $date_reglement = '';

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['nullable', 'string', 'max:100'],
            'emails' => ['required', 'string'],
            'nb_adultes' => ['required', 'integer', 'min:0'],
            'nb_enfants_famille' => ['required', 'integer', 'min:0'],
            'nb_enfants_seuls' => ['required', 'integer', 'min:0'],
            'mode_reglement' => ['nullable', 'in:Chèque,Espèces,Virement,CB'],
            'date_reglement' => ['nullable', 'date'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'nom' => 'nom',
            'prenom' => 'prénom',
            'emails' => 'adresses mail',
            'nb_adultes' => 'nombre d\'adultes',
            'nb_enfants_famille' => 'nombre d\'enfants en famille',
            'nb_enfants_seuls' => 'nombre d\'enfants seuls',
            'mode_reglement' => 'mode de règlement',
            'date_reglement' => 'date de règlement',
        ];
    }

    public function charger(Adhesion $adhesion): void
    {
        $this->nom = $adhesion->nom;
        $this->prenom = $adhesion->prenom ?? '';
        $this->emails = implode(', ', $adhesion->emailsDestinataires());
        $this->nb_adultes = $adhesion->nb_adultes;
        $this->nb_enfants_famille = $adhesion->nb_enfants_famille;
        $this->nb_enfants_seuls = $adhesion->nb_enfants_seuls;
        $this->montant_encaisse = $adhesion->montant_encaisse === null ? null : (float) $adhesion->montant_encaisse;
        $this->mode_reglement = $adhesion->mode_reglement ?? '';
        $this->date_reglement = $adhesion->date_reglement?->format('Y-m-d') ?? '';
    }

    public function versLigne(): LigneAdhesion
    {
        $this->validate();

        return new LigneAdhesion(
            numeroLigne: 0,
            nom: trim($this->nom),
            prenom: trim($this->prenom) === '' ? null : trim($this->prenom),
            emails: array_values(array_filter(array_map(fn (string $email) => strtolower(trim($email)), preg_split('/[,;\s]+/', $this->emails)))),
            nbAdultes: $this->nb_adultes,
            nbEnfantsFamille: $this->nb_enfants_famille,
            nbEnfantsSeuls: $this->nb_enfants_seuls,
            montantEncaisse: $this->montant_encaisse,
            modeReglement: $this->mode_reglement === '' ? null : $this->mode_reglement,
            dateReglement: $this->date_reglement === '' ? null : CarbonImmutable::parse($this->date_reglement),
        );
    }
}
