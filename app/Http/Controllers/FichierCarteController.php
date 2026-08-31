<?php

namespace App\Http\Controllers;

use App\Models\Adhesion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FichierCarteController extends Controller
{
    public function afficher(Adhesion $adhesion, string $format): StreamedResponse
    {
        $chemin = $format === 'pdf' ? $adhesion->chemin_pdf : $adhesion->chemin_png;

        abort_if($chemin === null || ! Storage::disk('data')->exists($chemin), 404);

        return Storage::disk('data')->response($chemin);
    }
}
