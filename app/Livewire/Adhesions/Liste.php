<?php

namespace App\Livewire\Adhesions;

use App\Enums\StatutAdhesion;
use App\Models\Adhesion;
use App\Models\Saison;
use Livewire\Attributes\Url;
use Livewire\Component;

class Liste extends Component
{
    #[Url]
    public string $statut = '';

    public ?string $message = null;

    public function mount(): void
    {
        $this->message = session('message');
    }

    public function supprimer(int $adhesionId): void
    {
        Adhesion::query()->findOrFail($adhesionId)->delete();

        $this->message = 'Adhésion supprimée.';
    }

    public function render()
    {
        $saison = Saison::active();

        return view('livewire.adhesions.liste', [
            'saison' => $saison,
            'adhesions' => $this->adhesionsFiltrees($saison),
            'statuts' => StatutAdhesion::cases(),
        ]);
    }

    private function adhesionsFiltrees(?Saison $saison)
    {
        if ($saison === null) {
            return collect();
        }

        return $saison->adhesions()
            ->with('destinataires')
            ->when($this->statut !== '', fn ($requete) => $requete->where('statut', $this->statut))
            ->orderBy('numero')
            ->get();
    }
}
