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

    public int $nouvelleAnnee = 0;

    public ?string $message = null;

    public function mount(): void
    {
        $this->chargerSaisonActive();
        $this->nouvelleAnnee = $this->anneeSuivante();
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
            ['nouvelleAnnee' => ['required', 'integer', 'between:2000,2100']],
            [],
            ['nouvelleAnnee' => 'année de début'],
        );

        $libelle = Saison::libellePourAnnee($this->nouvelleAnnee);

        if (Saison::query()->where('libelle', $libelle)->exists()) {
            $this->addError('nouvelleAnnee', "La saison {$libelle} existe déjà.");

            return;
        }

        $active = Saison::active();

        $ouvreur->ouvrir(
            $libelle,
            (float) ($active?->tarif_adulte ?? 0),
            (float) ($active?->tarif_enfant_famille ?? 0),
            (float) ($active?->tarif_enfant_seul ?? 0),
        );

        $this->chargerSaisonActive();
        $this->nouvelleAnnee = $this->anneeSuivante();
        $this->message = 'Nouvelle saison ouverte et activée. Vérifiez ses tarifs et ses visuels.';
    }

    public function render()
    {
        return view('livewire.reglages.saisons', [
            'saisons' => Saison::query()->orderByDesc('libelle')->get(),
            'anneesProposees' => range((int) date('Y') - 1, (int) date('Y') + 2),
            'libelleNouvelleSaison' => Saison::libellePourAnnee($this->nouvelleAnnee),
        ]);
    }

    private function anneeSuivante(): int
    {
        return (Saison::active()?->anneeDebut() ?? (int) date('Y') - 1) + 1;
    }

    private function chargerSaisonActive(): void
    {
        $active = Saison::active();

        if ($active !== null) {
            $this->form->charger($active);
        }
    }
}
