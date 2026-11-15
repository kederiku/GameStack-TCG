# GitHub Merge Workflow Implementation Plan

> **For agentic workers:** Use superpowers:executing-plans. Steps use checkbox syntax for tracking.

**Goal:** Publier et fusionner la branche vérifiée, avec un parcours obligatoire pour les agents.
**Architecture:** GitHub Actions conserve le job `ci`, complété par les tests Garage. `AGENTS.md` décrit la revue indépendante et la fusion du SHA vérifié ; GitHub protège `main`.
**Tech Stack:** Docker Compose, Make, GitHub Actions, GitHub CLI.
**Spec:** `docs/superpowers/specs/2026-11-15-github-merge-workflow-design.md`.

## Global Constraints

- L’utilisateur a choisi la fusion automatique après revue et CI verte.
- Préserver les données et secrets locaux ; rester sur la branche existante.
- Ne pas contourner les protections, ne pas forcer un push, ne pas fusionner un SHA non relu.

## Review Focus

- Le contrôle requis doit garder le nom `ci` et provenir de GitHub Actions.
- Un push après revue invalide son SHA ; une CI verte ancienne ne suffit pas.
- La suite Garage réelle doit cibler exclusivement la base dédiée et ses propres objets.
- Aucun secret généré ne doit figurer dans le commit ou les sorties CI.
- La protection doit s’appliquer aux administrateurs sans exiger une auto-approbation GitHub impossible.

## Task 1 — CI et règles versionnées

**Files:** `Makefile`, `.github/workflows/tests.yml`, `.github/pull_request_template.md`, `AGENTS.md`, `README.md`.
**Interfaces:** `make test-storage` exécute les tests existants `tests/Integration/GarageStorageTest.php` ; le check GitHub reste `ci`.

- [x] Vérifier le manque : pas de cible `test-storage`, pas d’AGENTS, pas de test Garage dans la CI.
- [x] Ajouter la cible Make, l’étape CI, concurrence et timeout, les règles agents et le modèle de PR.
- [x] Exécuter le lint du workflow, `make test-storage`, `make check` et `make assets` ; attendre succès.
- [x] Relire les fichiers suivis et les exclusions de secrets ; committer.

## Task 2 — Revue, PR et protection

- [ ] Faire relire les changements par un agent indépendant et traiter les défauts importants.
- [ ] Pousser la branche et créer une PR vers `main`, avec résultats et SHA revu.
- [ ] Attendre le check GitHub `ci`, corriger toute erreur et revérifier.
- [ ] Configurer la protection de `main` (PR, `ci` GitHub Actions, strict, admins, discussions résolues, aucun force push/suppression) et relire l’API.
- [ ] Vérifier le SHA, les discussions et l’état de fusion, puis fusionner en squash avec `--match-head-commit`.
- [ ] Vérifier la fusion et le run de `main`, synchroniser le checkout propre et donner les liens.
