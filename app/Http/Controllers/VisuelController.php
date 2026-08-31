<?php

namespace App\Http\Controllers;

use App\Models\Saison;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisuelController extends Controller
{
    public function logo(Saison $saison): StreamedResponse
    {
        $chemin = $saison->logo;

        abort_if($chemin === null || ! Storage::disk('data')->exists($chemin), 404);

        return Storage::disk('data')->response($chemin);
    }
}
