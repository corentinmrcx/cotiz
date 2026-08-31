<section class="carte">
    <h2>{{ $adhesion ? 'Modifier l\'adhésion '.$adhesion->numero : 'Nouvelle adhésion' }}</h2>
    <p class="aide">La cotisation est calculée depuis les tarifs de la saison, elle n'est jamais saisie.</p>

    <form wire:submit="enregistrer" class="formulaire">
        <div class="grille-2">
            <label>Nom
                <input type="text" wire:model="form.nom">
                @error('form.nom') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
            <label>Prénom
                <input type="text" wire:model="form.prenom">
                @error('form.prenom') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
        </div>
        <label>Adresses mail (séparées par des virgules)
            <input type="text" wire:model="form.emails">
            @error('form.emails') <span class="champ-erreur">{{ $message }}</span> @enderror
        </label>
        <div class="grille-3">
            <label>Adultes
                <input type="number" min="0" wire:model="form.nb_adultes">
                @error('form.nb_adultes') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
            <label>Enfants en famille
                <input type="number" min="0" wire:model="form.nb_enfants_famille">
                @error('form.nb_enfants_famille') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
            <label>Enfants seuls
                <input type="number" min="0" wire:model="form.nb_enfants_seuls">
                @error('form.nb_enfants_seuls') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
        </div>
        <div class="grille-2">
            <label>Mode de règlement
                <select wire:model="form.mode_reglement">
                    <option value="">—</option>
                    @foreach (['Chèque', 'Espèces', 'Virement', 'CB'] as $mode)
                        <option value="{{ $mode }}">{{ $mode }}</option>
                    @endforeach
                </select>
            </label>
            <label>Date de règlement
                <input type="date" wire:model="form.date_reglement">
                @error('form.date_reglement') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
        </div>
        <div class="actions">
            <button type="submit" class="bouton">Enregistrer</button>
            <a class="bouton secondaire" href="{{ route('adhesions') }}">Annuler</a>
        </div>
    </form>
</section>
