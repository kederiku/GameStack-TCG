# Local Image Storage Implementation Plan

> Archive historique. La commande de copie vers S3 et la conversion des images existantes ont été retirées.
> Voir [les instructions de stockage actuelles](../../../README.md#stockage-s3-local-et-interface).

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Fournir Garage + Garage Web UI dans Docker et y migrer les images des jeux avec vérification.

**Architecture:** Le disque des images est configurable et indépendant du stockage privé. Garage expose une API S3 interne et un endpoint web public local ; son interface reste un outil d’administration. Une commande distincte copie les objets avant une bascule explicite.

**Tech Stack:** Laravel 13, Filament 5, Flysystem S3 v3, Docker Compose, Garage 2.x, Garage Web UI.

**Spec:** `docs/superpowers/specs/2026-11-15-local-image-storage-design.md`

## Global Constraints

- Travailler dans le checkout existant, branche `codex/local-image-storage`.
- `GAME_IMAGES_DISK` utilise `public` en repli ; les nouvelles installations choisissent `s3`.
- Bucket `gamestackr-images` ; clés `games/{uuid}/{nom}.avif` conservées.
- Images publiques, conversion AVIF et garanties transactionnelles existantes conservées.
- Secrets générés et ignorés, ports du navigateur sur `127.0.0.1`, versions Docker fixées.
- `make init` et `make up` relançables ; `make down` conserve les volumes.
- Miniatures facultatives, aucune galerie spécifique ni déploiement de production.
- Copier et vérifier avant bascule ; conserver les sources et ne jamais écraser un conflit.

## Review Focus

- Un stockage S3 sans chemin local doit fonctionner pour l’ancienne conversion (test tâche 1).
- Une panne d’écriture doit préserver référence et image précédente ; nettoyage après commit journalisé (tâche 1 et suite existante).
- Migration interrompue, fichier manquant ou destination différente : relance sûre et échec explicite (tâche 2).
- Réinitialisation avec données existantes : secrets, layout et fichiers conservés ; configuration externe non écrasée (tâche 3).
- URL accessible au navigateur mais endpoint interne distinct, CORS et absence d’ACL AWS (test réel tâche 4).

---

### Task 1: Disque des images configurable et conversion portable

**Files:** créer `config/game-images.php`, `tests/Feature/GameImageStorageTest.php` ; modifier `app/Services/GameImages.php`, `app/Support/PublicGames.php`, `app/Filament/Resources/Games/Schemas/GameForm.php`, `app/Filament/Resources/Games/Tables/GamesTable.php`, `app/Console/Commands/ConvertGameImagesToAvif.php`, `phpunit.xml`.

**Interfaces:** produit `GameImages::diskName(): string` et `GameImages::disk(): Illuminate\Filesystem\FilesystemAdapter` ; tous les consommateurs utilisent la configuration `game-images.disk`.

- [x] Écrire les tests : création/remplacement/renommage/suppression sur le disque configuré, URL publique configurable, disque `public` intact, erreur d’écriture préservant le jeu, conversion d’un disque dont `path()` est indisponible.
- [x] Exécuter `docker compose exec -T app php artisan test --compact tests/Feature/GameImageStorageTest.php` ; attendre les échecs de sélection du disque.
- [x] Implémenter les accès centralisés et la conversion via fichier temporaire nettoyé dans `finally` ; imposer `GAME_IMAGES_DISK=public` dans PHPUnit.
- [x] Exécuter les tests nouveaux et `tests/Feature/ConvertGameImagesTest.php`, `tests/Feature/GameTest.php`, `tests/Feature/Filament/GameResourceTest.php` ; attendre succès.
- [x] Commit `feat: make game image storage configurable`.

### Task 2: Copie vérifiée vers S3

**Files:** créer `app/Console/Commands/CopyGameImagesToS3.php`, `tests/Feature/CopyGameImagesTest.php`.

**Interfaces:** consomme `GameImages::isManagedPath(string): bool`. Produit `games:copy-images-to-s3 {--dry-run}` ; source `public`, destination `s3`, aucun changement SQL ou de configuration.

- [x] Écrire les tests : copie des jeux visibles et masqués, conservation source/clé, simulation sans écriture, relance identique, conflit non écrasé, source manquante, chemin non géré, échec d’écriture et copie corrompue détectés, poursuite des autres objets.
- [x] Exécuter `docker compose exec -T app php artisan test --compact tests/Feature/CopyGameImagesTest.php` ; attendre commande absente.
- [x] Implémenter la commande avec SHA-256 du contenu relu, bilan et code non nul en cas d’erreur ; ne jamais modifier le disque actif.
- [x] Relancer les tests et attendre succès.
- [x] Commit `feat: add verified game image migration to S3`.

### Task 3: Garage et interface dans Docker

**Files:** modifier `compose.yaml`, `Makefile`, `.env.example`, `composer.json`, `composer.lock`, `config/filesystems.php` ; créer `docker/garage/configure.php`, `docker/garage/initialize.php`, `tests/Feature/GarageConfigurationTest.php`. Les fichiers générés vivent sous `storage/app/private/garage/`.

**Interfaces:** `configure.php` prépare la configuration et les secrets locaux sans modifier les valeurs existantes ; `initialize.php` initialise le layout, le bucket, les droits et le CORS via les API locales. Les scripts s’exécutent dans le conteneur PHP et sont appelés par Make. `make storage-password` permet de gérer l’accès Web UI. Le SDK S3 est partagé par Laravel et l’initialisation.

- [x] Vérifier les versions et architectures disponibles dans les registres ; fixer une paire Garage/Web UI compatible.
- [x] Écrire les tests d’exécution de la configuration dans un répertoire temporaire : génération, permissions, réexécution identique, configuration externe préservée, absence de secrets dans les sorties.
- [x] Exécuter ces tests et constater l’absence du générateur.
- [x] Ajouter l’adaptateur Composer S3, les services persistants, les scripts, l’orchestration Make et les variables d’exemple. L’interface ne conditionne pas le démarrage de Laravel.
- [x] Exécuter les tests de configuration, `docker compose config --quiet`, puis démarrer le stockage local ; attendre succès et authentification de l’interface.
- [x] Relancer l’initialisation avec un objet de test et vérifier son contenu ainsi que la stabilité des clés, sans exposer les secrets.
- [x] Commit `feat: run Garage and its web interface locally`.

### Task 4: Validation réelle, bascule locale et documentation

**Files:** modifier `README.md` ; créer `docs/superpowers/local-image-storage-verification.md` et un script d’intégration ciblé si nécessaire.

**Interfaces:** consomme le disque S3, les commandes et les services des tâches 1–3. Utilise des jeux et objets temporaires identifiables pour les essais. La copie réelle n’efface jamais les sources.

- [x] Exécuter Pint, PHPStan et la suite PHP complète ; corriger les échecs puis obtenir une suite verte.
- [x] Sur Garage réel, écrire/lire/lister/remplacer/supprimer des objets de test et vérifier les URL, MIME et CORS. Exécuter la conversion AVIF avec un vrai objet WebP de test.
- [x] Dans le navigateur, vérifier l’authentification Web UI, la navigation dans le bucket, un import Filament et l’affichage public. Constater les possibilités natives de prévisualisation.
- [x] Suspendre les écritures locales, exécuter simulation et copie des images existantes, vérifier tous les contenus ; seulement en cas de succès, activer `GAME_IMAGES_DISK=s3`, vider le cache, redémarrer et vérifier l’affichage.
- [x] Documenter installation, accès, mot de passe, migration, sauvegardes/limites et retour au stockage local. Consigner les résultats réels.
- [x] Commit `docs: document and verify local image storage`.
- [x] Faire relire toute la branche par un agent indépendant ; corriger les défauts importants avec tests de régression, puis conserver la branche pour l’utilisateur sans fusion ni push implicite.
