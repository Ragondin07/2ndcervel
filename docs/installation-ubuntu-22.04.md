# Installation sur Ubuntu Server 22.04 LTS

Cette procedure installe l'application de memoire projet privee sur une VM Ubuntu Server 22.04 LTS vierge, avec Docker Compose. Elle ne suppose pas que PHP, Composer, Laravel, PostgreSQL ou Meilisearch sont installes directement sur l'hote.

Le site est prevu pour un usage prive. L'acces recommande est LAN ou VPN. N'exposez pas PostgreSQL, Meilisearch, Tika ou de futurs services OCR directement sur Internet.

## A. Prerequis VM

Version attendue :

- Ubuntu Server 22.04 LTS 64 bits.

Ressources recommandees pour un MVP :

- CPU : 2 vCPU minimum.
- RAM : 4 Go minimum, 8 Go recommandes si beaucoup de fichiers PDF/bureautiques.
- Disque : 40 Go minimum, a adapter au volume de fichiers uploades.
- Reseau : IP fixe recommandee.
- Acces : SSH active, utilisateur avec droits `sudo`.
- Acces applicatif : LAN ou VPN recommande.

Ports utilises par le `docker-compose.yml` actuel :

- `22/tcp` : SSH.
- `8000/tcp` : application Laravel.
- `5432/tcp` : PostgreSQL, a ne pas exposer publiquement.
- `7700/tcp` : Meilisearch, a ne pas exposer publiquement.
- `9998/tcp` : Apache Tika, a ne pas exposer publiquement.

Pour une exposition Internet plus tard, utilisez un reverse proxy HTTPS comme Nginx ou Traefik et gardez les services internes non publics.

## B. Mise a jour systeme

Connectez-vous en SSH :

```bash
ssh utilisateur@IP_DE_LA_VM
```

Mettez le systeme a jour :

```bash
sudo apt update
sudo apt upgrade -y
```

Installez les paquets de base :

```bash
sudo apt install -y curl git unzip ca-certificates gnupg lsb-release nano ufw
```

## C. Installation Docker

Installez Docker depuis le depot officiel Docker.

Ajoutez la cle GPG :

```bash
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
```

Ajoutez le depot Docker :

```bash
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

Installez Docker Engine et le plugin Docker Compose :

```bash
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Verifiez l'installation :

```bash
docker --version
docker compose version
sudo docker run --rm hello-world
```

Ajoutez votre utilisateur au groupe `docker` :

```bash
sudo usermod -aG docker "$USER"
newgrp docker
```

Verifiez que Docker fonctionne sans `sudo` :

```bash
docker ps
```

Si la commande echoue encore, deconnectez-vous puis reconnectez-vous en SSH.

## D. Preparation du serveur

Creez le dossier applicatif :

```bash
sudo mkdir -p /opt/memoire-projet
sudo chown "$USER":"$USER" /opt/memoire-projet
```

Clonez le depot Git dans ce dossier :

```bash
git clone URL_DU_DEPOT_GIT /opt/memoire-projet
cd /opt/memoire-projet
```

Copiez le fichier d'environnement :

```bash
cp .env.example .env
```

Editez `.env` :

```bash
nano .env
```

## E. Configuration .env

Variables importantes a verifier ou modifier :

```env
APP_NAME="Memoire Projet Privee"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://IP_DE_LA_VM:8000
APP_PORT=8000

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=second_cervel
DB_USERNAME=second_cervel
DB_PASSWORD=mot_de_passe_postgres_fictif_a_changer
POSTGRES_HOST_PORT=5432

QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
UPLOADS_DISK=uploads
MAX_UPLOAD_SIZE_MB=50

MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=cle_meilisearch_fictive_a_changer
MEILISEARCH_HOST_PORT=7700
MEILI_ENV=production
SCOUT_DRIVER=meilisearch

TIKA_URL=http://tika:9998
TIKA_TIMEOUT=60
TIKA_HOST_PORT=9998

ADMIN_NAME="Administrateur"
ADMIN_EMAIL=admin@example.test
ADMIN_PASSWORD=mot_de_passe_admin_fictif_a_changer
```

Points importants :

- `APP_ENV=production` pour une VM durable.
- `APP_DEBUG=false` pour ne pas afficher les erreurs techniques aux utilisateurs.
- `APP_URL` doit correspondre a l'URL d'acces depuis le LAN ou le VPN.
- `APP_PORT` est le port expose par Docker sur la VM.
- `DB_HOST` doit rester `postgres`, car PostgreSQL tourne dans Docker Compose.
- `MEILISEARCH_HOST` doit rester `http://meilisearch:7700`, car Laravel contacte le service Docker interne.
- `MEILISEARCH_KEY` doit correspondre a la cle Meilisearch du `docker-compose.yml`.
- `POSTGRES_HOST_PORT`, `MEILISEARCH_HOST_PORT` et `TIKA_HOST_PORT` sont lies a `127.0.0.1` dans Docker Compose pour eviter une exposition publique directe.
- `ADMIN_PASSWORD` doit etre remplace avant le seed.
- Ne committez jamais le fichier `.env`.

Le stockage des uploads est gere par le disque Laravel `uploads`, qui pointe vers :

```text
storage/app/uploads
```

Dans Docker, ce dossier est persiste dans le volume `uploaded_files`.

## F. Lancement Docker Compose

Construisez et lancez les conteneurs :

```bash
docker compose pull
docker compose build
docker compose up -d
```

Verifiez l'etat :

```bash
docker compose ps
```

Consultez les logs :

```bash
docker compose logs -f app
```

Logs du worker :

```bash
docker compose logs -f worker
```

## G. Initialisation Laravel

Le conteneur `app` installe Composer si `vendor` est absent et genere `APP_KEY` si elle est vide. Vous pouvez verifier manuellement :

```bash
docker compose exec app composer install --no-interaction --prefer-dist
docker compose exec app php artisan key:generate --force
```

Lancez les migrations :

```bash
docker compose exec app php artisan migrate
```

Creez l'utilisateur administrateur initial :

```bash
docker compose exec app php artisan db:seed
```

Le lien public `storage` n'est pas necessaire pour les fichiers uploades prives, car ils sont telecharges via un controleur authentifie. Si Laravel en a besoin plus tard pour des fichiers publics :

```bash
docker compose exec app php artisan storage:link
```

Optimisez les caches Laravel :

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## H. Droits fichiers

Les dossiers suivants doivent etre accessibles en ecriture dans le conteneur :

- `storage`
- `bootstrap/cache`
- `storage/app/uploads`

Le script d'entree Docker cree ces dossiers et applique des droits de base. Si une erreur de permission apparait :

```bash
docker compose exec app sh -lc "mkdir -p storage/app/uploads storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache"
docker compose exec app sh -lc "chmod -R ug+rw storage bootstrap/cache"
```

Comme les fichiers sont dans un volume Docker, evitez de modifier directement les droits depuis l'hote sauf si vous savez quel utilisateur execute le conteneur.

## I. Workers

Les jobs Laravel utilisent la queue `database`.

Le `docker-compose.yml` contient un service `worker`. Lancez-le avec :

```bash
docker compose up -d worker
```

Commande equivalente manuelle :

```bash
docker compose exec app php artisan queue:work database --queue=indexing,default --sleep=2 --tries=3
```

Verifiez que le worker tourne :

```bash
docker compose ps worker
docker compose logs -f worker
```

Un upload de fichier cree un job `IndexFileJob`. Le worker traite ce job sans bloquer l'interface.

## J. Meilisearch

Meilisearch est inclus dans Docker Compose.

Verifiez depuis la VM :

```bash
curl http://localhost:7700/health
```

Si une cle est configuree, utilisez :

```bash
curl -H "Authorization: Bearer cle_meilisearch_fictive_a_changer" http://localhost:7700/health
```

La cle Meilisearch doit etre coherente entre :

- `MEILISEARCH_KEY` dans `.env`
- `MEILI_MASTER_KEY` dans `docker-compose.yml`

Synchronisez les reglages Scout si necessaire :

```bash
docker compose exec app php artisan scout:sync-index-settings
```

Reconstruisez les index :

```bash
docker compose exec app php artisan search:reindex
```

L'index Meilisearch est reconstructible. Il n'est pas la source de verite.

## K. Tika et Tesseract

Apache Tika est inclus dans le `docker-compose.yml` actuel avec le service `tika`.

Il sert a extraire du texte de fichiers PDF texte et bureautiques. Verifiez sa disponibilite :

```bash
curl http://localhost:9998/tika
```

Une reponse HTTP de Tika confirme que le service repond. Meme si Tika est indisponible, le site doit continuer a fonctionner ; seules les extractions de texte echoueront ou resteront en erreur.

Tesseract est prevu pour l'OCR mais n'est pas integre dans cette etape. Il ne doit donc pas bloquer l'installation ni le fonctionnement du site.

## L. Pare-feu UFW

Activez un pare-feu minimal.

Autorisez SSH avant d'activer UFW :

```bash
sudo ufw allow OpenSSH
```

Pour un acces LAN/VPN direct a Laravel sur le port `8000`, autorisez seulement le reseau prive. Exemple pour un LAN `192.168.1.0/24` :

```bash
sudo ufw allow from 192.168.1.0/24 to any port 8000 proto tcp
```

Si vous utilisez plus tard un reverse proxy HTTP/HTTPS :

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

Ne publiez pas les services internes. Bloquez explicitement les ports sensibles depuis l'exterieur si necessaire :

```bash
sudo ufw deny 5432/tcp
sudo ufw deny 7700/tcp
sudo ufw deny 9998/tcp
```

Activez UFW :

```bash
sudo ufw enable
sudo ufw status verbose
```

## M. Acces au site

Depuis la VM :

```text
http://localhost:8000
```

Depuis le LAN ou le VPN :

```text
http://IP_DE_LA_VM:8000
```

Connexion admin :

- email : valeur `ADMIN_EMAIL` du `.env`
- mot de passe : valeur `ADMIN_PASSWORD` du `.env` au moment du `db:seed`

Changez obligatoirement le mot de passe admin avant un usage reel. N'utilisez jamais les exemples fictifs en production.

## N. Mise en production privee

Reglages minimum :

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://memoire-projet.example.local
```

Recommandations :

- Acces via VPN ou LAN prive.
- HTTPS recommande, meme en reseau prive.
- Reverse proxy Nginx ou Traefik possible plus tard.
- Ne pas exposer directement l'application sur Internet sans controle d'acces supplementaire.
- Ne pas exposer PostgreSQL, Meilisearch, Tika ou de futurs services OCR.
- Utiliser des secrets forts pour PostgreSQL, Meilisearch et l'admin.

Apres modification de `.env`, rechargez la configuration Laravel :

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
docker compose restart app worker
```

## O. Sauvegarde minimale

A sauvegarder :

- base PostgreSQL ;
- fichiers uploades ;
- `.env` ;
- `docker-compose.yml` ;
- dossier `scripts` ;
- dossier `docs`.

Volumes Docker importants :

- `postgres_data` : donnees PostgreSQL ;
- `uploaded_files` : fichiers uploades ;
- `meilisearch_data` : index reconstructible ;
- `app_vendor` : dependances Composer, reconstructibles.

Voir les volumes :

```bash
docker volume ls
```

Les noms reels des volumes dependent du nom du dossier ou du projet Compose. Reperez les volumes termines par `_postgres_data` et `_uploaded_files`, puis inspectez-les si necessaire :

```bash
docker volume inspect NOM_DU_VOLUME_postgres_data
docker volume inspect NOM_DU_VOLUME_uploaded_files
```

Les scripts existants sont en PowerShell dans `scripts/backup`. Sur une VM Ubuntu, installez PowerShell si vous voulez les utiliser tels quels, ou utilisez les commandes Docker equivalentes.

Sauvegarde PostgreSQL manuelle :

```bash
mkdir -p backups/manual
docker compose exec -T postgres pg_dump -U second_cervel -d second_cervel > backups/manual/postgres.sql
```

Sauvegarde des uploads :

```bash
docker compose cp app:/var/www/html/storage/app/uploads backups/manual/uploads
```

Copiez aussi :

```bash
cp .env backups/manual/.env
cp .env.example backups/manual/.env.example
cp docker-compose.yml backups/manual/docker-compose.yml
cp -r scripts docs backups/manual/
```

L'index Meilisearch n'a pas besoin d'etre sauvegarde en priorite : reconstruisez-le avec `php artisan search:reindex`.

## P. Restauration minimale

Sur une autre VM Ubuntu 22.04 :

1. Installez Docker et Docker Compose.
2. Clonez le depot dans `/opt/memoire-projet`.
3. Restaurez `.env`.
4. Lancez les conteneurs.

```bash
cd /opt/memoire-projet
docker compose build
docker compose up -d
```

Restaurez la base PostgreSQL :

```bash
cat backups/manual/postgres.sql | docker compose exec -T postgres psql -U second_cervel -d second_cervel
```

Restaurez les uploads :

```bash
docker compose cp backups/manual/uploads app:/var/www/html/storage/app/
```

Relancez les migrations eventuelles :

```bash
docker compose exec app php artisan migrate
```

Nettoyez et reconstruisez les caches :

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Reconstruisez Meilisearch :

```bash
docker compose exec app php artisan search:reindex
```

Verifiez ensuite :

- connexion admin ;
- projets, notes, decisions, actions visibles ;
- fichiers visibles ;
- telechargement d'un fichier restaure ;
- recherche globale.

## Q. Maintenance courante

Mettre a jour le code :

```bash
cd /opt/memoire-projet
git pull
```

Reconstruire les conteneurs :

```bash
docker compose build
docker compose up -d
```

Relancer les migrations :

```bash
docker compose exec app php artisan migrate
```

Vider puis recreer les caches Laravel :

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Consulter les logs :

```bash
docker compose logs -f app
docker compose logs -f worker
docker compose logs -f postgres
docker compose logs -f meilisearch
docker compose logs -f tika
```

Redemarrer les services :

```bash
docker compose restart app worker
```

Arreter l'application :

```bash
docker compose down
```

Ne supprimez les volumes avec `docker compose down -v` que si vous acceptez de perdre les donnees locales.

## R. Depannage

### Docker non accessible sans sudo

Symptome :

```text
permission denied while trying to connect to the Docker daemon socket
```

Correction :

```bash
sudo usermod -aG docker "$USER"
newgrp docker
docker ps
```

Sinon, reconnectez-vous en SSH.

### Port deja utilise

Symptome :

```text
Bind for 0.0.0.0:8000 failed: port is already allocated
```

Diagnostic :

```bash
sudo ss -ltnp | grep ':8000'
```

Correction : changez le port hote dans `docker-compose.yml`, par exemple :

```yaml
ports:
  - "8080:8000"
```

Puis :

```bash
docker compose up -d
```

### Erreur de connexion PostgreSQL

Verifiez que PostgreSQL tourne :

```bash
docker compose ps postgres
docker compose logs postgres
```

Verifiez les variables :

```bash
docker compose exec app php artisan env
docker compose exec app php artisan migrate:status
```

`DB_HOST` doit etre `postgres`, pas `localhost`.

### APP_KEY manquante

Symptome : erreur Laravel sur la cle d'application.

Correction :

```bash
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan config:clear
docker compose restart app
```

### Permissions storage incorrectes

Symptomes : erreurs d'ecriture dans `storage`, sessions impossibles, logs impossibles.

Correction :

```bash
docker compose exec app sh -lc "chmod -R ug+rw storage bootstrap/cache"
docker compose restart app worker
```

### Page blanche Laravel

Consultez les logs :

```bash
docker compose logs -f app
docker compose exec app tail -n 100 storage/logs/laravel.log
```

En production, gardez `APP_DEBUG=false`. Pour diagnostiquer temporairement sur une VM de test seulement, passez `APP_DEBUG=true`, puis remettez `false`.

### Erreur 500

Actions utiles :

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan migrate:status
docker compose logs -f app
```

### Meilisearch indisponible

Verifiez :

```bash
docker compose ps meilisearch
docker compose logs meilisearch
curl http://localhost:7700/health
```

La recherche SQL de secours doit eviter un plantage brutal de l'application, mais la recherche Meilisearch ne sera pas disponible tant que le service est en erreur.

### Worker arrete

Verifiez :

```bash
docker compose ps worker
docker compose logs worker
```

Relancez :

```bash
docker compose up -d worker
```

### Fichiers uploades non accessibles

Verifiez que le volume est monte et que le fichier existe dans le conteneur :

```bash
docker compose exec app ls -la storage/app/uploads
```

Les fichiers ne doivent pas etre servis directement depuis le webroot. Le telechargement doit passer par le controleur authentifie.

### Migrations echouees

Verifiez la base :

```bash
docker compose ps postgres
docker compose logs postgres
docker compose exec app php artisan migrate:status
```

Relancez apres correction :

```bash
docker compose exec app php artisan migrate
```

N'utilisez `php artisan migrate:fresh` que sur une instance de test, car cette commande supprime les donnees.
