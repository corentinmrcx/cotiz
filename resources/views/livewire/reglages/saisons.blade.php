<section class="carte">
    <h2>Saison</h2>

    @if ($message)
        <p class="message succes">{{ $message }}</p>
    @endif

    <div class="grille-2">
        <div>
            <h3>Saisons existantes</h3>
            <table class="tableau">
                <thead>
                    <tr><th>Libellé</th><th>Tarifs</th><th>Adhésions</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($saisons as $saison)
                        <tr>
                            <td>{{ $saison->libelle }} @if ($saison->active) <span class="badge vert">Active</span> @endif</td>
                            <td class="nowrap">{{ $saison->tarif_adulte }} € / {{ $saison->tarif_enfant_famille }} € / {{ $saison->tarif_enfant_seul }} €</td>
                            <td>{{ $saison->adhesions_count }}</td>
                            <td class="actions-ligne">
                                @unless ($saison->active)
                                    <button type="button" class="bouton petit secondaire" wire:click="activer({{ $saison->id }})">Activer</button>
                                @endunless
                                @if ($saison->adhesions_count === 0)
                                    <button type="button" class="bouton petit danger" wire:click="supprimer({{ $saison->id }})" wire:confirm="Supprimer la saison {{ $saison->libelle }} ?">Supprimer</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if (! $ajoutOuvert)
                <button type="button" class="bouton secondaire" wire:click="$set('ajoutOuvert', true)">Ouvrir une nouvelle saison</button>
            @else
                <form wire:submit="ouvrirNouvelleSaison" class="formulaire encart">
                    <label>Année de début de la nouvelle saison
                        <input type="number" min="2000" max="2100" wire:model.live="nouvelleAnnee">
                        @error('nouvelleAnnee') <span class="champ-erreur">{{ $message }}</span> @enderror
                    </label>
                    <p class="aide">La saison <strong>{{ $libelleNouvelleSaison }}</strong> sera créée et activée. Les tarifs, le logo et la couleur de la saison active sont repris et restent modifiables.</p>
                    <div class="actions">
                        <button type="submit" class="bouton">Ouvrir et activer</button>
                        <button type="button" class="bouton secondaire" wire:click="$set('ajoutOuvert', false)">Annuler</button>
                    </div>
                </form>
            @endif
        </div>

        @if ($form->saison)
            <form wire:submit="enregistrer" class="formulaire">
                <h3>Saison active : {{ $form->saison->libelle }}</h3>
                <div class="grille-3">
                    <label>Tarif adulte (€)
                        <input type="number" step="0.01" min="0" wire:model="form.tarif_adulte">
                        @error('form.tarif_adulte') <span class="champ-erreur">{{ $message }}</span> @enderror
                    </label>
                    <label>Enfant en famille (€)
                        <input type="number" step="0.01" min="0" wire:model="form.tarif_enfant_famille">
                        @error('form.tarif_enfant_famille') <span class="champ-erreur">{{ $message }}</span> @enderror
                    </label>
                    <label>Enfant seul (€)
                        <input type="number" step="0.01" min="0" wire:model="form.tarif_enfant_seul">
                        @error('form.tarif_enfant_seul') <span class="champ-erreur">{{ $message }}</span> @enderror
                    </label>
                </div>
                <div class="grille-2">
                    <label>Couleur principale
                        <span class="couleur">
                            <input type="color" wire:model.live="form.couleur">
                            <code>{{ $form->couleur }}</code>
                        </span>
                        @error('form.couleur') <span class="champ-erreur">{{ $message }}</span> @enderror
                    </label>
                    <label>Logo (PNG ou SVG, blanc sur fond transparent)
                        @if ($form->saison->logo)
                            <span class="apercu-logo" style="background: {{ $form->couleur }}">
                                <img src="{{ route('saisons.logo', $form->saison) }}?v={{ $form->saison->updated_at->timestamp }}" alt="Logo actuel">
                            </span>
                        @endif
                        <input type="file" accept=".png,.svg,image/png,image/svg+xml" wire:model="form.logo">
                        @error('form.logo') <span class="champ-erreur">{{ $message }}</span> @enderror
                    </label>
                </div>
                <p class="aide"><a href="{{ route('cartes.apercu') }}" target="_blank">Aperçu de la carte</a> avec les réglages enregistrés.</p>
                <div class="actions">
                    <button type="submit" class="bouton" wire:loading.attr="disabled">Enregistrer la saison</button>
                    <span wire:loading wire:target="form.logo">Téléversement…</span>
                </div>
            </form>
        @endif
    </div>
</section>
