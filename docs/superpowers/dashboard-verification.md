# Dashboard — vérifications du 14 novembre 2026

## Résultat

Le dashboard `Mon espace` remplace les placeholders du starter. Il reprend la
charte GameStackr : navigation latérale, accueil personnalisé, illustration
partagée, panneau collection en préparation, jeux réels et raccourcis compte.
La palette commune s’applique aussi aux réglages et aux menus portalisés.

Design : [Mon espace](specs/2026-11-14-dashboard-design.md).
Plan : [réalisation](plans/2026-11-14-dashboard.md).
Travail local sur `codex/immersive-landing`, base `4600681`.

## Composants et absence de duplication

- `BrandLogo` : accueil, authentification, sidebar et alias `AppLogo` du layout alternatif.
- `CardFan` : accueil et dashboard, avec une adaptation compacte par classe.
- `GameGrid` / `GameCard` : mêmes tuiles sur l’accueil et le dashboard.
- `GameImage` / `GameSelector` : déplacés dans `components/games`, imports mis à jour.
- `EmptyState` : absence de jeux, catalogue à venir et collection en préparation.
- `AppearanceTabs` : réglages et topbar, variante compacte sans second contrôleur.
- `NavMain`, `NavUser`, `UserMenuContent` et primitives shadcn existants conservés.
- Une seule définition des couleurs de marque dans les tokens racine React ;
  suppression des copies locales `public-theme`.

Aucune dépendance ajoutée. Les jeux sont fournis par `PublicGames::all()` ; aucun
modèle, catalogue, prix ou compteur de collection fictif n’a été ajouté.

## Contrôles automatisés

```sh
docker compose exec -T app composer ci:check
docker compose exec -T app npm run build
git diff --check
```

- **119 tests réussis, 757 assertions** (baseline : 116 tests, 718 assertions).
- Nouveaux tests : utilisateur non vérifié, jeux triés, image optionnelle,
  slugs réservés exclus, payload public, absence de jeux.
- Cycle rouge/vert observé : deux tests échouaient sur `games` absent avant le
  contrôleur, puis les cinq tests dashboard passent.
- Formatage/lint frontend, TypeScript, Pint et PHPStan : succès.
- Compilation de production : succès. Message informatif existant du plugin
  de polices sur la dépendance optionnelle `fontaine` ; aucune erreur de build.
- Le premier passage Pint a signalé une indentation de chaînage dans le nouveau
  test ; corrigée, puis contrôle complet relancé avec succès.

## Navigateur

- Dashboard en clair et sombre, modes partagés avec les réglages.
- Largeurs 320, 1024 et 1440 px ; aucun débordement horizontal à 320 px,
  y compris avec des noms de compte et de jeu de près de 255 caractères.
- À 1024 px, l’illustration décorative se rapprochait trop du texte : elle
  apparaît désormais à partir de 1280 px. Correction recontrôlée après build.
- Sidebar développée/repliée sur ordinateur, logo compact et accès utilisateur.
- Tiroir mobile : titre accessible, bouton Fermer, navigation vers le profil
  et fermeture après choix d’un lien.
- Menu compte ouvert au clavier ; Échap ferme le menu et rend le focus au bouton.
- Absence de jeux : message explicite. Images absentes et image cassée : initiales.
- Navigation dashboard → Pokémon → accueil ; sélecteur de jeux et son thème.
- Accueil : grille partagée et contenu conservés. Connexion : formulaire rendu.
- Profil et apparence : navigation et formulaires conservés. Les tests HTTP
  existants couvrent aussi sécurité, déconnexion, 2FA et mises à jour du compte.
- Aucun avertissement ni erreur JavaScript observé pendant ces contrôles.

La prévisualisation locale sur le port 8001 utilisait exclusivement
`gamestackr_testing`, avec fixtures dans une transaction annulée après chaque
requête et compte construit en mémoire. Les requêtes de modification étaient
refusées. Les captures utilisent donc des données de test, et aucune donnée
métier de développement n’a été modifiée. Serveur et scripts temporaires retirés.

## Relecture et périmètre

Relecture indépendante de l’ensemble du diff et des nouveaux fichiers : aucun
défaut critique, important ou mineur signalé. Le contrôle visuel a été réalisé
séparément par l’agent principal. Les collections/prix/catalogues et la traduction
complète des formulaires de réglages restent hors périmètre ; leurs fonctions
existantes sont conservées. Les réglages internes gardent `lang="en"` et la
navigation française utilise `lang="fr"`.

Décisions d’exécution : checkout existant conservé, recommandation acceptée
réalisée sans nouvelle demande de validation, changements locaux sans fusion
ni publication. Les tests visuels sont manuels ; les régressions futures de
présentation nécessitent une nouvelle vérification au navigateur.
