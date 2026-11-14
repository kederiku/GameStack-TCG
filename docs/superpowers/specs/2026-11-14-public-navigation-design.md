# Landing page multi-TCG et navigation publique

## Intention et choix utilisateur

Créer l’identité publique de GameStackr TCG et permettre de choisir un jeu
depuis une topbar. `/` est une landing page multi-TCG ; `/{game.slug}` est
l’entrée de chaque jeu. Les composants restent en shadcn/ui.

Le 14 novembre 2026, l’utilisateur a choisi la direction **Immersive** parmi
les deux maquettes présentées. Le périmètre a ensuite été validé, avec demande
explicite de réalisation sur une nouvelle branche Git : `codex/immersive-landing`.

Références : [accueil CardCodex](https://cardcodex.com/) et
[page Pokémon](https://cardcodex.com/pokemon/?lang=en).

## Approches considérées

- **Immersive, choisie** : grand encart d’accueil, message central, éventail
  décoratif de cartes, puis entrées vers les jeux. Présente l’identité et la
  vocation multi-TCG ; les entrées occupent davantage d’espace vertical.
- **Jeux en premier** : introduction courte, puis grandes tuiles de jeux.
  Accès plus immédiat aux univers, avec une présentation de marque plus sobre.

## Présentation

- Topbar commune à l’accueil et aux pages de jeu : logo à gauche, sélecteur de
  jeu adjacent. Sur mobile, le sélecteur passe sur une seconde ligne.
- Logo textuel `GameStackr`, visuellement en capitales, avec `TCG` tourné de
  90° dans un petit cartouche à gauche. Police condensée et grasse
  **Big Shoulders Display**, comme dans la maquette. Il s’agit d’une
  approximation visuelle : la police exacte du logo CardCodex n’a pas été
  identifiée. Logo accessible sous le nom « GameStackr TCG ».
- Palette bleu nuit, surfaces ardoise, accents bleus et titres condensés.
  Conserver le réglage clair/sombre/système existant, avec une déclinaison claire.
- Accroche « Plusieurs univers. Une même passion. », courte présentation
  multi-TCG, bouton « Explorer les jeux » vers la section des jeux et bouton
  « Découvrir GameStackr » vers une courte présentation sur cette même page.
- Éventail décoratif de cartes génériques, réalisé en HTML/CSS. Les jeux
  illustratifs de la maquette ne deviennent pas des données de production.
- Section des jeux alimentée par les enregistrements Filament : image si
  disponible, nom et lien vers le jeu. Les images gardent leurs proportions ;
  une présentation textuelle remplace les images absentes.
- Choix de l’agent pour préserver l’accès aux fonctionnalités existantes :
  proposer « Connexion » et « Créer un compte » aux visiteurs, ou un accès
  à l’espace personnel aux utilisateurs connectés, dans la topbar.

## Navigation et données

- Le logo renvoie toujours à `/`. Sur `/`, le sélecteur affiche « Choisir un
  jeu ». Sur une page de jeu, il affiche le jeu courant et le marque dans le menu.
- Le sélecteur shadcn/ui contient tous les jeux enregistrés, triés par nom,
  avec leur image optionnelle. Un choix navigue avec Inertia vers le slug réel,
  par exemple `/pokemon` ou `/yugioh` ; aucun jeu n’est codé en dur.
- L’URL est la source du jeu sélectionné : accès direct, rechargement et
  historique précédent/suivant fonctionnent. Pas de mémorisation supplémentaire
  ni de redirection automatique de la landing page.
- Une première page de jeu affiche son nom, son image et un état vide explicite
  pour le catalogue à venir. Un slug inconnu renvoie une réponse 404.
- Sans jeu enregistré, la landing reste consultable et affiche un état vide ;
  le sélecteur est désactivé avec un libellé compréhensible.
- Les routes existantes restent prioritaires. Réserver les segments utilisés
  par l’application (administration, authentification, compte, santé et assets)
  pour éviter qu’un jeu ait un lien public menant à une autre fonctionnalité.
  Refuser les nouveaux slugs réservés avec un message de validation Filament ;
  signaler toute collision déjà présente sans renommer automatiquement les jeux.

## Périmètre et contraintes

Évolution ciblée du frontend public Laravel/React/Inertia existant. Réutiliser
les primitives shadcn/ui et le stockage local des images de jeux. Les layouts
du compte et l’administration Filament ne sont pas refondus.

Le catalogue de cartes, les extensions, les collections, les prix, le scanner
et les comptes de démonstration ne font pas partie de cette réalisation.
Ne pas afficher de statistiques inventées ou présenter ces fonctions comme
déjà disponibles. La maquette est un support de conception, pas le code à
réutiliser tel quel dans l’application.

Les détails de composants, de chargement des polices, de partage des données
et des routes restent à l’implémentation, dans la pile et les conventions
existantes. Aucun changement de modèle de données n’est nécessaire.

## Validation attendue

Tests des routes publiques, du jeu sélectionné, des images optionnelles, des
slugs inconnus/réservés et de l’absence de jeux. Vérifier les routes existantes.
Exécuter les contrôles PHP, TypeScript, frontend et compilation du projet.
Vérifier au navigateur le menu au clavier, la navigation entre deux jeux,
l’historique, les thèmes clair/sombre et le rendu à 320 px et sur ordinateur.
