# Jeux — vérification du 14 novembre 2026

## Correction SVG

- Les SVG sont conservés en `.svg`, après nettoyage avec
  `enshrined/svg-sanitize` 1.1.1 ; JPEG/PNG/WebP restent convertis en WebP.
- Limite SVG de 5 Mio, sans limite de dimensions en pixels.
- Huit tests supplémentaires couvrent l’upload Filament, le format vectoriel,
  les renommages, les remplacements SVG ↔ WebP, le retrait, la suppression,
  le rollback, les contenus actifs et le XML invalide.
- `composer ci:check` réussi : **94 tests, 369 assertions**, Pint, PHPStan,
  frontend et TypeScript réussis ; `composer validate --strict` réussi.
- Référence : [SVG sanitizer](https://github.com/darylldoyle/svg-sanitizer).

## Réalisation

- Modèle, migration, factory, policy et ressource Filament disponibles.
- Nom, slug unique et image optionnelle éditables ; miniature et recherche.
- JPEG/PNG/WebP convertis en WebP local ; transparence et orientation EXIF testées.
- Fichiers isolés par UUID, nommés d’après le jeu ; renommage, remplacement,
  retrait, suppression individuelle et groupée pris en charge.
- Validation avant écriture, nettoyage après commit et sur rollback, y compris
  au sein d’une transaction externe.
- Protection des chemins d’images contre les références étrangères ou périmées.
- Docker reconstruit avec GD/WebP et EXIF ; services redémarrés et sains.
- Migration `2026_11_14_120000_create_games_table` appliquée en développement.
- Trois routes `/admin/games` enregistrées, lien de stockage public vérifié.

## Preuves

- Avant modification : **54 tests, 200 assertions**.
- Nouveaux tests exécutés avant la ressource et les services : échec attendu.
- Tests Game : **21 tests** avec vrais fichiers temporaires et PostgreSQL.
- Tests Filament/Livewire : **11 tests**, dont les trois routes, les permissions,
  l’upload, le retrait, la suppression groupée et deux sauvegardes consécutives.
- Vérification finale : `docker compose exec -T app composer ci:check` réussie.
  **86 tests, 330 assertions**, Pint, PHPStan, frontend et TypeScript réussis.
- `composer validate --strict` et `git diff --check` réussis.

## Revue et corrections

- Le test de deux sauvegardes sans rechargement a révélé un chemin périmé après
  renommage ; `EditGame::afterSave()` recharge désormais le champ image.
- Une revue indépendante a identifié la suppression d’une instance devenue
  périmée après remplacement d’image. Le test a reproduit le fichier orphelin,
  puis est passé après verrouillage et relecture dans `Game::delete()`.
- Aucun autre problème important ou mineur signalé par la revue.

## Décisions et limites

- Interprétation de « format web » : WebP ; limites choisies : 5 Mio et
  4096 × 4096 pixels, qualité 85. Le slug reste stable lors d’un renommage.
- Service `SaveGame` pour les modifications ; suppression via instances Eloquent.
  Les requêtes SQL de masse et méthodes sans événements ne font pas partie de
  cette API de gestion des fichiers.
- L’atomicité ne couvre pas une interruption brutale du processus ou une panne
  durable du stockage. Les erreurs de nettoyage sont rapportées aux logs.
- Une seule connexion PostgreSQL utilisée par l’application.

## Références consultées

- [FileUpload Filament 5](https://filamentphp.com/docs/5.x/forms/file-upload) :
  stockage différé et protection des chemins.
- [Conversion WebP PHP](https://www.php.net/manual/en/function.imagewebp.php) :
  encodage GD et vérification du contenu produit.
