# Exploitation quotidienne

Ce projet est administrable depuis la racine avec `./admin.sh`. Les scripts sont conçus pour limiter les manipulations manuelles et ne suppriment pas les données hors commande de développement explicitement nommée.

## Commandes principales

```bash
./update.sh           # mise à jour sûre : sauvegarde .env, build si nécessaire, migrate --force, optimize:clear, workers
./healthcheck.sh      # diagnostic lisible des services, permissions, logs et espace disque
./logs.sh             # menu de consultation des logs
./fix-permissions.sh  # recrée et répare storage, bootstrap/cache et uploads
./dev-reset.sh        # développement uniquement : migrate:fresh --seed, conserve les uploads
./admin.sh            # console unique d'administration
```

## Déploiement standard

1. Mettre à jour le code.
2. Vérifier `.env` et les secrets.
3. Lancer :

```bash
./update.sh
./healthcheck.sh
```

4. Vérifier les URLs :

```text
http://localhost:8000/up
http://localhost:8000/login
http://localhost:8000/dashboard
```

## Rollback

1. Revenir au commit précédent avec Git.
2. Relancer les services sans supprimer les volumes :

```bash
git checkout <commit_precedent>
./update.sh
./healthcheck.sh
```

3. Si la base doit être restaurée, utiliser `./admin.sh` option `5. Restauration` avec une sauvegarde SQL créée par l'option `4. Sauvegarde`.

## Restauration

```bash
./admin.sh
# 5. Restauration
```

La restauration PostgreSQL demande une confirmation `RESTORE`. La restauration des uploads demande une confirmation `UPLOADS` et s'appuie sur les archives `backups/uploads/*.tar.gz` créées par le menu de sauvegarde.

## Diagnostic rapide

```bash
./healthcheck.sh
./logs.sh 7
./logs.sh 8
```

Vérifier aussi :

```bash
docker compose ps
docker compose logs --tail=200 app worker postgres meilisearch tika
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app php artisan queue:monitor indexing,default --max=100
```

## Développement

`./dev-reset.sh` refuse de s'exécuter si `APP_ENV=production`. Il recrée la base, exécute les seeders et reconstruit les index Meilisearch, tout en conservant les uploads.
