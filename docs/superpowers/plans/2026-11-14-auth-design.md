# Auth Design Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Appliquer le design Immersive validé aux sept pages d’authentification.

**Architecture:** Layout auth dédié réutilisant les composants de marque et
la palette publique. Formulaires Fortify/Inertia conservés, libellés français.

**Tech Stack:** Laravel/Fortify, React, Inertia, Tailwind, shadcn/ui existants.

**Spec:** `docs/superpowers/specs/2026-11-14-auth-design.md`

## Global Constraints

- Continuer sur `codex/immersive-landing`, checkout propre déjà utilisé.
- Aucun changement des règles d’authentification ni création de compte métier.
- Pas de dépendance ni framework frontend de tests supplémentaire.
- Les tests PostgreSQL utilisent exclusivement `gamestackr_testing`.
- Préserver les préférences clair/sombre/système et les pages de compte.

## Review Focus

- Formulaire long à 320 px : aucun champ ni bouton inaccessible ou débordant.
- Passkeys indisponibles : formulaire e-mail/mot de passe toujours utilisable.
- Code 2FA et récupération : titre, nom accessible et données soumis cohérents.
- Erreur ou statut long : lisible en clair/sombre, sans masquer le formulaire.
- Inscription désactivée : aucun lien de connexion vers une route indisponible.

### Task 1: Layout, formulaires et vérification

**Files:**

- Create `resources/js/layouts/auth/auth-immersive-layout.tsx` : structure commune.
- Modify `resources/js/layouts/auth-layout.tsx` : sélectionner ce layout.
- Modify `resources/js/pages/auth/*.tsx` : copie et présentation des sept écrans.
- Modify `resources/js/components/password-input.tsx` : labels accessibles optionnels.
- Modify `resources/css/app.css` : styles limités au formulaire auth.
- Modify `app/Providers/FortifyServiceProvider.php` et
  `tests/Feature/Auth/AuthenticationTest.php` : drapeau d’inscription.
- Document `docs/superpowers/auth-design-verification.md`.

**Interfaces:** `AuthLayoutProps` inchangé ; `Login` reçoit `canRegister: boolean`.
`PasswordInput` accepte `showLabel?: string` et `hideLabel?: string` avec les
valeurs anglaises existantes par défaut, pour préserver les écrans de compte.

- [ ] Observer la connexion initiale ; constater l’ancien logo et la palette.
- [ ] Tester que `auth/login` reçoit `canRegister` true/false selon Fortify ;
      constater l’échec avant de fournir la prop.
- [ ] Implémenter le layout : logo/raccourci accueil, panneau Immersive desktop,
      formulaire contenu dans une carte, langage français, focus et mobile.
- [ ] Mettre à jour les sept pages et leurs libellés ; garder les contrats de
      formulaire, proposer des labels au champ OTP et au code de récupération.
- [ ] Fournir le drapeau d’inscription et conditionner le lien de connexion.
- [ ] Vérifier desktop/320 px, clair/sombre, erreurs/statuts, retour accueil,
      liens entre formulaires, clavier et bascule des modes 2FA au navigateur.
- [ ] Exécuter `docker compose exec -T app composer ci:check`,
      `docker compose exec -T app npm run build` et `git diff --check` ; succès attendu.
- [ ] Commit de l’interface ; revue indépendante ciblée sur cette évolution.
- [ ] Traiter les défauts importants et consigner les preuves ; commit de
      documentation, branche conservée localement sans fusion/publication.

## Execution

Exécution inline du changement demandé sur le design déjà validé. Présentation
réversible vérifiée visuellement et par les analyses existantes, sans tests
qui recopient le CSS. Les nouveaux contrats serveur ont un test comportemental.
