# Sauvegarde et restauration MVP

Cette procedure couvre la sauvegarde minimale du MVP :

- base PostgreSQL ;
- fichiers uploades ;
- `.env.example` ;
- `docker-compose.yml` ;
- documentation de restauration.

L'index Meilisearch n'est pas prioritaire au MVP. Il n'est pas sauvegarde ici, car il doit pouvoir etre reconstruit depuis la base et les fichiers.

## Pre-requis

- Docker Compose doit fonctionner sur la machine.
- Les services doivent etre demarres avec :

```powershell
docker compose up -d
```

- Les scripts sont prevus pour PowerShell.

## Sauvegarde complete manuelle

Depuis la racine du projet :

```powershell
.\scripts\backup\backup-all.ps1
```

La sauvegarde est creee dans :

```text
backups/backup-YYYYMMDD-HHMMSS/
```

Elle contient notamment :

- un dump SQL PostgreSQL ;
- une archive ZIP des fichiers uploades ;
- `.env.example` ;
- `docker-compose.yml` ;
- `MANIFEST.txt`.

## Sauvegarder uniquement PostgreSQL

```powershell
.\scripts\backup\backup-postgres.ps1
```

Le script utilise par defaut :

- service Docker Compose : `postgres` ;
- base : `second_cervel` ;
- utilisateur : `second_cervel`.

Ces valeurs peuvent etre surchargees :

```powershell
.\scripts\backup\backup-postgres.ps1 -Database "second_cervel" -Username "second_cervel"
```

Le dump est cree dans le conteneur PostgreSQL puis copie vers le dossier `backups`, afin d'eviter les problemes d'encodage lies aux flux texte PowerShell.

## Sauvegarder uniquement les uploads

```powershell
.\scripts\backup\backup-uploads.ps1
```

Les fichiers sont copies depuis le conteneur `app`, dans :

```text
/var/www/html/storage/app/uploads
```

Ce dossier est hors racine web publique. Les fichiers ne doivent pas etre servis directement par le serveur web.

## Restaurer la base sur une instance de test

1. Copier le projet sur la machine de test.
2. Creer un `.env` adapte a partir de `.env.example`.
3. Demarrer les conteneurs :

```powershell
docker compose up -d
```

4. Restaurer le dump SQL :

```powershell
.\scripts\backup\restore-postgres.ps1 -DumpFile ".\backups\backup-YYYYMMDD-HHMMSS\postgres-YYYYMMDD-HHMMSS.sql"
```

Ce script est destine a une instance de test ou a une restauration explicitement choisie. Il peut remplacer les donnees existantes selon le contenu du dump.

## Restaurer les fichiers uploades

```powershell
.\scripts\backup\restore-uploads.ps1 -ArchiveFile ".\backups\backup-YYYYMMDD-HHMMSS\uploads-YYYYMMDD-HHMMSS.zip"
```

Les fichiers sont recopies dans le conteneur `app`, vers :

```text
/var/www/html/storage/app/uploads
```

Apres restauration, les chemins stockes en base doivent correspondre aux fichiers presents dans ce dossier.

## Verification apres restauration

Verifier :

1. L'application demarre.
2. La connexion fonctionne.
3. Les projets, notes, decisions, actions et fichiers sont visibles.
4. Un fichier restaure peut etre telecharge depuis sa fiche.
5. La recherche SQL retrouve les contenus restaurees.

## Secrets et securite

- Ne jamais commiter un vrai `.env`.
- Ne pas stocker de secrets dans les archives partagees sans chiffrement.
- Pour une sauvegarde hors machine, chiffrer l'archive avec un outil dedie.
- Evolution recommandee plus tard : BorgBackup, Restic ou archive chiffree avec rotation.

## Meilisearch

Meilisearch est volontairement exclu de cette sauvegarde MVP.

Raison :

- l'index est une donnee reconstruisible ;
- la source de verite reste PostgreSQL ;
- les fichiers originaux restent dans `storage/app/uploads`.

Quand l'indexation sera developpee, il faudra ajouter une commande de reconstruction de l'index depuis la base et les fichiers.
