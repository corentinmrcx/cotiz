<?php

namespace App\Http\Controllers;

use App\Models\Saison;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisuelController extends Controller
{
    public function afficher(Saison $saison, string $face): StreamedResponse
    {
        $chemin = $face === 'recto' ? $saison->visuel_recto : $saison->visuel_verso;

        abort_if($chemin === null || ! Storage::disk('data')->exists($chemin), 404);

        return Storage::disk('data')->response($chemin);
    }
}
