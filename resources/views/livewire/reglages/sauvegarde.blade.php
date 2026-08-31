<section class="carte">
    <h2>Sauvegarde</h2>

    @if (session('message'))
        <p class="message succes">{{ session('message') }}</p>
    @endif
    @if ($erreur)
        <p class="message erreur">{{ $erreur }}</p>
    @endif

    <div class="grille-2">
        <div>
            <h3>Exporter</h3>
            <p class="aide">
                L'archive contient toutes les données : saisons, adhésions, réglages, logos et
                cartes générées. CoTiz est un outil local : exportez après chaque session de
                travail et déposez l'archive sur le Drive de l'association.
                @if ($derniereExportation)
                    <br>Dernière exportation : {{ $derniereExportation->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.
                @else
                    <br>Aucune exportation pour le moment.
                @endif
            </p>
            <a class="bouton" href="{{ route('sauvegarde.telecharger') }}">Télécharger la sauvegarde</a>
        </div>
        <div>
            <h3>Restaurer</h3>
            <p class="aide">
                Remplace <strong>toutes</strong> les données actuelles par celles de l'archive.
                À utiliser sur une installation neuve ou après une fausse manipulation.
            </p>
            <form wire:submit="restaurer" class="formulaire">
                <label>Archive de sauvegarde (.zip)
                    <input type="file" accept=".zip" wire:model="archive">
                    @error('archive') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
                <div class="actions">
                    <button type="submit" class="bouton danger" wire:confirm="Remplacer toutes les données actuelles par cette sauvegarde ?" wire:loading.attr="disabled">Restaurer</button>
                    <span wire:loading wire:target="archive">Téléversement…</span>
                    <span wire:loading wire:target="restaurer">Restauration…</span>
                </div>
            </form>
        </div>
    </div>
</section>
