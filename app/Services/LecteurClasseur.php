<?php

namespace App\Services;

use App\Dto\LigneAdhesion;
use App\Exceptions\ClasseurInvalide;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class LecteurClasseur
{
    public const ONGLET = 'ADHESIONS';

    public const COLONNES_OBLIGATOIRES = [
        'Nom', 'Prenom', 'Nb_adultes', 'Nb_enfants_famille', 'Nb_enfants_seuls',
        'Date_reglement', 'Mode_reglement', 'Montant_encaisse',
    ];

    /** @return Collection<int, LigneAdhesion> */
    public function lire(string $chemin): Collection
    {
        $feuille = $this->ongletAdhesions($chemin);
        $colonnes = $this->colonnesParEntete($feuille);

        $lignes = collect();

        foreach ($feuille->getRowIterator(2) as $ligne) {
            $numeroLigne = $ligne->getRowIndex();
            $valeurs = fn (string $entete) => $this->valeurCellule($feuille, $colonnes[$entete], $numeroLigne);

            if ($this->ligneVide($feuille, $colonnes, $numeroLigne)) {
                continue;
            }

            $lignes->push(new LigneAdhesion(
                numeroLigne: $numeroLigne,
                nom: trim((string) $valeurs('Nom')),
                prenom: $this->texteOuNull($valeurs('Prenom')),
                emails: $this->emails($feuille, $colonnes, $numeroLigne),
                nbAdultes: $this->entier($valeurs('Nb_adultes')),
                nbEnfantsFamille: $this->entier($valeurs('Nb_enfants_famille')),
                nbEnfantsSeuls: $this->entier($valeurs('Nb_enfants_seuls')),
                montantEncaisse: $this->montant($valeurs('Montant_encaisse')),
                modeReglement: $this->texteOuNull($valeurs('Mode_reglement')),
                dateReglement: $this->date($feuille->getCell($colonnes['Date_reglement'].$numeroLigne)),
            ));
        }

        return $lignes;
    }

    private function ongletAdhesions(string $chemin): Worksheet
    {
        try {
            $classeur = IOFactory::load($chemin);
        } catch (Throwable $exception) {
            throw new ClasseurInvalide('Le fichier ne peut pas être lu comme un classeur : '.$exception->getMessage());
        }

        if ($this->estCsv($chemin)) {
            return $classeur->getActiveSheet();
        }

        $feuille = $classeur->getSheetByName(self::ONGLET);

        if ($feuille === null) {
            throw new ClasseurInvalide(sprintf('L\'onglet « %s » est absent du classeur.', self::ONGLET));
        }

        return $feuille;
    }

    /** @return array<string, string> entête → lettre de colonne */
    private function colonnesParEntete(Worksheet $feuille): array
    {
        $colonnes = [];

        foreach ($feuille->getRowIterator(1, 1) as $ligne) {
            foreach ($ligne->getCellIterator() as $cellule) {
                $entete = trim((string) $cellule->getValue());

                if ($entete !== '') {
                    $colonnes[$entete] = $cellule->getColumn();
                }
            }
        }

        $manquantes = array_diff(self::COLONNES_OBLIGATOIRES, array_keys($colonnes));

        if ($manquantes !== []) {
            throw new ClasseurInvalide('Colonnes manquantes en ligne 1 : '.implode(', ', $manquantes).'.');
        }

        if ($this->colonnesEmail($colonnes) === []) {
            throw new ClasseurInvalide('Aucune colonne dont l\'en-tête commence par « Email » en ligne 1.');
        }

        return $colonnes;
    }

    /** @param array<string, string> $colonnes */
    private function colonnesEmail(array $colonnes): array
    {
        return array_filter($colonnes, fn (string $entete) => str_starts_with($entete, 'Email'), ARRAY_FILTER_USE_KEY);
    }

    /** @param array<string, string> $colonnes */
    private function emails(Worksheet $feuille, array $colonnes, int $numeroLigne): array
    {
        $emails = [];

        foreach ($this->colonnesEmail($colonnes) as $lettre) {
            $email = strtolower(trim((string) $this->valeurCellule($feuille, $lettre, $numeroLigne)));

            if ($email !== '') {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }

    /** @param array<string, string> $colonnes */
    private function ligneVide(Worksheet $feuille, array $colonnes, int $numeroLigne): bool
    {
        foreach ($colonnes as $lettre) {
            if (trim((string) $this->valeurCellule($feuille, $lettre, $numeroLigne)) !== '') {
                return false;
            }
        }

        return true;
    }

    private function valeurCellule(Worksheet $feuille, string $lettre, int $numeroLigne): mixed
    {
        return $feuille->getCell($lettre.$numeroLigne)->getCalculatedValue();
    }

    private function texteOuNull(mixed $valeur): ?string
    {
        $texte = trim((string) $valeur);

        return $texte === '' ? null : $texte;
    }

    private function entier(mixed $valeur): int
    {
        return (int) round((float) str_replace(',', '.', trim((string) $valeur)));
    }

    private function montant(mixed $valeur): ?float
    {
        $texte = str_replace([' ', '€', ','], ['', '', '.'], trim((string) $valeur));

        return is_numeric($texte) ? round((float) $texte, 2) : null;
    }

    private function date(Cell $cellule): ?CarbonImmutable
    {
        $valeur = $cellule->getValue();

        if ($valeur === null || trim((string) $valeur) === '') {
            return null;
        }

        if (is_numeric($valeur) && Date::isDateTime($cellule)) {
            return CarbonImmutable::instance(Date::excelToDateTimeObject((float) $valeur));
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y'] as $format) {
            $date = CarbonImmutable::createFromFormat($format, trim((string) $valeur));

            if ($date !== false) {
                return $date->startOfDay();
            }
        }

        return null;
    }

    private function estCsv(string $chemin): bool
    {
        return strtolower(pathinfo($chemin, PATHINFO_EXTENSION)) === 'csv';
    }
}
