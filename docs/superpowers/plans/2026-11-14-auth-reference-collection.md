# Auth Reference and Collection Copy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Adapter l’auth à la référence fournie et recentrer la landing sur les collections.

**Architecture:** Modifier le layout auth commun et les textes publics ; conserver
les contrats des formulaires et les liens actuels. Illustration locale originale.

**Tech Stack:** React/Inertia, Tailwind et shadcn/ui existants.

**Spec:** `docs/superpowers/specs/2026-11-14-auth-reference-collection-design.md`

## Global Constraints

- Branche `codex/immersive-landing`, checkout propre existant.
- Aucune nouvelle fonction d’authentification ou de gestion de collection.
- Palette publique, logo et thèmes clair/sombre/système conservés.
- Asset livré dans le projet ; pas d’image distante chargée depuis CardCodex.

## Review Focus

- Formulaires longs et clavier : toutes les actions restent accessibles à 320 px.
- Visuel masqué sur mobile : pas de coût de chargement inutile ni décalage du formulaire.
- Nouvelle composition : logo, titres et chemins de retour compréhensibles.
- Textes collection : aucune fonction non implémentée présentée comme disponible.
- Landing : titres, SEO, CTA, tuiles, état vide et pied de page tous cohérents.

### Task 1: Composition et contenus

**Files:** `resources/js/layouts/auth/auth-immersive-layout.tsx`,
`resources/js/pages/welcome.tsx`, `resources/js/layouts/public-layout.tsx`,
`resources/images/auth-collection-v1.webp`, documentation de l’asset et vérification.

**Interfaces:** `AuthLayoutProps` et `PublicGamePageProps` inchangés.

- [ ] Examiner la référence et la version actuelle au navigateur.
- [ ] Générer et inspecter l’illustration, puis copier/optimiser l’asset dans le projet.
- [ ] Recomposer l’auth : logo et formulaire à gauche, visuel à droite, mobile sans visuel.
- [ ] Réécrire tous les contenus de landing autour des collections et de leur état de préparation.
- [ ] Vérifier desktop/320 px, clair/sombre, formulaire long et OTP au navigateur.
- [ ] Exécuter `composer ci:check` et `npm run build` dans Docker, puis `git diff --check`.
- [ ] Commit, revue indépendante et correction des défauts importants éventuels.
- [ ] Consigner les résultats et livrer la branche sans fusion/publication.

## Execution

Réalisation inline de la modification explicite, sur la branche existante.
Pas de nouvelle demande de validation d’une direction déjà précisée par l’utilisateur.
