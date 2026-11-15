# Game Visibility Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permettre de masquer ou réafficher un jeu depuis Filament.

**Architecture:** Colonne `is_visible`, validation dans `SaveGame`, interrupteur
et indicateur Filament, filtre du catalogue partagé et garde de la page publique.

**Tech Stack:** Laravel 13, Filament 5, PostgreSQL, PHPUnit/Livewire.

**Spec:** `docs/superpowers/specs/2026-11-15-game-visibility-design.md`

## Global Constraints

- Continuer sur `codex/game-details` ; valeur par défaut : `true`.
- Jeux masqués absents de toutes les listes du site ; leur page renvoie 404.
- Les jeux masqués restent administrables, avec leurs données et leur image.
- Une clé `is_visible` omise conserve la valeur ; une valeur invalide est refusée.

## Review Focus

- Un formulaire de création ne doit pas masquer un jeu par défaut.
- Une sauvegarde partielle ne doit pas republier un jeu masqué.
- Les listes d’authentification et du dashboard doivent suivre le même filtre.
- L’URL directe ne doit pas contourner le masquage, même connecté.
- Les administrateurs doivent pouvoir retrouver et réactiver un jeu masqué.

### Task 1: Visibilité de bout en bout

**Files:** nouvelle migration `2026_11_15_130000_add_visibility_to_games_table.php` ;
`app/Models/Game.php`, `app/Actions/Games/SaveGame.php`,
`app/Support/PublicGames.php`, `app/Http/Controllers/PublicGameController.php`,
schéma et table de la ressource Filament Games, `README.md`.

**Tests:** nouveau `tests/Feature/GameVisibilityTest.php` ; compléter
`tests/Feature/Filament/GameResourceTest.php`, `tests/Feature/Auth/AuthGamesTest.php`
et `tests/Feature/DashboardTest.php`.

**Interfaces:** `Game::is_visible` est un booléen ; `SaveGame::handle()` accepte
la clé facultative `is_visible` ; `PublicGames::all()` conserve le format existant.

- [x] Écrire les tests ; exécuter les tests ciblés et constater les échecs.
- [x] Ajouter migration, modèle, sauvegarde, formulaire et filtre public.
- [x] Valider les tests ciblés, Pint, PHPStan et toute la suite.
- [x] Faire relire le diff, appliquer la migration locale, documenter les résultats.
- [x] Créer un commit sur la branche existante.

**Validation :**
`docker compose exec -T app php artisan test --compact --filter='GameVisibilityTest|GameResourceTest|AuthGamesTest|DashboardTest|PublicGameTest'`
puis `docker compose exec -T app composer test` ; résultat attendu : tout passe.

## Résultats du 15 novembre 2026

- Avant implémentation : 18 échecs attendus (champ absent, état ignoré et absence
  de validation), 32 tests réussis.
- Après implémentation : 50 tests ciblés et 603 assertions, tous réussis.
- `composer test` : Pint validé sur 89 fichiers, PHPStan sans erreur,
  157 tests et 986 assertions réussis.
- Migration locale appliquée ; les jeux existants conservent leur visibilité.
- Revue indépendante en lecture seule : aucun défaut actionnable. Les formulaires
  complets conservent le fonctionnement existant (dernière sauvegarde gagnante) ;
  la garantie de préservation concerne une clé omise. L’accès aux fichiers d’images
  reste public, conformément au périmètre.
- `git diff --check` : aucune erreur.
