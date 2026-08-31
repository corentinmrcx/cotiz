@if ($lignes === [])
    <p class="aide">Aucune ligne.</p>
@else
    <table class="tableau">
        <thead>
            <tr><th>Ligne</th><th>Adhérent</th><th>Destinataires</th><th>Effectifs</th><th>Cotisation</th><th>Encaissé</th><th>Motifs</th></tr>
        </thead>
        <tbody>
            @foreach ($lignes as $ligne)
                <tr>
                    <td>{{ $ligne['ligne'] }}</td>
                    <td>{{ $ligne['nom'] }}</td>
                    <td>{{ $ligne['emails'] }}</td>
                    <td>{{ $ligne['effectifs'] }}</td>
                    <td>{{ \App\Services\FormateurMontant::euros($ligne['cotisation']) }} €</td>
                    <td>{{ $ligne['encaisse'] === null ? '—' : \App\Services\FormateurMontant::euros($ligne['encaisse']).' €' }}</td>
                    <td>{{ implode(' ', $ligne['motifs']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
