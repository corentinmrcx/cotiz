<?php

namespace App\Http\Controllers;

use App\Models\Saison;
use App\Services\GenerateurClasseurModele;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClasseurModeleController extends Controller
{
    public function telecharger(GenerateurClasseurModele $generateur): BinaryFileResponse
    {
        $saison = Saison::active();

        abort_if($saison === null, 404);

        $chemin = tempnam(sys_get_temp_dir(), 'cotiz-modele-').'.xlsx';
        $generateur->generer($saison, $chemin);

        return response()
            ->download($chemin, 'CoTiz_Classeur_Adhesions_MODELE_'.$saison->libelle.'.xlsx')
            ->deleteFileAfterSend();
    }
}
