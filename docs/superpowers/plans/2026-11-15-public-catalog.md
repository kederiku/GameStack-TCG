# Public Catalog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Remplacer la maquette A validée par le catalogue public connecté à l’admin.

**Architecture:** Requêtes publiques filtrées côté Laravel, DTO explicites et pages Inertia. Composants React partagés pour la grille et les fiches ; préférence FR/EN serveur indépendante des éditions. Aucun changement de schéma ou publication automatique.

**Tech Stack:** Laravel, PostgreSQL, Inertia, React/TypeScript, shadcn/ui, Docker/Make.

**Spec:** `docs/superpowers/specs/2026-11-15-public-catalog-design.md`

## Global Constraints

- Travail sur la branche existante. Livraison initialement locale ; publication
  et fusion ensuite autorisées explicitement par l’utilisateur selon `AGENTS.md`.
- Tests exclusivement sur `gamestackr_testing`, commandes PHP/Node dans Docker.
- Site React sombre uniquement. Administration Filament indépendante.
- Aucun seeder automatique, aucune carte ou statistique inventée.

## Review Focus

- Ancêtre masqué : aucune fuite par URL, compteurs, recherche ou relations.
- Traduction absente : nom de repli explicite et image de l’édition sélectionnée.
- Langue d’édition supprimée : repli annoncé, changement FR/EN sans changer l’édition.
- Dates absentes/annulées et sorties multiples : ordre déterministe et contexte de marché.
- Petit écran, noms longs et catalogues vides : contrôles utilisables, pas de débordement.

### Task 1: Données publiques et routes

**Files:** `app/Support/Catalog/PublicCatalog.php`, `app/Support/Catalog/PublicCatalogPresentation.php`, `app/Http/Controllers/PublicGameController.php`, `routes/web.php`, `tests/Feature/Catalog/PublicCatalogTest.php`.

**Interfaces:** `PublicCatalog::index(Game, Request): array` retourne catalogues, langues, filtres, extensions paginées, total et métadonnées du jeu. `PublicCatalog::show(Game, string $catalogKey, string $expansionKey, Request): array` retourne fiche, éditions visibles, édition choisie, sorties et relations publiques. DTO frontend dans `types/catalog.ts`.

- [x] Écrire les tests HTTP visibilité, accès par clés, éditions, replis, filtres et pagination ; vérifier l’échec sur les nouvelles propriétés/routes.
- [x] Implémenter requêtes avec relations préchargées, projection explicite, recherche noms/codes, sorties non annulées pour l’ordre chronologique. Filtrer avant pagination.
- [x] Exécuter `make test ARGS='--filter=PublicCatalogTest'` ; attendu : succès.

### Task 2: Langue, thème et pages réutilisables

**Files:** middleware `SetSiteLocale`, `HandleInertiaRequests`, Blade, hook `use-locale`, composant `language-switcher`, `PublicHeader`, `PublicLayout`, `GameSelector`, `welcome`, `components/catalog/*`, `pages/games/show`, `pages/games/expansion`, composants de thème et `tests/Feature/SiteLocaleTest.php`.

**Interfaces:** prop partagée `locale: 'fr' | 'en'`, hook `useLocale()` fournit `locale`, `t(fr,en)`, `localizedUrl(url)` ; sélecteur conserve paramètres courants et `edition`. Pages utilisent les DTO de Task 1.

- [x] Tester défaut FR, paramètre EN, cookie, invalides/tableaux et premier rendu sombre ; exécuter les tests et constater l’échec.
- [x] Implémenter préférence et sélecteur shadcn dans la topbar existante, traduire parcours public, supprimer le choix de thème React.
- [x] Construire grille A, filtres, groupes, pagination, fiche et sorties réelles à partir des composants partagés.
- [x] Exécuter tests ciblés, lint et TypeScript ; attendu : succès.

### Task 3: Validation et finition locale

**Files:** suppression de `components/catalog-prototype/`, documentation README et rapport de vérification.

**Interfaces:** parcours local réel sans paramètres de maquette ; données administrées inchangées.

- [x] Vérifier navigateur : liste, filtre, fiche, édition, FR/EN, navigation compte, mobile ; absence de débordement.
- [x] Retirer maquette et documenter les choix et résultats.
- [x] Exécuter `make check`, `make assets`, `git diff --check` ; attendu : succès. Réexécuter seulement les contrôles affectés par des corrections.
- [x] Faire relire le changement complet par un agent indépendant ; corriger les défauts importants et vérifier les corrections.
- [x] Livrer initialement les modifications sur la branche locale, sans push ni PR.

L’utilisateur a ensuite demandé de renommer la branche en fonction du travail
final, de commiter l’ensemble et de suivre le workflow de fusion vers `main`.
La publication doit inclure une revue indépendante du commit final, les contrôles
locaux, la CI de la PR puis celle du commit squash sur `main`.
