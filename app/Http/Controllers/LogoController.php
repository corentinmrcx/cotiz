<?php

namespace App\Http\Controllers;

use App\Models\Saison;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogoController extends Controller
{
    public function afficher(Saison $saison): StreamedResponse
    {
        $chemin = $saison->logo;

        abort_if($chemin === null || ! Storage::disk('data')->exists($chemin), 404);

        return Storage::disk('data')->response($chemin);
    }
}
