# Design Harmonization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Étendre le design GameStackr à tous les réglages et composants du site encore issus du starter.

**Architecture:** Conserver les layouts actifs et les tokens globaux. Harmoniser les primitives shadcn, construire une section de réglages réutilisable et remplacer la navigation interne par des onglets. Préserver les contrats Inertia/Fortify/WebAuthn.

**Tech Stack:** React 19, TypeScript, Inertia 3, Tailwind 4, shadcn/Radix, Laravel 13.

**Spec:** `docs/superpowers/specs/2026-11-14-design-harmonization-design.md`

## Global Constraints

- Périmètre site et espace membre ; Filament exclu.
- Réutiliser le logo, les polices, les tokens et les composants du projet.
- Pas de nouvelle dépendance, nouveau modèle de données ou nouvelle route métier requis. Pas de copie d’assets CardCodex.
- Aucun chiffre, indicateur de sécurité ou contenu utilisateur fictif.
- Respecter les modes clair/sombre/système, le clavier, les préférences de réduction des animations et les contenus longs.
- Garder actions, validation, erreurs, focus, confirmations et fonctionnalités existantes.
- Textes frontend internes en français ; erreurs externes conservées.
- Panneaux 16 px ; champs/actions principales 12 px, 44–48 px de hauteur.

## Review Focus

- Écran de 320 px et noms/adresses/clés longs : pas de débordement, actions accessibles.
- Fenêtre longue et écran bas : contenu défilant, fermeture et validation atteignables.
- Sécurité désactivée ou configuration 2FA inachevée : les commandes suivent les props existantes.
- Navigation au clavier : page active annoncée, labels explicites, Échap et focus restauré.
- Primitives partagées : connexion, menus de jeux et sidebar conservent leurs dimensions adaptées.

## Task 1: Primitives visuelles communes

**Files:** `resources/js/components/ui/{button,input,card,dialog,sheet,input-otp,dropdown-menu,select,badge,alert,tooltip,checkbox,sonner}.tsx`, `resources/css/app.css`, `resources/js/components/{password-input,alert-error,passkey-verify}.tsx`, `resources/js/app.tsx`.

**Interfaces:** Conserver toutes les signatures existantes. `Button` garde ses variantes et tailles ; `Input`, `DialogContent` et les menus continuent d’accepter `className` et les props Radix.

- [ ] Vérifier la baseline TypeScript et les tests HTTP Settings/Auth dans Docker.
- [ ] Ajuster les primitives : actions principales 44 px, inputs 48 px, cartes/dialogues 16 px ; garder les commandes `sm` et `icon` compactes et permettre les labels longs.
- [ ] Ajouter les surfaces de formulaire, titres condensés, menus arrondis, focus cohérent, dialogues bornés à la fenêtre, fermeture française ; OTP compact à 320 px.
- [ ] Consolider les overrides auth redondants et aligner les messages communs et la barre de progression.
- [ ] Vérifier TypeScript, puis inspecter connexion et fenêtre longue au clavier sur petit écran pendant la QA finale.
- [ ] Enregistrer le lot après validation du frontend.

## Task 2: Réglages et composants du compte

**Files:** créer `resources/js/components/settings-section.tsx` ; modifier `resources/js/layouts/settings/layout.tsx`, les trois `resources/js/pages/settings/*.tsx`, `resources/js/components/{appearance-tabs,delete-user,manage-two-factor,two-factor-setup-modal,two-factor-recovery-codes,manage-passkeys,passkey-item,passkey-register}.tsx`, `resources/js/hooks/use-two-factor-auth.ts`.

**Interfaces:** `SettingsSection({ id, title, description, icon, action?, children, destructive? })` rend une section sémantique avec titre h2 et panneau ; `icon: LucideIcon`, `action?: ReactNode`, `children: ReactNode`. `AppearanceTabs` conserve `compact` et ajoute `preview?: boolean`, toujours piloté par `useAppearance`.

- [ ] Créer la section réutilisable puis le cadre de réglages : titre h1 visible, largeur max-w-7xl et liens Profil/Sécurité/Apparence avec `aria-current`.
- [ ] Réorganiser Profil et suppression de compte en panneaux, traduire libellés et messages, conserver les noms/actions de formulaire et les attributs data-test.
- [ ] Réorganiser mot de passe, 2FA et passkeys en panneaux ; traduire fenêtres, étapes, codes, états vides, erreurs frontend ; protéger les layouts contre les noms longs.
- [ ] Ajouter les trois aperçus de thème aux réglages d’apparence avec boutons `aria-pressed`, sans dupliquer le stockage de thème.
- [ ] Vérifier TypeScript et tests HTTP existants ; inspecter états 2FA activé/inactif, passkeys vide/rempli/non pris en charge avec fixtures isolées en QA.
- [ ] Enregistrer le lot après validation du frontend.

## Task 3: Cohérence, vérification et revue

**Files:** supprimer uniquement les layouts et composants du starter dont l’absence d’import est confirmée ; documenter dans `docs/superpowers/design-harmonization-verification.md` et README.

**Interfaces:** Aucun changement aux routes, données, layouts actifs ou fonctionnalités.

- [ ] Auditer les imports des layouts alternatifs et enlever les doublons inutilisés ; inspecter les textes restants et les styles de composants utilisés.
- [ ] Lancer `docker compose exec -T app composer ci:check` puis `docker compose exec -T app npm run build`. Résultat attendu : zéro erreur ; consigner les avertissements existants.
- [ ] Vérifier au navigateur les réglages à 320, 768 et 1440 px, trois thèmes, clavier, menus, fenêtres, erreurs et textes longs. Utiliser des fixtures isolées pour les données sensibles, aucune mutation du compte réel.
- [ ] Vérifier accueil, jeu, dashboard et parcours auth ; corriger tout écart observé et relancer uniquement les contrôles affectés.
- [ ] Exécuter `git diff --check`, documenter les preuves/limites, enregistrer les modifications puis demander une revue indépendante de la branche contre la spec.
- [ ] Traiter les défauts importants, laisser la branche locale prête à consulter et fournir un bilan concis.

## Choix d’exécution

Exécution dans ce checkout sur la nouvelle branche déjà créée et validée,
sans nouveau worktree. Implémentation dans cette session puis une revue
indépendante finale. L’accord utilisateur pour lancer l’implémentation est acquis.
Les changements visuels et textuels utilisent les tests existants et la QA
navigateur ; aucun test recopiant les classes CSS ou la prose ne sera ajouté.
