<?php

namespace Tests\Support;

use App\Models\Saison;
use App\Services\GenerateurClasseurModele;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FabriqueClasseur
{
    /** @param array<int, array<int, mixed>> $lignes */
    public static function xlsx(Saison $saison, array $lignes): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'cotiz-test-').'.xlsx';

        (new GenerateurClasseurModele)->generer($saison, $chemin);

        $classeur = IOFactory::load($chemin);
        $classeur->getSheetByName('ADHESIONS')->fromArray($lignes, null, 'A2');
        (new Xlsx($classeur))->save($chemin);

        return $chemin;
    }

    /** @param array<int, array<int, mixed>> $lignes */
    public static function xlsxBrut(array $entetes, array $lignes, string $onglet = 'ADHESIONS'): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'cotiz-test-').'.xlsx';

        $classeur = new Spreadsheet;
        $feuille = $classeur->getActiveSheet();
        $feuille->setTitle($onglet);
        $feuille->fromArray($entetes, null, 'A1');
        $feuille->fromArray($lignes, null, 'A2');
        (new Xlsx($classeur))->save($chemin);

        return $chemin;
    }
}
