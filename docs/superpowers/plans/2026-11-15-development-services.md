# Development Services Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Afficher dans Filament la réponse des services et leur état Docker, uniquement en local.

**Architecture:** Un collecteur PHP isolé écrit un rapport JSON atomique dans un volume partagé en lecture seule par Laravel. Un service Laravel combine le rapport frais avec trois sondes de lecture. Une page Filament native présente les résultats et les liens externes.

**Tech Stack:** PHP 8.4, Laravel 13, Filament 5, Docker Compose, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-15-development-services-design.md`

## Global Constraints

- Branche `codex/admin-development-services`, checkout existant propre réutilisé.
- PHP/Node via Docker et Make ; tests exclusivement sur `gamestackr_testing`.
- Administrateurs vérifiés, `APP_ENV=local`, aucun accès induit par `APP_DEBUG`.
- Collecte 5 s, actualisation UI 15 s, rapport périmé au-delà de 30 s.
- Aucun secret publié, aucun contrôle d’écriture sur Docker ou les services.
- Exécution dans cette session ; revue indépendante complète avant fusion.

## Review Focus

- Rapport ancien, absent ou invalide : état Docker inconnu, sondes toujours disponibles (tâche 1).
- Projet Compose différent, commandes Make et conteneurs absents : aucune fausse attribution (tâche 1).
- Passage hors local et appels Livewire : accès refusé et aucun contrôle réseau (tâche 3).
- Service en panne : message générique, contrôle borné, autres résultats préservés (tâche 2).
- Ports personnalisés et mobile : liens corrects et présentation sans débordement (tâche 3).

### Task 1: Rapport Docker filtré et collecteur isolé

**Files:** `app/Support/Development/DockerCollector.php`, `DockerSnapshot.php`, `docker/development/collect.php`, `compose.yaml`, `config/development.php`, `tests/Unit/DockerCollectorTest.php`, `tests/Feature/Development/DockerSnapshotTest.php`.

**Interfaces:** `DockerCollector::collect(): void` reçoit le projet, le répertoire et une closure de lecture API ; écrit `services.json`. `DockerSnapshot::read(): array` retourne un rapport validé ou un tableau vide.

- [ ] Écrire les tests de filtrage projet/service/oneoff, état arrêté, rapport minimal et publication atomique ; lecture absente/corrompue/périmée.
- [ ] Exécuter `make test ARGS='--filter=Docker'`, constater les échecs attendus.
- [ ] Implémenter les deux classes, le runner et le volume isolé ; pas de réseau pour le collecteur.
- [ ] Relancer les tests ciblés, attendre leur réussite et committer.

### Task 2: Disponibilité et catalogue des services

**Files:** `app/Support/Development/ServiceHealth.php`, `ServiceCatalog.php`, `tests/Feature/Development/ServiceHealthTest.php`, `config/development.php`.

**Interfaces:** `ServiceHealth::check(): array` expose PostgreSQL, Mailpit et Garage avec résultat et durée ; `ServiceCatalog::links(): array` calcule les liens depuis les ports configurés ; consomme `DockerSnapshot::read()` dans la page.

- [ ] Écrire les tests d’une sonde réussie, d’une panne isolée, d’erreurs masquées, de timeouts et de liens à ports personnalisés.
- [ ] Exécuter `make test ARGS='--filter=ServiceHealth'`, constater les échecs attendus.
- [ ] Implémenter les sondes bornées, sans mutation, avec erreurs génériques.
- [ ] Relancer les tests ciblés et committer après réussite.

### Task 3: Page Filament, navigation et vérifications complètes

**Files:** `app/Filament/Pages/DevelopmentServices.php`, `app/Providers/Filament/AdminPanelProvider.php`, `tests/Feature/Filament/DevelopmentServicesTest.php`, `README.md`, rapport de vérification.

**Interfaces:** Page `/admin/development-services`, action `refreshStatus()`, schéma Filament calculé depuis les résultats serveur ; liens de `ServiceCatalog::links()`.

- [ ] Écrire les tests de navigation locale, accès production/staging/utilisateurs ordinaires, rafraîchissement Livewire et affichage dégradé.
- [ ] Exécuter les tests ciblés et constater les échecs attendus.
- [ ] Implémenter la page avec Sections/Grid/TextEntry/Actions ; rafraîchissement visible toutes les 15 s.
- [ ] Vérifier les tests, `make up`, le rapport réel, les liens et les rendus desktop/mobile.
- [ ] Documenter l’usage et les limites ; exécuter `make check`, `make assets`, `make test-storage` et `git diff --check`.
- [ ] Committer, demander une revue indépendante de la plage complète, publier la PR et attendre la CI ; fusion suivant `AGENTS.md`.
