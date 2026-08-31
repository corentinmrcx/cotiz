<?php

namespace App\Http\Controllers;

use App\Enums\StatutAdhesion;
use App\Models\Saison;
use App\Services\ExportateurSaison;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HistoriqueController extends Controller
{
    public function index(): View
    {
        return view('historique.index', [
            'saisons' => Saison::query()
                ->withCount(['adhesions', 'adhesions as envoyees_count' => fn ($requete) => $requete->where('statut', StatutAdhesion::Envoye->value)])
                ->orderByDesc('libelle')
                ->get(),
        ]);
    }

    public function saison(Saison $saison): View
    {
        return view('historique.saison', [
            'saison' => $saison,
            'adhesions' => $saison->adhesions()->with('destinataires')->orderBy('numero')->get(),
        ]);
    }

    public function exporter(Saison $saison, ExportateurSaison $exportateur): BinaryFileResponse
    {
        return response()
            ->download($exportateur->exporter($saison), $exportateur->nomArchive($saison))
            ->deleteFileAfterSend();
    }
}
