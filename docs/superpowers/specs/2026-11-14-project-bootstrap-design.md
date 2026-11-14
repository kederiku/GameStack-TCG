# GamestackrTCG — initialisation

L’utilisateur demande un projet Laravel dans sa dernière version stable, Filament,
une inscription et une connexion avec Inertia, React et shadcn/ui, PostgreSQL,
Docker Compose et des commandes Make. Il a choisi « Docker Compose dédié + Make :
lance directement » le 14 novembre 2026 et dispensé la validation préalable.

## Approche

Utiliser le starter kit React officiel actuel, installer Laravel 13 et Filament 5,
et conserver les écrans et tests d’authentification du starter kit. Un Compose
dédié a été choisi plutôt que Sail pour exposer directement les services via Make.
Le code est créé dans le dossier vide fourni, sans worktree supplémentaire.

## Choix de réalisation

- Environnement de développement : PHP 8.4, Node 24, PostgreSQL 18.
- Services : application PHP, Vite, worker de queue, PostgreSQL et Mailpit.
- Application sur localhost:8000 ; Vite sur 5173 ; Mailpit sur 8025.
- Base persistante ; tests dans une base PostgreSQL séparée.
- Authentification du starter kit, inscription publique, mots de passe oubliés.
- Filament sur /admin ; un attribut is_admin privé contrôle l’accès.
- Commande interactive pour créer un administrateur, sans mot de passe prédéfini.
- Make initialise, démarre, arrête, teste et exécute les outils dans Docker.
- README en français avec démarrage et maintenance.

## Limites

Socle de développement uniquement, sans fonctionnalité métier TCG, déploiement
de production, domaine, TLS ou fournisseur d’e-mail externe.

## Validation

Installation réelle via Make, compilation frontend, contrôles PHP et TypeScript,
tests d’authentification et d’accès Filament sur PostgreSQL, vérification HTTP
des écrans et persistance après arrêt/redémarrage.
