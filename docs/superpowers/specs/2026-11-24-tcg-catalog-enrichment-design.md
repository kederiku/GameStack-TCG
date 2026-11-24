# Catalogue enrichi et noms anglais

Complément de périmètre demandé le 24 novembre 2026, avant fusion de la PR 9.
Les décisions fonctionnelles ci-dessous reprennent les précisions explicites
de l’utilisateur. L’architecture et les garanties de la
[conception initiale](2026-11-24-fresh-schema-and-tcg-seed-design.md) restent valables.

## Résultat attendu

- Environ dix extensions distinctes par TCG lorsque le jeu en possède autant.
  Les jeux récents gardent leur nombre réel de sets disponibles ; aucun produit
  fictif, tirage supplémentaire ou simple langue ne sert à remplir un quota.
- Conserver les distinctions de catalogues régionaux existantes. Un même titre
  dans deux catalogues réellement différents peut occuper deux lignes, mais ne
  devient pas deux nouveautés pour atteindre la cible.
- Inclure systématiquement l’édition française d’une extension lorsqu’elle est
  réellement imprimée, y compris pour les extensions déjà présentes. Ajouter
  les autres langues physiques attestées par des sources officielles.
- Ajouter un nom d’affichage anglais aux noms non latins des séries et extensions.
  Conserver les noms officiels natifs des éditions imprimées.
- En l’absence de nom français, l’interface française affiche l’anglais avant
  le nom d’origine. Une traduction d’affichage n’ajoute jamais une édition physique.

## Approche retenue

Étendre les manifestes PHP par jeu et leurs colonnes de traduction existantes.
C’est un enrichissement de la base de référence approuvée, sans nouvel importeur,
requête réseau pendant le seed, table d’alias ou changement de schéma. Un import
automatique externe ajouterait de nouvelles dépendances et politiques de
synchronisation sans répondre au besoin d’un seed déterministe et vérifié.

Les titres officiels anglais sont privilégiés. Quand aucun titre anglais officiel
n’est publié pour un produit natif, son rendu anglais est explicitement identifié
comme traduction éditoriale dans les notes de provenance. Le nom original reste
intact et une disponibilité anglophone n’est jamais déduite de ce libellé.

Pour `display_name`, l’ordre devient : langue demandée, anglais, repli courant du
modèle (langue du catalogue ou langue par défaut de l’application), nom de référence.
Les doublons de langue sont retirés comme actuellement. Les descriptions,
périodes annoncées et valeurs explicitement vides conservent leurs règles actuelles.
Cette décision porte sur les noms et n’introduit pas un réglage générique de
repli ni de nouvelle langue d’interface.

## Qualité des données

Les nouvelles lignes et les éditions ajoutées sont établies à partir des fiches
produits, listes de cartes et annonces des éditeurs. La langue de la page web
n’est pas une preuve de la langue des cartes. Les noms, codes, langues, dates,
marchés et canaux importés doivent être attestés au niveau pertinent du set.
Les sorties territoriales non prouvées restent absentes, même si la date du
lancement global est connue.

Les annonces sans impression confirmée et les campagnes sans produit disponible
ne compensent pas un catalogue récent encore court. Les limites propres à chaque
jeu sont documentées. Les libellés et codes de langues supplémentaires utilisent
le référentiel de contenu existant ; l’interface reste FR/EN.

## Exécution et vérification

La même branche et la même PR sont conservées. La fusion de la version initiale
est suspendue jusqu’à l’intégration et la revue de cet enrichissement.
La remise à zéro locale déjà validée n’est pas répétée : le seed réexécutable
enrichit la base actuelle.

Vérifier le repli anglais des noms avec la locale résolue correcte, la priorité
du français lorsqu’il existe, la conservation des autres replis, les éditions
françaises réellement disponibles et l’absence d’édition anglaise inventée pour
un nom traduit. Conserver les tests d’idempotence et de rollback du seed.
Rejouer les contrôles applicables, appliquer le seed, vérifier le parcours FR/EN,
nettoyer les seuls résidus de QA et obtenir une revue indépendante de la tête
finale avant la CI et la fusion prévues par `AGENTS.md`.
