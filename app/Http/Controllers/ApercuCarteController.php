<?php

namespace App\Http\Controllers;

use App\Models\Adhesion;
use App\Models\Saison;
use App\Services\RenduCarte;
use Illuminate\Http\Response;

class ApercuCarteController extends Controller
{
    public function afficher(RenduCarte $rendu): Response
    {
        $saison = Saison::active();

        abort_if($saison === null, 404);

        return response($rendu->html($this->adhesionFictive($saison)));
    }

    private function adhesionFictive(Saison $saison): Adhesion
    {
        $adhesion = new Adhesion([
            'nom' => 'Dupont',
            'prenom' => null,
            'nb_adultes' => 1,
            'nb_enfants_famille' => 0,
            'nb_enfants_seuls' => 0,
            'cotisation_calculee' => $saison->tarif_adulte,
        ]);

        $adhesion->setRelation('saison', $saison);

        return $adhesion;
    }
}
