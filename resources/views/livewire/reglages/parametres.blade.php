<section class="carte">
    <h2>Expéditeur et envoi</h2>

    @if ($message)
        <p class="message succes">{{ $message }}</p>
    @endif
    @if ($erreur)
        <p class="message erreur">{{ $erreur }}</p>
    @endif

    <form wire:submit="enregistrer" class="formulaire">
        <fieldset>
            <legend>Expéditeur</legend>
            <div class="grille-2">
                <label>Nom
                    <input type="text" wire:model="form.expediteur_nom">
                    @error('form.expediteur_nom') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
                <label>Adresse
                    <input type="email" wire:model="form.expediteur_email">
                    @error('form.expediteur_email') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
            </div>
            <label class="case">
                <input type="checkbox" wire:model="form.copie_cachee_active">
                Envoyer une copie cachée de chaque mail à l'adresse expéditrice
            </label>
        </fieldset>

        <fieldset>
            <legend>Serveur SMTP</legend>
            <div class="grille-3">
                <label>Serveur
                    <input type="text" wire:model="form.smtp_host">
                    @error('form.smtp_host') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
                <label>Port
                    <input type="number" wire:model="form.smtp_port">
                    @error('form.smtp_port') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
                <label>Chiffrement
                    <select wire:model="form.smtp_encryption">
                        <option value="tls">STARTTLS (port 587)</option>
                        <option value="ssl">SSL (port 465)</option>
                        <option value="aucun">Aucun</option>
                    </select>
                </label>
            </div>
            <div class="grille-2">
                <label>Identifiant
                    <input type="text" wire:model="form.smtp_username" autocomplete="off">
                </label>
                <label>Mot de passe d'application
                    <input type="password" wire:model="form.smtp_password" autocomplete="new-password">
                </label>
            </div>
            <div class="actions">
                <button type="button" class="bouton secondaire" wire:click="testerConnexionSmtp" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="testerConnexionSmtp">Enregistrer et tester la connexion</span>
                    <span wire:loading wire:target="testerConnexionSmtp">Connexion en cours…</span>
                </button>
            </div>
        </fieldset>

        <fieldset>
            <legend>Mail envoyé aux adhérents</legend>
            <label>Objet
                <input type="text" wire:model="form.mail_objet">
                @error('form.mail_objet') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
            <label for="corps-editeur">Corps</label>
            <div class="editeur">
                <div class="editeur-outils">
                    <button type="button" data-commande="bold" title="Gras"><b>G</b></button>
                    <button type="button" data-commande="italic" title="Italique"><i>I</i></button>
                    <button type="button" data-commande="underline" title="Souligné"><u>S</u></button>
                </div>
                <div id="corps-editeur" class="editeur-zone" contenteditable="true" wire:ignore>{!! \App\Services\NettoyeurHtmlMail::enHtml($form->mail_corps) !!}</div>
                <textarea id="corps-cache" wire:model="form.mail_corps" hidden></textarea>
            </div>
            @error('form.mail_corps') <span class="champ-erreur">{{ $message }}</span> @enderror
            <script>
                (() => {
                    const zone = document.getElementById('corps-editeur');
                    const cache = document.getElementById('corps-cache');
                    const synchroniser = () => {
                        cache.value = zone.innerHTML;
                        cache.dispatchEvent(new Event('input', { bubbles: true }));
                    };
                    zone.addEventListener('input', synchroniser);
                    document.querySelectorAll('.editeur-outils button').forEach((bouton) => {
                        bouton.addEventListener('click', () => {
                            zone.focus();
                            document.execCommand(bouton.dataset.commande);
                            synchroniser();
                        });
                    });
                    synchroniser();
                })();
            </script>
            <p class="aide">
                Variables disponibles :
                @foreach (['nom', 'prenom', 'saison', 'cotisation', 'nb_adultes', 'nb_enfants', 'tarif_adulte', 'tarif_enfant_famille', 'tarif_enfant_seul', 'asso_nom', 'asso_email'] as $variable)
                    <code>{{ '{'.'{'.$variable.'}'.'}' }}</code>
                @endforeach
            </p>
            <label>Délai entre deux envois (secondes)
                <input type="number" min="0" max="60" wire:model="form.delai_entre_envois">
                @error('form.delai_entre_envois') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
        </fieldset>

        <fieldset>
            <legend>Association (affichée sur la carte et dans le mail)</legend>
            <div class="grille-2">
                <label>Nom
                    <input type="text" wire:model="form.asso_nom">
                    @error('form.asso_nom') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
                <label>Adresse mail affichée
                    <input type="email" wire:model="form.asso_email_affiche">
                    @error('form.asso_email_affiche') <span class="champ-erreur">{{ $message }}</span> @enderror
                </label>
            </div>
            <label>Adresse postale
                <input type="text" wire:model="form.asso_adresse">
                @error('form.asso_adresse') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
        </fieldset>

        <div class="actions">
            <button type="submit" class="bouton">Enregistrer les réglages</button>
            @if ($this->message)
                <span class="confirmation">{{ $this->message }}</span>
            @endif
        </div>
    </form>
</section>
