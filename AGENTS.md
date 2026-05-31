# Règles d'exploitation du projet

Pour chaque fonctionnalité ou correction future :

- mettre à jour les scripts d'administration si le changement affecte le déploiement, les dépendances, les migrations, les workers, les logs, les permissions, Meilisearch, PostgreSQL, Tika ou les uploads ;
- fournir les commandes exactes à exécuter ;
- fournir les tests à réaliser ;
- fournir les URLs à vérifier ;
- fournir les commandes de logs utiles au diagnostic ;
- ne jamais régénérer ou remplacer une `APP_KEY` existante sans demande explicite ;
- ne jamais supprimer les volumes Docker, la base PostgreSQL ou les uploads dans un script de maintenance standard.
