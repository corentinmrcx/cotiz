<?php

namespace App\Services;

use App\Models\Saison;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateurClasseurModele
{
    private const NB_LIGNES = 300;

    private const JAUNE = 'FFF9E7A3';

    private const GRIS = 'FFE0E0E0';

    private const COLONNES = [
        'A' => 'Nom', 'B' => 'Prenom', 'C' => 'Email_1', 'D' => 'Email_2',
        'E' => 'Nb_adultes', 'F' => 'Nb_enfants_famille', 'G' => 'Nb_enfants_seuls',
        'H' => 'Cotisation', 'I' => 'Date_reglement', 'J' => 'Mode_reglement',
        'K' => 'Montant_encaisse', 'L' => 'Ecart', 'M' => 'Commentaire',
    ];

    public function generer(Saison $saison, string $cheminSortie): void
    {
        $classeur = new Spreadsheet;

        $this->remplirAdhesions($classeur->getActiveSheet());
        $this->remplirInfos($classeur->createSheet(), $saison);

        $classeur->setActiveSheetIndex(0);

        (new Xlsx($classeur))->save($cheminSortie);
    }

    private function remplirAdhesions(Worksheet $feuille): void
    {
        $feuille->setTitle(LecteurClasseur::ONGLET);
        $derniere = self::NB_LIGNES + 1;

        foreach (self::COLONNES as $lettre => $entete) {
            $feuille->setCellValue($lettre.'1', $entete);
            $feuille->getColumnDimension($lettre)->setWidth(18);
        }

        $feuille->getStyle('A1:M1')->getFont()->setBold(true);
        $feuille->freezePane('A2');

        $feuille->getStyle("A2:G{$derniere}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::JAUNE);
        $feuille->getStyle("I2:K{$derniere}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::JAUNE);
        $feuille->getStyle("M2:M{$derniere}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::JAUNE);
        $feuille->getStyle("H2:H{$derniere}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::GRIS);
        $feuille->getStyle("L2:L{$derniere}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::GRIS);

        for ($ligne = 2; $ligne <= $derniere; $ligne++) {
            $feuille->setCellValue("H{$ligne}", "=IF(COUNTA(A{$ligne}:G{$ligne})=0,\"\",E{$ligne}*INFOS!\$B\$2+F{$ligne}*INFOS!\$B\$3+G{$ligne}*INFOS!\$B\$4)");
            $feuille->setCellValue("L{$ligne}", "=IF(OR(K{$ligne}=\"\",H{$ligne}=\"\"),\"\",K{$ligne}-H{$ligne})");
        }

        $feuille->getStyle("H2:H{$derniere}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $feuille->getStyle("K2:L{$derniere}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $feuille->getStyle("I2:I{$derniere}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);

        $this->validationEntierPositif($feuille, "E2:G{$derniere}");
        $this->validationListe($feuille, "J2:J{$derniere}", '"Chèque,Espèces,Virement,CB"');
        $this->ecartEnRouge($feuille, "L2:L{$derniere}");
    }

    private function remplirInfos(Worksheet $feuille, Saison $saison): void
    {
        $feuille->setTitle('INFOS');
        $feuille->getColumnDimension('A')->setWidth(34);
        $feuille->getColumnDimension('B')->setWidth(16);

        $feuille->fromArray([
            ['Tarif', 'Montant (€)'],
            ['Adulte', (float) $saison->tarif_adulte],
            ['Enfant en famille', (float) $saison->tarif_enfant_famille],
            ['Enfant seul', (float) $saison->tarif_enfant_seul],
            [],
            ['Totaux', ''],
            ['Nombre d\'adhésions', '=COUNTA(ADHESIONS!A2:A'.(self::NB_LIGNES + 1).')'],
            ['Total des cotisations', '=SUM(ADHESIONS!H2:H'.(self::NB_LIGNES + 1).')'],
            ['Total encaissé', '=SUM(ADHESIONS!K2:K'.(self::NB_LIGNES + 1).')'],
            [],
            ['Notice', ''],
            ['Saison', $saison->libelle],
            ['Cases jaunes : à remplir. Cases grises : calculées automatiquement.', ''],
            ['Nb_adultes, Nb_enfants_famille, Nb_enfants_seuls : nombre de personnes, pas un montant.', ''],
            ['Email_1 obligatoire, Email_2 facultatif. Une colonne Email_3 peut être ajoutée.', ''],
            ['Cotisation et Ecart se calculent seuls. Un écart non nul s\'affiche en rouge.', ''],
            ['Les tarifs ci-dessus servent au contrôle. Ceux de CoTiz font foi.', ''],
            [],
            ['Exemple de saisie', ''],
            ['DUPONT | Marie | marie@exemple.fr | | 2 | 1 | 0 | Chèque | 19', ''],
        ], null, 'A1');

        $feuille->getStyle('A1:B1')->getFont()->setBold(true);
        $feuille->getStyle('A6')->getFont()->setBold(true);
        $feuille->getStyle('A11')->getFont()->setBold(true);
        $feuille->getStyle('A19')->getFont()->setBold(true);
        $feuille->getStyle('B2:B4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::JAUNE);
        $feuille->getStyle('B7:B9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::GRIS);
        $feuille->getStyle('B2:B4')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $feuille->getStyle('B8:B9')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
    }

    private function validationEntierPositif(Worksheet $feuille, string $plage): void
    {
        $validation = new DataValidation;
        $validation->setSqref($plage)
            ->setType(DataValidation::TYPE_WHOLE)
            ->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL)
            ->setFormula1('0')
            ->setAllowBlank(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Effectif invalide')
            ->setError('Saisir un nombre de personnes (entier positif), pas un montant.');

        $feuille->setDataValidation($this->premiereCellule($plage), $validation);
    }

    private function validationListe(Worksheet $feuille, string $plage, string $valeurs): void
    {
        $validation = new DataValidation;
        $validation->setSqref($plage)
            ->setType(DataValidation::TYPE_LIST)
            ->setFormula1($valeurs)
            ->setAllowBlank(true)
            ->setShowDropDown(true)
            ->setShowErrorMessage(true)
            ->setErrorTitle('Mode de règlement')
            ->setError('Choisir une valeur dans la liste.');

        $feuille->setDataValidation($this->premiereCellule($plage), $validation);
    }

    private function premiereCellule(string $plage): string
    {
        return explode(':', $plage)[0];
    }

    private function ecartEnRouge(Worksheet $feuille, string $plage): void
    {
        $condition = new Conditional;
        $condition->setConditionType(Conditional::CONDITION_CELLIS)
            ->setOperatorType(Conditional::OPERATOR_NOTEQUAL)
            ->addCondition('0');
        $condition->getStyle()->getFont()->getColor()->setARGB('FFC00000');
        $condition->getStyle()->getFont()->setBold(true);

        $feuille->getStyle($plage)->setConditionalStyles([$condition]);
    }
}
