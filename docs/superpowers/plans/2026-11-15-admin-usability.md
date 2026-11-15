# Administration lisible — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Rendre l’administration Filament compréhensible et confortable pour un nouvel utilisateur.

**Architecture:** Garder les ressources existantes, ajouter un widget d’accueil et des liens contextualisés. Utiliser les sections repliables et les colonnes configurables de Filament, avec du CSS limité au panneau.

**Tech Stack:** Laravel 13, Filament 5, Livewire, Blade, CSS/Vite, PostgreSQL, Docker/Make.

**Spec:** [Conception](../specs/2026-11-15-admin-usability-design.md)

## Global Constraints

- Conserver les validations, autorisations administrateur vérifié, comportements d’images, statuts et identités.
- Aucun nouveau modèle métier, migration, produit, carte ou écran public.
- Commandes PHP/Node dans Docker via Make ; tests exclusivement gamestackr_testing.
- Les aides restent consultables au clavier ; aucune information essentielle ne doit dépendre du survol.
- Exécution directe autorisée ; reprendre le checkout propre avec une branche dédiée, sans dupliquer le montage Docker.

## Review Focus

- Widget appelé directement ou rôle révoqué : refus d’accès (test tâche 1).
- Langue française limitée au panneau, y compris après action Livewire (test HTTP tâche 1 et navigateur tâche 2).
- Paramètres catalogue/série incohérents ou malformés : aucune série étrangère préremplie (test tâche 1).
- Base vide et longs noms : états utiles et absence de débordement (test widget tâche 1, navigateur tâche 2).
- Champs repliés : valeurs préservées et erreurs atteignables au clavier (tests de mutation existants et navigateur tâche 2).

### Task 1: Accueil, navigation et contexte

**Files:** créer `app/Filament/Widgets/CatalogOverview.php`, sa vue `resources/views/filament/widgets/catalog-overview.blade.php`, `app/Http/Middleware/SetAdminLocale.php` et `tests/Feature/Filament/AdminUsabilityTest.php`. Modifier `AdminPanelProvider.php`, la navigation des six ressources, les pages catalogue/série/extension et leurs actions de liste/création.

**Interfaces:** `CatalogOverview::canView(): bool` autorise l’administrateur vérifié ; sa vue reçoit les effectifs réels et les liens des ressources. `SetAdminLocale::handle(Request, Closure): Response` applique fr à la requête puis restaure la langue précédente. Paramètres de création `catalog` et `series` ; filtres existants `filters[catalog][value]`, `filters[series][value]`.

- [x] Écrire les tests : accueil vide puis effectifs exacts, refus direct du widget, locale HTTP fr sans fuite, liens filtrés ne montrant que les enfants du parent, création préremplie cohérente et paramètres invalides ignorés.
- [x] Exécuter `make test ARGS='tests/Feature/Filament/AdminUsabilityTest.php'` ; constater les échecs correspondant aux fonctionnalités manquantes.
- [x] Ajouter le widget, le middleware persistant du panneau, les groupes de navigation et les liens/préremplissages décrits.
- [x] Relancer les tests ciblés et ceux de `CatalogResourcesTest.php` ; résultat attendu : réussite.
- [x] Enregistrer le lot fonctionnel et son résultat dans le journal.

### Task 2: Formulaires, tableaux et présentation

**Files:** `CatalogFields.php`, les schémas et relation managers du catalogue, les tables des ressources, `CreateCatalogRecord.php`, `EditCatalogRecord.php`, les pages Jeux et `resources/css/filament/admin.css`.

**Interfaces:** étendre `CatalogFields::layout` avec `array $advanced = []` ; conserver les paramètres existants. Les sections d’aide et options avancées utilisent les composants natifs. Les colonnes clés deviennent configurables, les noms/contexte restent recherchables.

- [x] Réorganiser les champs et déplacer les explications longues vers l’aide repliable ; raccourcir les aides immédiates sans perdre les distinctions métier.
- [x] Simplifier les tableaux, alléger les actions de suppression et améliorer le contraste, les espacements et la présentation du widget en clair/sombre.
- [x] Exécuter `make check` et `make assets` ; résultat attendu : succès. Pas de tests miroir des textes/CSS.
- [x] Vérifier dans le navigateur sur la base de tests : accueil vide/rempli, liens et création, recherche après Livewire en français, ouverture clavier des aides, erreurs dans les options, clair/sombre, écran étroit. Corriger les défauts observés.
- [x] Enregistrer le lot et les preuves. La revue indépendante, la publication et la fusion suivent ensuite AGENTS.md ; leurs résultats sont tracés dans la PR.

## Résultats de validation

- `make check` réussi : Pint, PHPStan, **263 tests / 1880 assertions**, lint frontend et TypeScript.
- `make assets` réussi. Aucun changement de stockage ou de traitement des images ; suite Garage locale non requise, exécutée par la CI de la PR.
- Navigateur sur `gamestackr_testing` : accueil vide puis rempli, clair/sombre, série → extensions filtrées → création préremplie et enregistrée, recherche par jeu après Livewire, aides ouvertes au clavier, formulaires et accueil à 390 px, tableau à défilement horizontal contenu, noms longs.
- Une erreur métier dans une section repliée a été reproduite, couverte par un test en échec, corrigée et revérifiée dans le navigateur : ouverture automatique et message visible.
- Traductions françaises ajoutées pour les libellés d’accessibilité manquants de Filament.
- Choix d’exécution : checkout existant sur branche dédiée, pour conserver le montage Docker de l’application.
- Revue indépendante : correction du préchargement des jeux dans les tableaux séries/extensions lorsque leur colonne est masquée. Test ciblé rouge (3 requêtes supplémentaires), puis vert (0 requête pendant l’affichage des descriptions). `make check` relancé avec succès.
