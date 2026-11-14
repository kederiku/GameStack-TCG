# Dashboard — Mon espace

Le 14 novembre 2026, l’utilisateur retient la recommandation d’une vue
personnelle, avec accès aux jeux réels et états explicites pour les collections
à venir. L’objectif est de prolonger le design GameStackr et de mutualiser les
composants pour conserver une application cohérente.

## Approche retenue

Faire évoluer le layout authentifié existant : navigation latérale compacte,
topbar, accueil personnalisé, panneau collection et grille de jeux. Cette vue
personnelle est retenue plutôt qu’une entrée centrée uniquement sur les jeux.
Une nouvelle structure indépendante du reste de l’app dupliquerait navigation
et styles ; le layout existant est donc conservé et harmonisé, y compris sur
les réglages du compte.

Inspiration : organisation de CardCodex (navigation latérale, panneaux et accès
aux univers), selon sa page indexée. Le site était en maintenance lors de la
consultation directe ; aucun dashboard privé n’a été consulté.

## Présentation et contenu

- Palette, logo, titres condensés et boutons de l’accueil déjà approuvé.
- Bonjour personnalisé, titre « Mon espace », accès rapide aux jeux.
- Un panneau « Ma collection » en préparation, sans nombres, prix ni courbes
  inventés ; les cartes possédées, séries et envies sont présentées comme à venir.
- Jeux administrés dans Filament : images réelles ou initiales, nom et lien réel.
- Une colonne secondaire avec accès au profil, à la sécurité et à l’apparence.
- Sidebar : Mon espace, explorer les jeux, réglages, retour à l’accueil,
  menu du compte et déconnexion. Retirer les liens promotionnels du starter.
- Thèmes clair/sombre/système existants ; mobile à 320 px, clavier et noms longs.

## Réutilisation

La palette publique devient la palette React commune, y compris pour les menus
portalisés. Le logo et les composants de jeux sortent du dossier `public` pour
refléter leur portée partagée. Extraire une tuile/grille de jeux utilisée aussi
sur l’accueil et un état vide utilisé par l’accueil, le dashboard et les jeux.
Réutiliser les primitives shadcn/ui, `Heading` et les menus utilisateur existants.
Pas de nouvelle bibliothèque ni de nouvelle couche de composants génériques
sans usage concret. Les réglages conservent leurs formulaires et comportements.

## Données et validation

Un contrôleur dashboard expose `games` via `PublicGames::all()`. Garder les
middlewares `auth` et `verified`. Aucun changement de schéma ou de données métier.
Tests HTTP : accès, utilisateur non vérifié, jeux triés, image optionnelle,
slugs réservés exclus, absence de jeux et payload public. Contrôles PHP,
TypeScript, lint/build, puis navigateur desktop/mobile, thèmes, navigation,
menu utilisateur et régression accueil/connexion/réglages.
