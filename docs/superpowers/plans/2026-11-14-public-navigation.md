# Public Navigation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Livrer la landing Immersive et la navigation publique multi-TCG validées.

**Architecture:** Un contrôleur public fournit les jeux et le jeu courant aux
pages Inertia. Un layout public et ses composants shadcn/ui partagent la topbar.
Les routes et la validation Filament utilisent la même liste de slugs réservés.

**Tech Stack:** Laravel 13, Inertia 3, React 19, TypeScript, Tailwind 4, shadcn/ui.

**Spec:** `docs/superpowers/specs/2026-11-14-public-navigation-design.md`

## Global Constraints

- Travailler sur `codex/immersive-landing`, dans le checkout propre existant.
- Routes `/{game.slug}`, données Filament réelles ; aucun jeu de démonstration ajouté.
- Logo Big Shoulders Display et TCG vertical, direction Immersive, clair/sombre/système.
- Composants shadcn/ui existants ; aucun catalogue, prix ou collection implémenté.
- Les tests utilisent exclusivement PostgreSQL `gamestackr_testing`.

## Review Focus

- Slug réservé déjà présent : jamais de lien public vers une page de compte/admin.
- Noms longs et images absentes/cassées : pas de débordement à 320 px.
- Menu rendu dans un portail : palette publique correcte en clair et en sombre.
- Navigation entre jeux puis retour navigateur : le sélecteur suit l’URL.
- Visiteur ou compte connecté : accès au compte conservé et aucun nom privé exposé.

## File Structure

- `app/Http/Controllers/PublicGameController.php` : landing, jeu et payload public.
- `app/Support/GameSlug.php` : segments réservés et contrainte de route.
- `app/Actions/Games/SaveGame.php`, `app/Filament/Resources/Games/Schemas/GameForm.php` : refus des slugs réservés.
- `routes/web.php` : accueil et route par slug, après les routes existantes.
- `resources/js/types/game.ts` : contrat des pages publiques.
- `resources/js/layouts/public-layout.tsx` : topbar, contenu et pied de page publics.
- `resources/js/components/public/{brand-logo,game-image,game-selector,public-header,card-fan}.tsx` : composants ciblés.
- `resources/js/pages/welcome.tsx`, `resources/js/pages/games/show.tsx` : landing et état vide du jeu.
- `resources/css/app.css`, `vite.config.ts`, `resources/js/app.tsx` : styles, police et résolution du layout public.
- `tests/Feature/PublicGameTest.php`, `tests/Feature/ReservedGameSlugTest.php`, `tests/Feature/Filament/GameResourceTest.php` : contrats HTTP et validation.

### Task 1: Routes publiques et slugs

**Interfaces:**
- Produit `PublicGame`: `{id: number, name: string, slug: string, imageUrl: string | null, url: string}`.
- Props : `{games: PublicGame[], currentGame: PublicGame | null, canRegister: boolean}`.
- `PublicGameController::index(): Response`, `show(Game $game): Response`.
- Route nommée `games.show`, binding explicite `{game:slug}`.
- `GameSlug::RESERVED` et `GameSlug::routePattern(): string` partagés.

- [ ] Écrire les tests HTTP : liste triée sans timestamps, images WebP/SVG et null,
  racine vide, page d’un jeu, slug différent de l’id, slug modifié, slug inconnu,
  routes `/login`, `/register`, `/dashboard`, `/admin`, `/up` prioritaires,
  et exclusion d’un ancien slug réservé de la liste publique.
  Assertions centrales :
  ```php
  $this->get('/pokemon')->assertOk()->assertInertia(fn (Assert $page) => $page
      ->component('games/show', false)->where('currentGame.slug', 'pokemon'));
  $this->get('/absent')->assertNotFound();
  ```
- [ ] Écrire les tests SaveGame (création/modification/génération automatique)
  et Filament refusant un slug réservé sans changer le jeu ni stocker l’image.
- [ ] Exécuter `docker compose exec -T app php artisan test --compact --filter='PublicGameTest|ReservedGameSlugTest|GameResourceTest'` ; constater les échecs attendus.
- [ ] Implémenter le contrat avec un payload explicite, une URL relative générée
  par la route nommée et `Storage::disk('public')->url()` pour les images.
  Trier par nom puis id, exclure les slugs réservés. Vérifier les collisions
  présentes en base de développement sans modifier ces enregistrements.
- [ ] Exécuter les tests ciblés, Pint et PHPStan ; résultat attendu : succès.
- [ ] Commit `feat: add public game routes and reserve application slugs`.

### Task 2: Landing Immersive et topbar

**Interfaces:** consomme les props Task 1. `PublicLayout` lit ces props Inertia ;
`GameSelector({games, currentGame})` utilise les `url` du serveur via `Link`.

- [ ] Constater au navigateur que la landing et le sélecteur demandés sont absents.
- [ ] Créer le layout et le logo accessibles. Ajouter Big Shoulders Display
  800/900 par le pipeline de polices Vite existant. Isoler les couleurs dans
  `.public-theme`, également appliquée au contenu du portail du menu.
- [ ] Créer le sélecteur `DropdownMenu` avec liens, images optionnelles, coche
  du jeu actif, lien tous les jeux et état désactivé si liste vide. Le composant
  `GameImage` revient au nom/monogramme si l’image ne charge pas.
- [ ] Créer le header responsive : logo, sélecteur, connexion/inscription ou
  espace personnel. Lire le drapeau `canRegister` fourni par Fortify.
- [ ] Remplacer le starter welcome par la landing : accroche exacte du spec,
  éventail abstrait sans nom de jeu figé, liens ancrés, grille de jeux avec état
  vide, présentation brève de GameStackr et pied de page.
- [ ] Ajouter `games/show` avec image, nom et catalogue à venir. Brancher le
  layout public pour `welcome` et `games/*`, conserver les autres layouts.
- [ ] Exécuter `npm run check`, `npm run types:check`, `npm run build` dans Docker ;
  corriger uniquement les fichiers de cette réalisation. Résultat attendu : succès.
- [ ] Vérifier au navigateur la navigation au clavier, les liens et l’historique,
  les images réelles, la largeur 320 px et les thèmes clair/sombre. Vérifier un
  nom long et une image absente dans les fixtures de tests et la mise en page.
- [ ] Commit `feat: build immersive multi-TCG landing and game selector`.

### Task 3: Vérification finale et documentation

**Interfaces:** toutes les fonctionnalités des tâches 1 et 2.

- [ ] Exécuter `docker compose exec -T app composer ci:check`,
  `docker compose exec -T app npm run build`, `git diff --check` ; tous passent.
- [ ] Faire relire la branche par un agent indépendant selon
  `superpowers:requesting-code-review`, puis traiter les défauts importants.
- [ ] Documenter les vérifications et les limites dans
  `docs/superpowers/public-navigation-verification.md`, mettre à jour README.
- [ ] Commit de vérification, puis laisser la branche prête à examiner, sans
  fusion ni publication non demandée.

## Execution

L’utilisateur a autorisé la réalisation. Exécution inline dans cette session,
avec une revue indépendante finale ; pas de nouvelle validation du plan.
