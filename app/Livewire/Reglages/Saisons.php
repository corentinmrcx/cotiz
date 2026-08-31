<?php

namespace App\Livewire\Reglages;

use App\Livewire\Forms\SaisonForm;
use App\Models\Saison;
use App\Services\OuvreurSaison;
use Livewire\Component;
use Livewire\WithFileUploads;

class Saisons extends Component
{
    use WithFileUploads;

    public SaisonForm $form;

    public string $nouveauLibelle = '';

    public ?string $message = null;

    public function mount(): void
    {
        $this->chargerSaisonActive();
    }

    public function enregistrer(): void
    {
        $this->form->enregistrer();

        $this->message = 'Saison enregistrée.';
    }

    public function activer(int $saisonId): void
    {
        Saison::query()->findOrFail($saisonId)->activer();

        $this->chargerSaisonActive();
        $this->message = 'Saison active modifiée.';
    }

    public function ouvrirNouvelleSaison(OuvreurSaison $ouvreur): void
    {
        $this->validate(
            ['nouveauLibelle' => ['required', 'regex:/^\d{4}-\d{4}$/', 'unique:saisons,libelle']],
            [],
            ['nouveauLibelle' => 'libellé de la nouvelle saison'],
        );

        $active = Saison::active();

        $ouvreur->ouvrir(
            $this->nouveauLibelle,
            (float) ($active?->tarif_adulte ?? 0),
            (float) ($active?->tarif_enfant_famille ?? 0),
            (float) ($active?->tarif_enfant_seul ?? 0),
        );

        $this->nouveauLibelle = '';
        $this->chargerSaisonActive();
        $this->message = 'Nouvelle saison ouverte et activée. Vérifiez ses tarifs et ses visuels.';
    }

    public function render()
    {
        return view('livewire.reglages.saisons', [
            'saisons' => Saison::query()->orderByDesc('libelle')->get(),
        ]);
    }

    private function chargerSaisonActive(): void
    {
        $active = Saison::active();

        if ($active !== null) {
            $this->form->charger($active);
        }
    }
}
