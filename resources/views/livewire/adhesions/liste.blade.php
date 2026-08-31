<div>
    @if ($message)
        <p class="message succes">{{ $message }}</p>
    @endif

    @if (! $saison)
        <p class="message erreur">Aucune saison active. Créez-en une dans les Réglages.</p>
    @else
        <section class="carte">
            <div class="barre-outils">
                <div>
                    <strong>{{ $saison->libelle }}</strong> — {{ $adhesions->count() }} adhésion(s)
                </div>
                <label class="filtre">Statut
                    <select wire:model.live="statut">
                        <option value="">Tous</option>
                        @foreach ($statuts as $statutPossible)
                            <option value="{{ $statutPossible->value }}">{{ $statutPossible->libelle() }}</option>
                        @endforeach
                    </select>
                </label>
                <a class="bouton secondaire" href="{{ route('adhesions.nouvelle') }}">Ajouter une adhésion</a>
            </div>

            <table class="tableau">
                <thead>
                    <tr><th>N°</th><th>Adhérent</th><th>Effectifs</th><th>Cotisation</th><th>Destinataires</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($adhesions as $adhesion)
                        <tr>
                            <td>{{ $adhesion->numero }}</td>
                            <td>{{ $adhesion->nomComplet() }}</td>
                            <td>{{ $adhesion->nb_adultes }} ad. / {{ $adhesion->nbEnfants() }} enf.</td>
                            <td>{{ \App\Services\FormateurMontant::euros($adhesion->cotisation_calculee) }} €</td>
                            <td>{{ implode(', ', $adhesion->emailsDestinataires()) }}</td>
                            <td><x-statut :statut="$adhesion->statut" /></td>
                            <td class="actions-ligne">
                                <a class="bouton petit secondaire" href="{{ route('adhesions.modifier', $adhesion) }}">Modifier</a>
                                <button type="button" class="bouton petit danger" wire:click="supprimer({{ $adhesion->id }})" wire:confirm="Supprimer l'adhésion {{ $adhesion->numero }} ?">Supprimer</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Aucune adhésion.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</div>
