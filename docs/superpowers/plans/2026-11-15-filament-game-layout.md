# Filament Game Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Organiser les formulaires des jeux en sections et améliorer leur liste.

**Architecture:** Schéma Filament natif à deux groupes adaptatifs ; sections
dans chaque groupe ; présentation et visibilité des colonnes ajustées.

**Tech Stack:** Filament 5, Laravel 13, PHPUnit/Livewire.

**Spec:** `docs/superpowers/specs/2026-11-15-filament-game-layout-design.md`

## Global Constraints

- Conserver la branche et les comportements métier existants.
- Sections avec image et visibilité en colonne latérale, choix de l’utilisateur.
- Composants natifs ; pas de dépendance ou thème supplémentaires.
- Vérification visuelle en lecture seule sur les jeux existants.

## Review Focus

- Les groupes doivent conserver les chemins des champs Livewire.
- Le slug doit rester recherchable même si sa colonne est masquée par défaut.
- Les logos doivent rester entiers, sans recadrage ni transformation ajoutée.
- Sur mobile, tous les champs doivent rester accessibles.
- La visibilité doit être compréhensible indépendamment de la couleur.

### Task 1: Formulaires et liste

**Files:** `app/Filament/Resources/Games/Schemas/GameForm.php`,
`app/Filament/Resources/Games/Tables/GamesTable.php`,
`tests/Feature/Filament/GameResourceTest.php`, `README.md`.

**Interfaces:** Aucun changement des clés de formulaire ni du contrat de sauvegarde.

- [x] Organiser le formulaire avec Group et Section, colonnes adaptatives et icônes.
- [x] Améliorer la présentation des colonnes de la liste.
- [x] Vérifier les parcours Livewire existants et la recherche par slug.
- [x] Inspecter le rendu navigateur, puis exécuter `composer test`.
- [x] Faire relire le diff, consigner les résultats et créer un commit.

**Validation :** `docker compose exec -T app php artisan test --compact --filter=GameResourceTest`
puis `docker compose exec -T app composer test` ; résultat attendu : tout passe.

## Résultats

- Revue indépendante : correction de la grille `lg` héritée des pages Filament.
  Un test sur les schémas réels de création et modification reproduit le défaut
  (deux colonnes au lieu d’une), puis passe après l’ajout explicite de `lg => 1`.
- Recherche par slug : test de régression validé malgré la colonne masquée.
- Vérification Chrome : création et liste sur grand écran ; modification à
  390 × 844 px et 1100 × 844 px. Sections empilées aux petites et moyennes largeurs,
  disposition 2:1 sur grand écran, aperçu du logo et boutons accessibles.
- Liste contrôlée à 390 px : images et éditeurs masqués, noms lisibles sur une
  ligne, badges avec texte et icône ; défilement horizontal natif conservé.
- Aucune donnée de jeu modifiée pendant la vérification visuelle.
- `composer test` final : Pint valide 89 fichiers, PHPStan sans erreur,
  **159 tests réussis, 994 assertions**. `git diff --check` passe.
