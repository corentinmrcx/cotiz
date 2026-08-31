<?php

namespace App\Livewire\Adhesions;

use App\Enums\NiveauVerdict;
use App\Livewire\Forms\AdhesionForm;
use App\Models\Adhesion;
use App\Models\Saison;
use App\Services\EnregistreurAdhesion;
use App\Services\ValidateurImport;
use Livewire\Component;

class Formulaire extends Component
{
    public AdhesionForm $form;

    public ?Adhesion $adhesion = null;

    public function mount(?Adhesion $adhesion = null): void
    {
        if ($adhesion?->exists) {
            $this->adhesion = $adhesion;
            $this->form->charger($adhesion);
        }
    }

    public function enregistrer(EnregistreurAdhesion $enregistreur, ValidateurImport $validateur)
    {
        $ligne = $this->form->versLigne();
        $saison = $this->adhesion?->saison ?? Saison::active();
        $verdict = $validateur->valider($ligne, $saison);

        if ($verdict->niveau === NiveauVerdict::Rejet) {
            $this->addError('form.emails', implode(' ', $verdict->motifs));

            return null;
        }

        $this->adhesion === null
            ? $enregistreur->creer($ligne, $saison)
            : $enregistreur->modifier($this->adhesion, $ligne);

        session()->flash('message', $this->adhesion === null ? 'Adhésion créée.' : 'Adhésion modifiée.');

        return $this->redirectRoute('adhesions');
    }

    public function render()
    {
        return view('livewire.adhesions.formulaire');
    }
}
