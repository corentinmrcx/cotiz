<?php

namespace App\Services;

use App\Enums\CleReglage;
use App\Enums\StatutAdhesion;
use App\Mail\CarteAdherentMail;
use App\Models\Adhesion;
use App\Models\Reglage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class EnvoyeurCarte
{
    public function __construct(
        private GenerateurCarte $generateur,
        private ComposeurMail $composeur,
        private ConfigurateurTransportSmtp $configurateur,
    ) {}

    public function envoyer(Adhesion $adhesion): void
    {
        try {
            $this->generateur->generer($adhesion);
            $this->configurateur->appliquerReglages();

            $this->destinataires($adhesion)->send($this->mail($adhesion));

            $adhesion->update([
                'statut' => StatutAdhesion::Envoye,
                'date_envoi' => now(),
                'erreur_envoi' => null,
            ]);
        } catch (Throwable $exception) {
            $adhesion->update([
                'statut' => StatutAdhesion::Echec,
                'erreur_envoi' => $exception->getMessage(),
            ]);
        }
    }

    private function destinataires(Adhesion $adhesion)
    {
        $envoi = Mail::to($adhesion->emailsDestinataires());

        if (Reglage::valeur(CleReglage::CopieCacheeActive) === '1') {
            $envoi->bcc(Reglage::valeur(CleReglage::ExpediteurEmail));
        }

        return $envoi;
    }

    private function mail(Adhesion $adhesion): CarteAdherentMail
    {
        return new CarteAdherentMail(
            objet: $this->composeur->objet($adhesion),
            corpsHtml: $this->composeur->corpsHtml($adhesion),
            corpsTexte: $this->composeur->corpsTexte($adhesion),
            cheminPng: Storage::disk('data')->path($adhesion->chemin_png),
            cheminPdf: Storage::disk('data')->path($adhesion->chemin_pdf),
            nomFichierPdf: basename($adhesion->chemin_pdf),
        );
    }
}
