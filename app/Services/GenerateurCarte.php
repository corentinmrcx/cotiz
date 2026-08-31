<?php

namespace App\Services;

use App\Enums\CleReglage;
use App\Models\Adhesion;
use App\Models\Reglage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class GenerateurCarte
{
    private const PIXELS_PAR_MILLIMETRE = 96 / 25.4;

    public function __construct(private RenduCarte $rendu) {}

    public function generer(Adhesion $adhesion): void
    {
        $html = $this->rendu->html($adhesion);
        $base = $this->cheminSansExtension($adhesion);

        $this->disque()->makeDirectory(dirname($base));

        $this->navigateur($html)
            ->paperSize($this->enMillimetres(config('cotiz.carte.largeur')), $this->enMillimetres(config('cotiz.carte.hauteur')))
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->save($this->disque()->path($base.'.pdf'));

        $this->navigateur($html)
            ->windowSize(config('cotiz.carte.largeur'), config('cotiz.carte.hauteur') * 2)
            ->deviceScaleFactor(config('cotiz.carte.facteur_echelle'))
            ->fullPage()
            ->save($this->disque()->path($base.'.png'));

        $adhesion->update([
            'chemin_pdf' => $base.'.pdf',
            'chemin_png' => $base.'.png',
        ]);
    }

    public function nomFichier(Adhesion $adhesion): string
    {
        $association = NormalisateurNomFichier::nomCompact(Reglage::valeur(CleReglage::AssoNom, ''));
        $adherent = NormalisateurNomFichier::segment(mb_strtoupper($adhesion->nom).' '.ucfirst((string) $adhesion->prenom));

        return sprintf('CarteAdherent_%s_%s_%s', $association, $adhesion->saison->libelle, $adherent);
    }

    private function cheminSansExtension(Adhesion $adhesion): string
    {
        return 'cartes/'.$adhesion->saison->libelle.'/'.$this->nomFichier($adhesion);
    }

    private function navigateur(string $html): Browsershot
    {
        return Browsershot::html($html)
            ->setChromePath(config('cotiz.chrome_path'))
            ->noSandbox()
            ->timeout(60);
    }

    private function enMillimetres(int $pixels): float
    {
        return $pixels / self::PIXELS_PAR_MILLIMETRE;
    }

    private function disque(): Filesystem
    {
        return Storage::disk('data');
    }
}
