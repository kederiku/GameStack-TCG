# Journal de réalisation

- Choix confirmé : Docker Compose dédié et Make ; exécution directe autorisée.
- Dossier initial vide, aucun dépôt existant à isoler ou modifier.
- Starter officiel : laravel/react-starter-kit, commit
  `012f4248f70e255e32ad93f540aca5fb7e8c96fe`.
- Dernière version Laravel vérifiée via Packagist : 13.42.0.
- Les services PHP et Vite partagent une image PHP/Node : Wayfinder peut appeler
  Artisan sans dépendance PHP sur l’hôte.
- Choix complémentaire : l’entrée /admin/login redirige vers /login (React/Fortify)
  afin de ne pas contourner les fonctions d’authentification du starter kit.
- Préparation : tests exécutés avec DB_DATABASE=gamestackr_testing forcé dans
  phpunit.xml, distinct de la base de développement gamestackr.

## Versions installées

- Laravel 13.42.0 ; Filament 5.11.3.
- PHP 8.4.27 ; Composer 2.11.2.
- React 19.3.1 ; Inertia React 3.11.0 ; Inertia Laravel 3.7.0.
- Tailwind CSS 4.3.5 ; composants shadcn/ui du starter officiel.
- PostgreSQL 18.7 ; Node 24 (image officielle).

## Vérifications du 14 novembre 2026

- `make init` : image construite, dépendances installées, migrations appliquées,
  compilation frontend réussie et services démarrés.
- `make check` : **54 tests, 200 assertions**, PHPStan sans erreur, Pint réussi,
  analyse frontend sans avertissement et TypeScript réussi.
- `composer validate --strict` : manifeste et verrouillage valides.
- Écrans inscription, connexion et mot de passe oublié vérifiés dans le
  navigateur ; aucun avertissement ou erreur console.
- Tests ajoutés : restriction Filament en mode local, vérification e-mail,
  protection contre l’élévation via inscription et mass assignment, création
  interactive d’un admin, refus des doublons et mots de passe invalides,
  redirection vers la destination demandée, passage obligatoire par la 2FA.
- Installation indépendante sous `/tmp/gamestackr-clean-smoke` sans `.env`,
  `vendor` ni `node_modules` : `make init` réussi sur des ports et un volume dédiés.
- Deuxième `make init` sur cette copie : clé entre guillemets inchangée, donnée
  témoin conservée. Clé brute de 32 caractères également conservée.
- `make down` puis `make up` : donnée témoin conservée.
- Suite de tests de cette copie : donnée témoin toujours présente dans la base
  de développement, confirmant l’isolation de la base de tests.
- E-mail réellement envoyé par Laravel en SMTP et retrouvé dans Mailpit.
- Conteneurs et volume de la copie de test supprimés après validation.
- Contrôle final : application et Vite accessibles en HTTP, toutes les migrations
  appliquées ; application, Vite, PostgreSQL et Mailpit sains, worker actif.

## Revue indépendante et correctifs

- Deux anomalies relevées puis reproduites : UID/GID 1000 imposés sous Linux,
  et régénération d’une clé valide entre guillemets.
- UID/GID configurables dans l’image et automatiquement transmis par Make sous
  Linux. Vérification dans une image construite avec UID/GID 1001 : écriture
  autorisée dans un répertoire appartenant à 1001, auparavant refusée.
- La clé est désormais vérifiée via la configuration Laravel, pas une expression
  régulière sur `.env`. Le correctif est validé sur les formats généré, cité et brut.
- Les services applicatifs sont arrêtés avant la réinstallation des dépendances,
  puis relancés pour restaurer Vite et le rechargement à chaud.
- Aucun problème critique ni point mineur restant dans la revue.

Le périmètre reste un socle de développement local. Le déploiement de production,
TLS, les e-mails externes et les fonctionnalités métier TCG sont hors périmètre.
Les fichiers restent dans le dossier demandé ; aucun dépôt distant ni push.
