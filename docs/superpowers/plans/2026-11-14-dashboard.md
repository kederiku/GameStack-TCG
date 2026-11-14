# Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Réaliser le dashboard Mon espace dans le design GameStackr partagé.

**Architecture:** Conserver le layout React/Inertia et les primitives shadcn.
Mutualiser les tokens visuels, le logo, la grille de jeux et les états vides.
Le dashboard reçoit les jeux par le présentateur public existant.

**Tech Stack:** Laravel, React, TypeScript, Inertia, Tailwind, shadcn/ui.

**Spec:** `docs/superpowers/specs/2026-11-14-dashboard-design.md`

## Global Constraints

- Aucun chiffre de collection ou prix inventé, aucune nouvelle dépendance.
- Préserver auth/verified, les formulaires et les préférences de thème.
- Utiliser les jeux réels de Filament, sans fixture de production.
- Réutiliser le checkout propre `codex/immersive-landing` et son environnement Docker.

## Review Focus

- Zéro jeu : état vide lisible et aucun accès trompeur.
- Image absente ou cassée, nom long : initiales et retour à la ligne.
- Mobile : tiroir utilisable au clavier et fermé après navigation.
- Menus portalisés : même thème que la page.
- Profil/sécurité/apparence : navigation et formulaires existants préservés.

### Task 1: Données du dashboard

**Files:** `tests/Feature/DashboardTest.php`, `app/Http/Controllers/DashboardController.php`, `routes/web.php`.

**Interfaces:** consomme `PublicGames::all()`, produit `games: PublicGame[]` sur la page `dashboard`.

- [x] Ajouter les tests de jeux triés (Magic puis Pokémon), image/null, slug réservé exclu, liste vide et utilisateur non vérifié.
- [x] Exécuter `docker compose exec -T app php artisan test --compact --filter=DashboardTest` ; vérifier l’échec sur `games` absent.
- [x] Brancher un contrôleur invokable conservant auth/verified et utilisant `PublicGames::all()`.
- [x] Relancer les tests dashboard ; attendre un succès.

### Task 2: Design et composants partagés

**Files:** `resources/css/app.css`, composants marque/jeux/état vide, layouts et navigation authentifiés, `resources/js/pages/{dashboard,welcome}.tsx`, `resources/js/pages/games/show.tsx`.

**Interfaces:** `GameGrid({games: PublicGame[]})`, `GameCard({game: PublicGame})`, `EmptyState({icon, title, description, children?, className?})` ; le dashboard consomme `games` et `auth.user`.

- [x] Déplacer logo, image et sélecteur de jeux dans des emplacements partagés et mettre à jour tous les imports.
- [x] Unifier les tokens React et extraire grille/tuile et état vide ; utiliser ces composants sur les pages existantes.
- [x] Harmoniser la sidebar/topbar et les menus, préserver fermeture mobile et raccourci sidebar.
- [x] Construire l’accueil personnel, le panneau collection à venir, les jeux et les accès compte.
- [x] Vérifier `npm run check`, `npm run types:check` et `npm run build` dans Docker.

### Task 3: Validation et revue

**Files:** `docs/superpowers/dashboard-verification.md`, `README.md`.

**Interfaces:** vérifier les routes et composants précédents dans l’application réelle.

- [x] Exécuter `composer ci:check` dans Docker ; attendre tous les tests verts.
- [x] Vérifier au navigateur desktop/mobile, thèmes, sidebar, menus, liste vide, images et noms longs via fixtures temporaires de test.
- [x] Vérifier l’accueil, la connexion et les réglages après mutualisation.
- [x] Faire relire le diff par un agent indépendant ; corriger les défauts importants.
- [x] Documenter les résultats et livrer le travail localement, sans publication.
