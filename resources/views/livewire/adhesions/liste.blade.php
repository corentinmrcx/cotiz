<div>
    @if ($message)
        <p class="message succes">{{ $message }}</p>
    @endif

    @if (! $saison)
        <p class="message erreur">Aucune saison active. Créez-en une dans les Réglages.</p>
    @else
        @if ($traitement)
            <section class="carte" wire:poll.{{ $traitement === \App\Livewire\Adhesions\Liste::ENVOI ? $delaiSecondes.'s' : '500ms' }}="traiterSuivante">
                <h2>{{ $traitement === \App\Livewire\Adhesions\Liste::ENVOI ? 'Envoi des cartes' : 'Génération des cartes en test' }}</h2>
                <div class="progression"><div class="progression-barre" style="width: {{ $nbTotal ? round(100 * $nbTraitees / $nbTotal) : 0 }}%"></div></div>
                <p>{{ $nbTraitees }} / {{ $nbTotal }} — ne fermez pas cette page.</p>
                <button type="button" class="bouton secondaire" wire:click="interrompre">Interrompre</button>
            </section>
        @endif

        <section class="carte">
            <div class="barre-outils">
                <div><strong>{{ $saison->libelle }}</strong> — {{ $adhesions->count() }} adhésion(s)</div>
                <label class="filtre">Statut
                    <select wire:model.live="statut">
                        <option value="">Tous</option>
                        @foreach ($statuts as $statutPossible)
                            <option value="{{ $statutPossible->value }}">{{ $statutPossible->libelle() }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="actions">
                    <a class="bouton secondaire" href="{{ route('adhesions.nouvelle') }}">Ajouter</a>
                    <button type="button" class="bouton secondaire" wire:click="demarrerGenerationTest" @disabled($traitement)>Générer les cartes en test</button>
                    <button type="button" class="bouton" wire:click="demarrerEnvoi" wire:confirm="Envoyer les cartes par mail à toutes les adhésions non envoyées ?" @disabled($traitement)>Envoyer les cartes</button>
                </div>
            </div>

            <table class="tableau">
                <thead>
                    <tr><th>N°</th><th>Adhérent</th><th>Effectifs</th><th>Cotisation</th><th>Destinataires</th><th>Statut</th><th>Carte</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($adhesions as $adhesion)
                        <tr>
                            <td>{{ $adhesion->numero }}</td>
                            <td class="nowrap">{{ $adhesion->nomComplet() }}</td>
                            <td class="nowrap">{{ $adhesion->nb_adultes }} ad. / {{ $adhesion->nbEnfants() }} enf.</td>
                            <td>{{ \App\Services\FormateurMontant::euros($adhesion->cotisation_calculee) }} €</td>
                            <td>{{ implode(', ', $adhesion->emailsDestinataires()) }}</td>
                            <td class="nowrap">
                                <x-statut :statut="$adhesion->statut" />
                                @if ($adhesion->date_envoi)
                                    <small class="aide-ligne">{{ $adhesion->date_envoi->format('d/m/Y H:i') }}</small>
                                @endif
                                @if ($adhesion->erreur_envoi)
                                    <small class="aide-ligne erreur-ligne" title="{{ $adhesion->erreur_envoi }}">{{ \Illuminate\Support\Str::limit($adhesion->erreur_envoi, 80) }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($adhesion->chemin_pdf)
                                    <a href="{{ route('cartes.fichier', [$adhesion, 'pdf']) }}" target="_blank">PDF</a>
                                    · <a href="{{ route('cartes.fichier', [$adhesion, 'png']) }}" target="_blank">PNG</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="actions-ligne">
                                <details class="menu-actions">
                                    <summary title="Actions">⋯</summary>
                                    <div class="menu-actions-liste">
                                        <button type="button" wire:click="renvoyer({{ $adhesion->id }})" wire:confirm="Envoyer la carte {{ $adhesion->numero }} par mail ?" @disabled($traitement)>{{ $adhesion->statut === \App\Enums\StatutAdhesion::AEnvoyer ? 'Envoyer' : 'Renvoyer' }}</button>
                                        <a href="{{ route('adhesions.modifier', $adhesion) }}">Modifier</a>
                                        <button type="button" class="danger" wire:click="supprimer({{ $adhesion->id }})" wire:confirm="Supprimer l'adhésion {{ $adhesion->numero }} ?">Supprimer</button>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">Aucune adhésion.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</div>
