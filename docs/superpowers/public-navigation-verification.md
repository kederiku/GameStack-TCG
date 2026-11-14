# Navigation publique — vérifications du 14 novembre 2026

## Livraison

Branche `codex/immersive-landing`, issue de `main` à `62e0c1c`.
Design validé : [Immersive](specs/2026-11-14-public-navigation-design.md).
Plan : [navigation publique](plans/2026-11-14-public-navigation.md).

La landing `/`, la topbar shadcn/ui et les pages `/{game.slug}` partagent les
jeux réels de Filament. Le logo GameStackr utilise Big Shoulders Display et
une mention TCG verticale. Les pages de jeu affichent l’état de catalogue à venir.

## Contrôles automatisés

Commandes exécutées dans le conteneur `app` :

```sh
docker compose exec -T app composer ci:check
docker compose exec -T app npm run build
git diff --check
```

- **110 tests réussis, 563 assertions**, contre 94 tests avant cette réalisation.
- Pint, PHPStan, formatage/lint frontend et TypeScript : succès.
- Compilation de production : succès ; police Big Shoulders Display incluse.
- Les nouveaux tests HTTP et de validation ont d’abord échoué sur le code
  initial, puis réussi après implémentation.

Les tests couvrent la liste triée, les payloads publics explicites, les images
optionnelles, la racine sans jeu, le binding par slug, les renommages et
suppression, les URL inconnues, les routes de compte/administration, la session
authentifiée et le drapeau d’inscription Fortify. La validation SaveGame et
Filament refuse les slugs réservés avant toute écriture d’image. Les préfixes
de routes configurés dynamiquement sont aussi couverts.

## Vérifications dans le navigateur

- Landing et page du jeu réel de développement `pokemon`, avec son SVG réel.
- Sélection au clavier, navigation vers Pokémon puis Yu-Gi-Oh!, précédent,
  suivant et rechargement : le jeu sélectionné correspond toujours à l’URL.
- Échap ferme le menu et rend le focus au sélecteur.
- Liste vide : message explicite et sélecteur désactivé.
- Jeu sans image et URL d’image cassée : monogramme de secours.
- Nom de 255 caractères sans espaces : contenu lisible et menu utilisable.
- À 320 px : pas de débordement horizontal sur la landing, la page de jeu
  ou le menu ; les liens de compte sont accessibles dans le menu mobile.
- Thèmes clair et sombre : contenu et menus portalisés utilisent la palette
  publique. Le choix système existant est conservé.
- Aucune erreur JavaScript observée sur la landing de développement.

Un serveur QA temporaire sur `localhost:8001` a utilisé exclusivement
`gamestackr_testing`, avec trois fixtures temporaires. Un cadre HTML de 320 px
a permis un contrôle stable des petites largeurs et du choix de thème ;
l’outil de redimensionnement du navigateur s’appliquait de façon différée.
La landing réelle a également été observée dans une fenêtre de 320 px.
Le serveur, le cadre HTML et les fixtures ont été retirés après les essais.
Aucune donnée métier de la base de développement n’a été modifiée.
L’audit en lecture seule de cette base n’a trouvé aucun slug réservé existant.

Le premier contrôle visuel a révélé que les alias Tailwind de couleurs se
résolvaient à la racine et ignoraient la palette du layout. Les alias ont été
passés dans `@theme inline` ; le bleu des boutons et les couleurs des portails
ont été revérifiés dans les deux thèmes.

## Périmètre et décisions

- Checkout propre réutilisé sur la nouvelle branche demandée. Cela évite de
  dupliquer les services Docker ; le dossier reste partagé avec les autres éditeurs.
- Réalisation du plan autorisé sans seconde demande d’approbation. Le plan et
  le design restent versionnés pour examiner les choix.
- Présentation vérifiée au navigateur, par TypeScript et par compilation,
  sans ajouter de framework de tests frontend. Les régressions visuelles
  futures nécessitent toujours une vérification navigateur.
- Branche conservée localement, sans fusion ni publication, conformément à
  la demande de réaliser le travail dans une nouvelle branche.

Il n’y a ni catalogue de cartes fonctionnel, ni collection, ni prix. Les jeux
affichés dépendent des données Filament. Les préférences de thème et les écrans
de compte existants sont conservés. Les liens réservés existants sont masqués
et doivent être renommés dans Filament si l’on veut les rendre publics.

## Relecture

Relecture indépendante de `62e0c1c..f0c1f02` : aucun défaut critique ou important
signalé. Le seul point relevé concernait la langue du contenu public : les
textes français héritaient du `lang="en"` du document.

Ce point a été traité comme une correction d’accessibilité : `lang="fr"` est
maintenant déclaré sur le layout public et ses deux menus portalisés, sans
changer la langue des pages de compte. Vérification navigateur avant/après :
langue héritée `en` puis `fr` pour le contenu principal, le menu des jeux et
le menu mobile de compte. Aucun autre point n’a été laissé en attente.

Les éléments que la relecture a exclus ont été confirmés : catalogue,
collections, prix et scanner restent hors du périmètre validé ; README et
preuve de vérification sont livrés par la dernière tâche ; la traduction de
toute l’application n’est pas nécessaire à la navigation publique française.
