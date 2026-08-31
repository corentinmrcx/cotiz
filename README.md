# CoTiz

Cartes d'adhérent dématérialisées du Foyer de Soudron : import du classeur de
la trésorière, génération des cartes en PDF et PNG, envoi des mails avec suivi,
historique par saison et export ZIP.

Le cadrage complet (décisions, modèle de données, format d'import, lots) est
dans [`claude.md`](claude.md). Ce fichier en est le contrat.

## Démarrage

Prérequis : Docker et Docker Compose. Rien d'autre à installer.

```bash
git clone git@github.com:corentinmarcoux/cotiz.git && cd cotiz
cp .env.example .env
docker compose up -d
```

Puis ouvrir <http://localhost:8000>. Le premier démarrage construit l'image
(quelques minutes), génère la clé de chiffrement, crée la base et la saison
par défaut.

Les fois suivantes : `docker compose up -d` (ou `make up`).

Le `.env` contient le mot de passe d'application Gmail (`MAIL_PASSWORD`).
Il n'est jamais commité. Ces valeurs initialisent les Réglages au premier
démarrage ; ensuite, tout se modifie depuis l'écran Réglages.

## Utilisation

1. **Réglages** — vérifier l'expéditeur, tester la connexion SMTP, relire
   l'objet et le corps du mail, contrôler les tarifs, le logo et la couleur de
   la saison active. Ouvrir une nouvelle saison en début de campagne.
2. **Import** — télécharger le classeur modèle (avec les tarifs de la saison),
   le transmettre à la trésorière, puis déposer le classeur rempli. L'écran de
   contrôle sépare lignes valides, avertissements et rejets. Rien n'est écrit
   avant validation.
3. **Adhésions** — *Générer les cartes en test* produit les PDF et PNG sans
   envoi, pour vérification. *Envoyer les cartes* envoie un mail par adhésion,
   une requête à la fois, avec barre de progression. *Renvoyer* pour un cas
   unitaire. Saisie manuelle pour les corrections et les adhérents tardifs.
4. **Historique** — consultation des saisons passées.
5. **Réglages → Sauvegarde** — export complet de l'outil (à déposer sur le
   Drive de l'association) et restauration d'une sauvegarde.

La carte est entièrement dessinée en HTML/CSS (seul le logo est une image).
Aperçu sur données fictives : <http://localhost:8000/cartes/apercu>. Toute la
géométrie est dans `resources/cartes/gabarit.css` ; les exports Canva d'origine
sont gardés en référence dans `docs/reference-canva/`.

## Données, sauvegarde et restauration

Tout l'état de l'application vit dans `./data` : base SQLite, clé de
chiffrement (`app.key`), visuels, cartes générées. Ce dossier n'est pas
versionné et **ne doit jamais être placé dans un dossier synchronisé**
(Drive, kDrive, Dropbox) : SQLite et la synchronisation de fichiers font
mauvais ménage.

**La sauvegarde officielle se fait depuis l'application** : Réglages →
Sauvegarde → « Télécharger la sauvegarde » produit une archive complète
(données, réglages, logos, cartes) restaurable depuis le même écran sur
n'importe quelle installation, même avec une autre clé de chiffrement. Un
bandeau d'alerte s'affiche tant que des modifications n'ont pas été exportées.

En complément, deux commandes bas niveau copient `./data` tel quel :

```bash
make backup                              # produit data-AAAA-MM-JJ.zip à la racine
make restore ARCHIVE=data-2026-09-15.zip # remplace ./data (l'ancien est conservé en data.avant-restauration-*)
```

Reprendre le travail sur une autre machine : `git clone`, `cp .env.example .env`
(et renseigner le mot de passe SMTP), `docker compose up -d`, puis restaurer la
sauvegarde depuis l'écran Réglages.

## Authentification

Désactivée par défaut (`APP_AUTH_ENABLED=false`) : en local, rien n'est exposé
au réseau. Le jour d'une mise en ligne, passer à `true` : un compte est créé au
démarrage depuis `APP_AUTH_EMAIL` et `APP_AUTH_PASSWORD`.

## Développement

```bash
make test                      # suite de tests dans le conteneur
docker compose exec app ./vendor/bin/pint   # style de code
```

Pour travailler sur le code sans reconstruire l'image à chaque modification,
créer un `docker-compose.override.yml` (ignoré par Git) qui monte le dépôt :

```yaml
services:
  app:
    volumes:
      - .:/app:z
      - ./data:/app/data:z
    user: "1000:1000"
    environment:
      HOME: /tmp
```

puis installer les dépendances une fois : `docker compose exec app composer install`.

### Organisation du code

| Dossier | Contenu |
|---|---|
| `app/Services` | Règles métier, chacune à un seul endroit : `CalculateurCotisation`, `LecteurClasseur`, `ValidateurImport`, `EnregistreurAdhesion`, `GenerateurCarte`, `EnvoyeurCarte`, `ExportateurSaison`… |
| `app/Livewire` | Composants d'écran, qui orchestrent les services sans logique métier |
| `app/Dto` | Objets de transfert (`LigneAdhesion`, `VerdictLigne`, `DonneesCarte`) |
| `resources/views/cartes` | Gabarit de carte (recto/verso) |
| `resources/cartes/gabarit.css` | Géométrie complète de la carte |
| `database/seeders` | Saison, réglages et compte par défaut ; logo initial |

## Mode production (différé)

Non implémenté. Le jour venu : un `docker-compose.prod.yml` avec
`APP_AUTH_ENABLED=true`, `read_only` avec le volume de données monté sur
`/app/data` et un `tmpfs` sur `/tmp` et `/app/storage`, `pids_limit: 256`,
`mem_limit: 1g`, `shm_size: 512m`. Voir la section 10 du cadrage.
