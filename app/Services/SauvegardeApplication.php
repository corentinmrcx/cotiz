<?php

namespace App\Services;

use App\Enums\CleReglage;
use App\Exceptions\SauvegardeInvalide;
use App\Models\Reglage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class SauvegardeApplication
{
    private const VERSION = 1;

    private const FICHIER_DONNEES = 'cotiz-sauvegarde.json';

    private const DOSSIERS_FICHIERS = ['visuels', 'cartes'];

    public function __construct(private EtatSauvegarde $etat) {}

    public function exporter(): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'cotiz-sauvegarde-').'.zip';
        $archive = new ZipArchive;

        if ($archive->open($chemin, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l\'archive ZIP.');
        }

        $archive->addFromString(self::FICHIER_DONNEES, json_encode($this->donnees(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        foreach (self::DOSSIERS_FICHIERS as $dossier) {
            foreach (Storage::disk('data')->allFiles($dossier) as $fichier) {
                $archive->addFile(Storage::disk('data')->path($fichier), $fichier);
            }
        }

        $archive->close();
        $this->etat->marquerExportee();

        return $chemin;
    }

    public function nomArchive(): string
    {
        return 'CoTiz_Sauvegarde_'.now()->format('Y-m-d').'.zip';
    }

    public function restaurer(string $cheminArchive): void
    {
        $archive = new ZipArchive;

        if ($archive->open($cheminArchive) !== true) {
            throw new SauvegardeInvalide('Le fichier ne peut pas être lu comme une archive ZIP.');
        }

        $donnees = $this->lireDonnees($archive);

        DB::transaction(function () use ($donnees) {
            $this->remplacerTables($donnees);
            $this->remplacerReglages($donnees['reglages']);
        });

        $this->remplacerFichiers($archive);
        $archive->close();

        $this->etat->marquerExportee();
    }

    private function donnees(): array
    {
        return [
            'version' => self::VERSION,
            'exportee_le' => now()->toIso8601String(),
            'saisons' => DB::table('saisons')->get()->map(fn ($ligne) => (array) $ligne)->all(),
            'adhesions' => DB::table('adhesions')->get()->map(fn ($ligne) => (array) $ligne)->all(),
            'destinataires' => DB::table('destinataires')->get()->map(fn ($ligne) => (array) $ligne)->all(),
            'reglages' => Reglage::tous(),
        ];
    }

    private function lireDonnees(ZipArchive $archive): array
    {
        $contenu = $archive->getFromName(self::FICHIER_DONNEES);

        if ($contenu === false) {
            throw new SauvegardeInvalide('Cette archive n\'est pas une sauvegarde CoTiz : fichier '.self::FICHIER_DONNEES.' absent.');
        }

        $donnees = json_decode($contenu, true);

        if (! is_array($donnees) || ($donnees['version'] ?? null) !== self::VERSION) {
            throw new SauvegardeInvalide('Le format de la sauvegarde n\'est pas reconnu.');
        }

        return $donnees;
    }

    private function remplacerTables(array $donnees): void
    {
        foreach (['destinataires', 'adhesions', 'saisons'] as $table) {
            DB::table($table)->delete();
        }

        foreach (['saisons', 'adhesions', 'destinataires'] as $table) {
            foreach (array_chunk($donnees[$table] ?? [], 100) as $lot) {
                DB::table($table)->insert($lot);
            }
        }
    }

    private function remplacerReglages(array $reglages): void
    {
        DB::table('reglages')->delete();

        foreach ($reglages as $cle => $valeur) {
            $cleReglage = CleReglage::tryFrom($cle);

            if ($cleReglage !== null) {
                Reglage::definir($cleReglage, $valeur);
            }
        }
    }

    private function remplacerFichiers(ZipArchive $archive): void
    {
        foreach (self::DOSSIERS_FICHIERS as $dossier) {
            Storage::disk('data')->deleteDirectory($dossier);
        }

        for ($index = 0; $index < $archive->numFiles; $index++) {
            $nom = $archive->getNameIndex($index);

            if ($this->estUnFichierDeDonnees($nom)) {
                Storage::disk('data')->put($nom, $archive->getFromIndex($index));
            }
        }
    }

    private function estUnFichierDeDonnees(string $nom): bool
    {
        if (str_contains($nom, '..')) {
            return false;
        }

        foreach (self::DOSSIERS_FICHIERS as $dossier) {
            if (str_starts_with($nom, $dossier.'/') && ! str_ends_with($nom, '/')) {
                return true;
            }
        }

        return false;
    }
}
