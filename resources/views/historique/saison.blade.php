<x-layouts.app titre="Saison {{ $saison->libelle }}">
    <h1>Saison {{ $saison->libelle }}</h1>

    <section class="carte">
        <div class="barre-outils">
            <div>{{ $adhesions->count() }} adhésion(s)</div>
            <div class="actions">
                <a class="bouton secondaire" href="{{ route('historique') }}">Retour</a>
                <a class="bouton" href="{{ route('historique.export', $saison) }}">Exporter (ZIP)</a>
            </div>
        </div>
        <table class="tableau">
            <thead>
                <tr><th>N°</th><th>Adhérent</th><th>Effectifs</th><th>Cotisation</th><th>Encaissé</th><th>Destinataires</th><th>Statut</th><th>Carte</th></tr>
            </thead>
            <tbody>
                @forelse ($adhesions as $adhesion)
                    <tr>
                        <td>{{ $adhesion->numero }}</td>
                        <td>{{ $adhesion->nomComplet() }}</td>
                        <td>{{ $adhesion->nb_adultes }} ad. / {{ $adhesion->nbEnfants() }} enf.</td>
                        <td>{{ \App\Services\FormateurMontant::euros($adhesion->cotisation_calculee) }} €</td>
                        <td>{{ $adhesion->montant_encaisse === null ? '—' : \App\Services\FormateurMontant::euros($adhesion->montant_encaisse).' €' }}</td>
                        <td>{{ implode(', ', $adhesion->emailsDestinataires()) }}</td>
                        <td><x-statut :statut="$adhesion->statut" :titre="$adhesion->erreur_envoi" /> @if ($adhesion->date_envoi) <small class="aide-ligne">{{ $adhesion->date_envoi->format('d/m/Y H:i') }}</small> @endif</td>
                        <td>
                            @if ($adhesion->chemin_pdf)
                                <a href="{{ route('cartes.fichier', [$adhesion, 'pdf']) }}" target="_blank">PDF</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Aucune adhésion.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layouts.app>
