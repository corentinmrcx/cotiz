# CoTiz

Cartes d'adhérent dématérialisées du Foyer de Soudron : import du classeur de
la trésorière, génération des cartes en PDF et PNG, envoi des mails.

Le cadrage complet est dans [`claude.md`](claude.md).

## Démarrage

Prérequis : Docker et Docker Compose.

```bash
git clone git@github.com:corentinmarcoux/cotiz.git && cd cotiz
cp .env.example .env
docker compose up -d
```

Puis ouvrir <http://localhost:8000>.

Les fois suivantes : `docker compose up -d`.

## Données

Tout l'état de l'application vit dans `./data` (base SQLite, visuels, cartes
générées). Ce dossier n'est pas versionné et ne doit jamais être placé dans un
dossier synchronisé (Drive, Dropbox…).
