# Game Details Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ajouter quatre champs facultatifs aux jeux et aux formulaires Filament.

**Architecture:** Migration additive, attributs et casts Eloquent, validation
dans `SaveGame`, composants Filament existants. Les écritures partielles
conservent les valeurs omises et les valeurs vides effacent les informations.

**Tech Stack:** Laravel 13, Filament 5, Livewire, PostgreSQL, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-15-game-details-design.md`

## Global Constraints

- `start_year`, `end_year`, `publisher`, `description` sont nullables.
- Années entières entre 1000 et 9999 ; fin ≥ création si les deux sont connues.
- Éditeur : 255 caractères maximum ; description : texte simple.
- Garder les données et les parcours existants, notamment les images.

## Review Focus

- Modifier le nom seul ne doit pas effacer les informations existantes.
- Effacer une année doit fonctionner sans exiger l’autre.
- Modifier uniquement la création doit respecter une fin déjà enregistrée.
- Les champs invalides doivent être refusés avant le stockage d’une image.
- Les champs vides depuis Filament doivent être enregistrés comme `null`.

### Task 1: Persistance et formulaires des informations facultatives

**Files:**
- Create: `database/migrations/2026_11_15_120000_add_details_to_games_table.php`
- Modify: `app/Models/Game.php`
- Modify: `app/Actions/Games/SaveGame.php`
- Modify: `app/Filament/Resources/Games/Schemas/GameForm.php`
- Create: `tests/Feature/GameDetailsTest.php`
- Modify: `tests/Feature/Filament/GameResourceTest.php`
- Modify: `README.md`

**Interfaces:** `SaveGame::handle(Game $game, array $data): Game` accepte les
quatre clés facultatives et conserve celles qui sont omises. `Game` expose les
années sous forme `?int`, les autres informations sous forme `?string`.

- [x] Écrire les tests de sauvegarde et Filament pour les quatre champs,
  leur effacement et préservation, les années isolées/égales et les erreurs.
- [x] Exécuter les tests ciblés et constater les échecs liés aux champs absents.
- [x] Ajouter migration, modèle, validation et formulaire ; actualiser le README.
- [x] Exécuter les tests ciblés puis Pint, PHPStan et la suite complète.
- [x] Relire le diff avec un agent de revue et corriger les défauts avérés.
- [x] Appliquer la migration locale et conserver le travail sur la branche dédiée.

**Commandes de vérification :**

`docker compose exec -T app php artisan test --compact --filter='GameDetailsTest|GameResourceTest'`

`docker compose exec -T app composer test`

Résultat attendu : tous les tests et contrôles passent.

## Résultats du 15 novembre 2026

- État initial : 119 tests, 757 assertions, tous réussis.
- Avant implémentation : 26 échecs attendus (champs/formulaire absents,
  informations ignorées et validation inexistante), 12 tests existants réussis.
- Après implémentation : 38 tests ciblés, 199 assertions, tous réussis.
- `composer test` : Pint validé sur 87 fichiers, PHPStan sans erreur,
  144 tests et 860 assertions réussis.
- Revue indépendante en lecture seule : aucun défaut actionnable.
- Migration additive appliquée à la base locale ; aucun jeu prérempli.
- `git diff --check` : aucune erreur.
