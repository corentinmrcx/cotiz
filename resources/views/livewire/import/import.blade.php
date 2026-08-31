<div>
    @if ($message)
        <p class="message succes">{{ $message }}</p>
    @endif
    @if ($erreur)
        <p class="message erreur">{{ $erreur }}</p>
    @endif

    @if (! $saison)
        <p class="message erreur">Aucune saison active. Créez-en une dans les Réglages.</p>
    @elseif (! $analyse)
        <section class="carte">
            <h2>Déposer le classeur de la trésorière</h2>
            <p class="aide">
                Saison active : <strong>{{ $saison->libelle }}</strong>. Seul l'onglet <code>ADHESIONS</code> est lu.
                <a href="{{ route('classeur.modele') }}">Télécharger le classeur modèle</a> avec les tarifs de la saison.
            </p>
            <form wire:submit="analyser" class="formulaire">
                <label>Classeur (.xlsx)
                    <input type="file" accept=".xlsx,.csv" wire:model="fichier">
                    @error('fichier') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
                <div class="actions">
                    <button type="submit" class="bouton" wire:loading.attr="disabled">Analyser</button>
                    <span wire:loading wire:target="fichier">Téléversement…</span>
                    <span wire:loading wire:target="analyser">Analyse en cours…</span>
                </div>
            </form>
        </section>
    @else
        <section class="carte">
            <h2>Contrôle avant import</h2>
            <p class="aide">Rien n'est enregistré tant que vous n'avez pas validé. Effectifs : adultes / enfants en famille / enfants seuls.</p>

            <h3><span class="badge vert">{{ count($groupes['valide']) }}</span> Lignes valides</h3>
            @include('livewire.import.groupe', ['lignes' => $groupes['valide']])

            <h3><span class="badge orange">{{ count($groupes['avertissement']) }}</span> Avertissements</h3>
            @include('livewire.import.groupe', ['lignes' => $groupes['avertissement']])
            @if ($groupes['avertissement'] !== [])
                <label class="case">
                    <input type="checkbox" wire:model.live="importerAvertissements">
                    Importer aussi les lignes en avertissement
                </label>
            @endif

            <h3><span class="badge rouge">{{ count($groupes['rejet']) }}</span> Lignes rejetées</h3>
            @include('livewire.import.groupe', ['lignes' => $groupes['rejet']])

            <div class="actions">
                <button type="button" class="bouton" wire:click="importer" wire:loading.attr="disabled" @disabled($nbImportables === 0)>
                    Importer {{ $nbImportables }} adhésion(s)
                </button>
                <button type="button" class="bouton secondaire" wire:click="annuler">Annuler</button>
            </div>
        </section>
    @endif
</div>
