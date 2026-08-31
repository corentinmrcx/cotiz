<?php

namespace App\Services;

use App\Models\Adhesion;
use App\Models\Saison;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class ExportateurSaison
{
    public function exporter(Saison $saison): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'cotiz-export-').'.zip';
        $archive = new ZipArchive;

        if ($archive->open($chemin, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l\'archive ZIP.');
        }

        $archive->addFromString('adhesions_'.$saison->libelle.'.csv', $this->csv($saison));

        foreach ($saison->adhesions()->whereNotNull('chemin_pdf')->get() as $adhesion) {
            if (Storage::disk('data')->exists($adhesion->chemin_pdf)) {
                $archive->addFile(Storage::disk('data')->path($adhesion->chemin_pdf), 'cartes/'.basename($adhesion->chemin_pdf));
            }
        }

        $archive->close();

        return $chemin;
    }

    public function nomArchive(Saison $saison): string
    {
        return 'CoTiz_'.$saison->libelle.'.zip';
    }

    private function csv(Saison $saison): string
    {
        $flux = fopen('php://temp', 'r+');
        fwrite($flux, "\xEF\xBB\xBF");
        fputcsv($flux, ['Numero', 'Nom', 'Prenom', 'Emails', 'Nb_adultes', 'Nb_enfants_famille', 'Nb_enfants_seuls', 'Cotisation', 'Montant_encaisse', 'Mode_reglement', 'Date_reglement', 'Statut', 'Date_envoi', 'Fichier_PDF'], ';');

        foreach ($saison->adhesions()->with('destinataires')->orderBy('numero')->get() as $adhesion) {
            fputcsv($flux, $this->ligneCsv($adhesion), ';');
        }

        rewind($flux);
        $contenu = stream_get_contents($flux);
        fclose($flux);

        return $contenu;
    }

    private function ligneCsv(Adhesion $adhesion): array
    {
        return [
            $adhesion->numero,
            mb_strtoupper($adhesion->nom),
            $adhesion->prenom,
            implode(', ', $adhesion->emailsDestinataires()),
            $adhesion->nb_adultes,
            $adhesion->nb_enfants_famille,
            $adhesion->nb_enfants_seuls,
            str_replace('.', ',', $adhesion->cotisation_calculee),
            $adhesion->montant_encaisse === null ? '' : str_replace('.', ',', $adhesion->montant_encaisse),
            $adhesion->mode_reglement,
            $adhesion->date_reglement?->format('d/m/Y'),
            $adhesion->statut->libelle(),
            $adhesion->date_envoi?->format('d/m/Y H:i'),
            $adhesion->chemin_pdf === null ? '' : basename($adhesion->chemin_pdf),
        ];
    }
}
