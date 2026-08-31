<?php

namespace App\Services;

use App\Dto\VerdictLigne;
use App\Enums\NiveauVerdict;
use App\Exceptions\ClasseurInvalide;
use App\Models\Saison;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ImportateurAdhesions
{
    public function __construct(
        private LecteurClasseur $lecteur,
        private ValidateurImport $validateur,
        private EnregistreurAdhesion $enregistreur,
    ) {}

    /**
     * @return Collection<int, VerdictLigne>
     *
     * @throws ClasseurInvalide
     */
    public function analyser(string $chemin, Saison $saison): Collection
    {
        return $this->lecteur->lire($chemin)
            ->map(fn ($ligne) => $this->validateur->valider($ligne, $saison));
    }

    /** @param Collection<int, VerdictLigne> $verdicts */
    public function importer(Collection $verdicts, Saison $saison, bool $avecAvertissements): int
    {
        $aImporter = $verdicts->filter(fn (VerdictLigne $verdict) => match ($verdict->niveau) {
            NiveauVerdict::Valide => true,
            NiveauVerdict::Avertissement => $avecAvertissements,
            NiveauVerdict::Rejet => false,
        });

        DB::transaction(function () use ($aImporter, $saison) {
            foreach ($aImporter as $verdict) {
                $this->enregistreur->creer($verdict->ligne, $saison);
            }
        });

        return $aImporter->count();
    }
}
