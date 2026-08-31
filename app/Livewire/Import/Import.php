<?php

namespace App\Livewire\Import;

use App\Dto\VerdictLigne;
use App\Enums\NiveauVerdict;
use App\Exceptions\ClasseurInvalide;
use App\Models\Saison;
use App\Services\ImportateurAdhesions;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Import extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $fichier = null;

    public bool $analyse = false;

    public bool $importerAvertissements = false;

    /** @var array<string, array<int, array<string, mixed>>> */
    public array $groupes = ['valide' => [], 'avertissement' => [], 'rejet' => []];

    public ?string $message = null;

    public ?string $erreur = null;

    public function analyser(ImportateurAdhesions $importateur): void
    {
        $this->validate(
            ['fichier' => ['required', 'file', 'extensions:xlsx,csv', 'max:10240']],
            [],
            ['fichier' => 'classeur'],
        );

        $this->message = null;
        $this->erreur = null;
        $this->groupes = ['valide' => [], 'avertissement' => [], 'rejet' => []];

        try {
            $verdicts = $importateur->analyser($this->fichier->getRealPath(), Saison::active());
        } catch (ClasseurInvalide $exception) {
            $this->erreur = $exception->getMessage();
            $this->analyse = false;

            return;
        }

        foreach ($verdicts as $verdict) {
            $this->groupes[$verdict->niveau->value][] = $this->ligneAffichable($verdict);
        }

        $this->analyse = true;
    }

    public function importer(ImportateurAdhesions $importateur): void
    {
        $verdicts = $importateur->analyser($this->fichier->getRealPath(), Saison::active());
        $nombre = $importateur->importer($verdicts, Saison::active(), $this->importerAvertissements);

        $this->reset('fichier', 'analyse', 'groupes', 'importerAvertissements');
        $this->message = sprintf('%d adhésion(s) importée(s).', $nombre);
    }

    public function annuler(): void
    {
        $this->reset('fichier', 'analyse', 'groupes', 'importerAvertissements', 'erreur');
    }

    public function render()
    {
        return view('livewire.import.import', [
            'saison' => Saison::active(),
            'nbImportables' => count($this->groupes[NiveauVerdict::Valide->value])
                + ($this->importerAvertissements ? count($this->groupes[NiveauVerdict::Avertissement->value]) : 0),
        ]);
    }

    private function ligneAffichable(VerdictLigne $verdict): array
    {
        return [
            'ligne' => $verdict->ligne->numeroLigne,
            'nom' => $verdict->ligne->nomComplet(),
            'emails' => implode(', ', $verdict->ligne->emails),
            'effectifs' => sprintf('%d / %d / %d', $verdict->ligne->nbAdultes, $verdict->ligne->nbEnfantsFamille, $verdict->ligne->nbEnfantsSeuls),
            'cotisation' => $verdict->cotisationCalculee,
            'encaisse' => $verdict->ligne->montantEncaisse,
            'motifs' => $verdict->motifs,
        ];
    }
}
