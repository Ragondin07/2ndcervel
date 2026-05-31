# Memoire Projet Privee

Base Laravel de l'application web privee de memoire projet.

Le MVP couvre l'authentification, les projets, notes, decisions, actions, fichiers, recherche globale, jobs d'indexation et extraction texte via Tika.

## Prerequis

- Docker
- Docker Compose
- Acces reseau au premier lancement pour telecharger les images Docker et les dependances Composer

PHP et Composer ne sont pas requis sur la machine hote : ils sont fournis par le conteneur `app`.

## Services

- `app` : application Laravel exposee sur le port `8000`
- `postgres` : base PostgreSQL exposee sur le port `5432`
- `meilisearch` : moteur de recherche prevu pour la suite, expose sur le port `7700`
- `tika` : extraction de texte des fichiers bureautiques et PDF, expose sur le port `9998`

Volumes Docker :

- `postgres_data` : donnees PostgreSQL
- `uploaded_files` : fichiers uploades dans `storage/app/uploads`
- `meilisearch_data` : donnees Meilisearch
- `app_vendor` : dependances Composer installees dans le conteneur


## Exploitation automatisée

Les opérations courantes sont regroupées dans des scripts Bash à la racine :

```bash
./admin.sh            # console unique
./update.sh           # mise à jour sûre sans suppression de données
./healthcheck.sh      # diagnostic système
./logs.sh             # consultation guidée des logs
./fix-permissions.sh  # réparation des permissions Laravel
./dev-reset.sh        # reset complet de développement uniquement
```

La procédure détaillée de déploiement, rollback, restauration et diagnostic est documentée dans `docs/operations.md`.

## Installation

Depuis la racine du projet :

```bash
docker compose build
```

Puis lancer les services :

```bash
docker compose up -d
```

## Installation sur une VM Ubuntu 22.04

La procedure complete pour deployer le site sur une VM Ubuntu Server 22.04 LTS vierge est documentee ici :

```text
docs/installation-ubuntu-22.04.md
```

Resume des commandes principales :

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y curl git unzip ca-certificates gnupg lsb-release nano ufw
git clone URL_DU_DEPOT_GIT /opt/memoire-projet
cd /opt/memoire-projet
cp .env.example .env
nano .env
docker compose pull
docker compose build
docker compose up -d
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
# Donnees de demonstration optionnelles : SEED_DEMO_DATA=true docker compose exec app php artisan db:seed
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose up -d worker
```

Pour une VM privee, privilegier un acces LAN/VPN et ne pas exposer PostgreSQL, Meilisearch, Tika ou de futurs services OCR publiquement.

Au premier demarrage, le conteneur `app` :

1. copie `.env.example` vers `.env` si necessaire ;
2. installe les dependances Composer si `vendor` est absent ;
3. genere `APP_KEY` une seule fois uniquement si elle est vide ;
4. refuse de demarrer si `APP_KEY` est corrompue, au lieu de reecrire `.env` ;
5. lance Laravel sur `0.0.0.0:8000`.

## Migration

Lancer les migrations :

```bash
docker compose exec app php artisan migrate
```

Creer l'utilisateur administrateur initial :

```bash
docker compose exec app php artisan db:seed
# Donnees de demonstration optionnelles : SEED_DEMO_DATA=true docker compose exec app php artisan db:seed
```

En developpement uniquement, pour repartir d'une base propre avec les donnees de test MVP, sauvegardez d abord PostgreSQL et les uploads puis activez explicitement `SEED_DEMO_DATA=true`. Ne lancez jamais `migrate:fresh` sur une base a conserver :

```bash
SEED_DEMO_DATA=true docker compose exec app php artisan migrate:fresh --seed
```

Synchroniser les reglages Meilisearch puis reconstruire les index de recherche :

```bash
docker compose exec app php artisan scout:sync-index-settings
docker compose exec app php artisan search:reindex
```

Les identifiants de developpement sont configurables dans `.env` :

```text
ADMIN_NAME="Administrateur"
ADMIN_EMAIL=admin@example.test
ADMIN_PASSWORD=ChangeMeNow!
```

Changez `ADMIN_PASSWORD` avant tout usage reel.

## Acces local

Application Laravel :

```text
http://localhost:8000
```

Un visiteur non connecte est redirige vers :

```text
http://localhost:8000/login
```

Apres connexion, l'utilisateur arrive sur :

```text
http://localhost:8000/dashboard
```

Meilisearch :

```text
http://localhost:7700
```

La cle locale Meilisearch de developpement est definie dans `.env.example` et `docker-compose.yml` :

```text
local_master_key
```

La recherche utilise Meilisearch via Laravel Scout quand le service est disponible. Si Meilisearch est indisponible, la page Recherche bascule sur une recherche SQL simple de secours.

## Commandes utiles

Voir les logs :

```bash
docker compose logs -f app
```

Arreter l'environnement :

```bash
docker compose down
```

Supprimer aussi les volumes de developpement :

```bash
docker compose down -v
```

## Tests automatises

Les tests de base du MVP utilisent PHPUnit et une base SQLite en memoire.

Depuis l'environnement Docker :

```bash
docker compose exec app php artisan test
```

Ou directement avec PHPUnit :

```bash
docker compose exec app ./vendor/bin/phpunit
```

## Worker de jobs

Les jobs Laravel utilisent la queue `database` au MVP. Les tables de queue sont creees par les migrations.

Le service Docker `worker` traite les jobs en arriere-plan :

```bash
docker compose up -d worker
```

Commande manuelle equivalente :

```bash
docker compose exec app php artisan queue:work database --queue=indexing,default --sleep=2 --tries=3
```

L'upload de fichier cree un job `IndexFileJob`. Ce job appelle Tika pour les formats supportes et met a jour les statuts sans lancer OCR ou IA.

## Extraction de texte

Apache Tika est utilise par le worker pour extraire le texte des fichiers supportes :

- PDF texte
- DOCX
- XLSX
- ODS
- CSV
- TXT / Markdown
- PPTX

Le fichier original reste inchange dans `storage/app/uploads`. Le texte extrait est enregistre dans `files.extracted_text`, puis le fichier est reindexe dans Meilisearch via Scout.

Relancer l'indexation d'un fichier depuis l'interface Fichiers, ou via le worker apres upload :

```bash
docker compose up -d tika worker
```

La page d'administration de l'indexation est reservee aux administrateurs :

```text
http://localhost:8000/admin
```

Elle permet de surveiller les statuts, voir les dernieres erreurs, relancer un fichier, relancer les fichiers d'un projet, relancer tous les fichiers et purger/reconstruire l'index Meilisearch.

OCR et IA restent exclus de cette etape.

## Sauvegarde MVP

La procedure de sauvegarde et restauration minimale est documentee ici :

```text
docs/backup.md
```

Sauvegarde complete manuelle :

```bash
powershell -ExecutionPolicy Bypass -File scripts/backup/backup-all.ps1
```

## Notes de cadrage

- Les fichiers uploades doivent rester hors base de donnees.
- Meilisearch est branche a Laravel Scout pour la recherche globale.
- L'index Meilisearch n'est pas sauvegarde au MVP : il est reconstructible avec `php artisan search:reindex`.
- Un worker Laravel est present pour preparer l'indexation asynchrone des fichiers.
- Tika est integre pour l'extraction texte. Tesseract, OCR et IA locale ne sont pas integres dans ce socle.
