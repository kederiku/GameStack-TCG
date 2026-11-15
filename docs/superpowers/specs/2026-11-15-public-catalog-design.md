# Catalogue public — direction A validée

L’utilisateur a validé la maquette A le 15 novembre 2026 et autorisé son
intégration aux vraies données. Le travail a d’abord été conservé localement.
L’utilisateur a ensuite demandé le renommage de la branche, le commit de
l’ensemble et le workflow de publication/fusion vers `main` selon `AGENTS.md`.

## Résultat

Toutes les pages de jeu affichent leurs catalogues et extensions publiés,
regroupés par série. Recherche, catalogue, langue d’édition, type et tri restent
partageables dans l’URL. Pagination de 36 extensions pour borner les réponses.
Une série se consulte par le filtre `series` du catalogue. Les extensions sans
série ont leur propre groupe. Les catalogues vides restent consultables.

Les fiches utilisent `/{jeu}/{catalogue}/{extension}` et `edition=<langue>`.
Une édition visible est choisie par défaut dans la langue par défaut du
catalogue, puis dans la première langue disponible ; un choix explicite reste
indépendant de la langue de l’interface. Un choix devenu indisponible produit
un message de repli. Les noms de présentation suivent FR/EN avec repli vers
la langue du catalogue, puis le nom de base. Aucun logo d’une autre édition
ne remplace un logo manquant. Les sorties affichent marché, canal, statut,
date exacte ou période annoncée. Les rapprochements ne fusionnent pas les
catalogues et n’affichent que des cibles publiées.

La visibilité héritée jeu/catalogue/série/extension/édition est appliquée dans
les requêtes publiques et les accès directs. Une extension publiée sans
édition visible reste une fiche informative, sans données d’édition masquées.
Aucune écriture de publication, aucun seeder automatique, aucune carte ou
statistique inventée.

## Interface et composants

- Reprendre PublicLayout/PublicHeader, GameSelector, GameImage, BrandLogo,
  EmptyState et les primitives shadcn, avec les tokens GameStackr existants.
- PublicHeader reçoit le choix FR/EN ; préférence persistante, liens de
  catalogue partageables avec `lang`. Traduire le parcours public (accueil,
  navigation, catalogue et fiches). Les pages compte/authentication existantes
  restent un parcours distinct ; ne pas refondre leur contenu dans ce chantier.
- Site React sombre uniquement, y compris les portails et le premier rendu.
  Supprimer les contrôles de thème et les liens de réglage devenus inutiles.
  L’administration Filament reste indépendante.
- En-tête A : titre à gauche ; logo, compteur réel, éditeur et période à
  droite, sans icônes de métadonnées. Date de fin si renseignée.
- Sélecteur de catalogue centré à largeur naturelle, sans libellé visible
  « Catalogue », avec nom accessible. Repli mobile sans débordement.
- Cartes d’extension horizontales, logo à gauche, nom et sorties à droite,
  langues disponibles, grille et groupes repliables comme la maquette A.
- Fiche : choix d’édition, métadonnées réelles, état partagé « cartes à venir ».
- Supprimer le branchement temporaire et les fichiers de maquette une fois
  le parcours réel vérifié.

## Vérifications

Tests HTTP PostgreSQL dédié : visibilité à chaque niveau, clés identiques
dans des catalogues différents, localisations et logos manquants, éditions
indépendantes de FR/EN, sorties multiples/annulées, filtres et pagination,
requêtes malformées, métadonnées facultatives et préférences de langue.
Puis `make check`, `make assets`, parcours navigateur desktop/mobile et
revue indépendante du diff complet. Ne pas modifier les données applicatives.
