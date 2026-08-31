<?php

namespace App\Livewire\Adhesions;

use App\Enums\CleReglage;
use App\Enums\StatutAdhesion;
use App\Models\Adhesion;
use App\Models\Reglage;
use App\Models\Saison;
use App\Services\EnvoyeurCarte;
use App\Services\GenerateurCarte;
use Livewire\Attributes\Url;
use Livewire\Component;

class Liste extends Component
{
    public const GENERATION = 'generation';

    public const ENVOI = 'envoi';

    #[Url]
    public string $statut = '';

    public ?string $message = null;

    public ?string $traitement = null;

    /** @var int[] */
    public array $fileAttente = [];

    public int $nbTotal = 0;

    public function mount(): void
    {
        $this->message = session('message');
    }

    public function supprimer(int $adhesionId): void
    {
        Adhesion::query()->findOrFail($adhesionId)->delete();

        $this->message = 'Adhésion supprimée.';
    }

    public function demarrerGenerationTest(): void
    {
        $this->demarrer(self::GENERATION, Saison::active()->adhesions()->orderBy('numero')->pluck('id')->all());
    }

    public function demarrerEnvoi(): void
    {
        $ids = Saison::active()->adhesions()
            ->where('statut', '!=', StatutAdhesion::Envoye->value)
            ->orderBy('numero')
            ->pluck('id')
            ->all();

        $this->demarrer(self::ENVOI, $ids);
    }

    public function traiterSuivante(GenerateurCarte $generateur, EnvoyeurCarte $envoyeur): void
    {
        if ($this->fileAttente === []) {
            $this->terminer();

            return;
        }

        $adhesion = Adhesion::query()->find(array_shift($this->fileAttente));

        if ($adhesion !== null) {
            $this->traitement === self::GENERATION
                ? $generateur->generer($adhesion)
                : $envoyeur->envoyer($adhesion);
        }

        if ($this->fileAttente === []) {
            $this->terminer();
        }
    }

    public function renvoyer(int $adhesionId, EnvoyeurCarte $envoyeur): void
    {
        $adhesion = Adhesion::query()->findOrFail($adhesionId);
        $envoyeur->envoyer($adhesion);

        $this->message = $adhesion->refresh()->statut === StatutAdhesion::Envoye
            ? sprintf('Carte %s envoyée.', $adhesion->numero)
            : sprintf('Échec de l\'envoi de la carte %s.', $adhesion->numero);
    }

    public function interrompre(): void
    {
        $this->fileAttente = [];
        $this->terminer();
    }

    public function render()
    {
        $saison = Saison::active();

        return view('livewire.adhesions.liste', [
            'saison' => $saison,
            'adhesions' => $this->adhesionsFiltrees($saison),
            'statuts' => StatutAdhesion::cases(),
            'nbTraitees' => $this->nbTotal - count($this->fileAttente),
            'delaiSecondes' => max(1, (int) Reglage::valeur(CleReglage::DelaiEntreEnvois, '2')),
        ]);
    }

    private function demarrer(string $traitement, array $ids): void
    {
        $this->traitement = $traitement;
        $this->fileAttente = $ids;
        $this->nbTotal = count($ids);
        $this->message = null;
    }

    private function terminer(): void
    {
        $this->message = $this->traitement === self::GENERATION
            ? sprintf('%d carte(s) générée(s) en mode test, aucun envoi.', $this->nbTotal)
            : sprintf('Envoi terminé : %d adhésion(s) traitée(s).', $this->nbTotal);
        $this->traitement = null;
        $this->nbTotal = 0;
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
