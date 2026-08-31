<x-layouts.app titre="Historique">
    <h1>Historique</h1>

    <section class="carte">
        <table class="tableau">
            <thead>
                <tr><th>Saison</th><th>Tarifs</th><th>Adhésions</th><th>Envoyées</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($saisons as $saison)
                    <tr>
                        <td>{{ $saison->libelle }} @if ($saison->active) <span class="badge vert">Active</span> @endif</td>
                        <td>{{ \App\Services\FormateurMontant::euros($saison->tarif_adulte) }} € / {{ \App\Services\FormateurMontant::euros($saison->tarif_enfant_famille) }} € / {{ \App\Services\FormateurMontant::euros($saison->tarif_enfant_seul) }} €</td>
                        <td>{{ $saison->adhesions_count }}</td>
                        <td>{{ $saison->envoyees_count }}</td>
                        <td class="actions-ligne">
                            <a class="bouton petit secondaire" href="{{ route('historique.saison', $saison) }}">Consulter</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="aide">La sauvegarde complète de l'outil se fait depuis l'écran Réglages.</p>
    </section>
</x-layouts.app>
