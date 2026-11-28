# Pokémon Seed Implementation Plan

> **For agentic workers:** Use superpowers:executing-plans for inline implementation, followed by independent whole-branch review.

**Goal:** Enrichir le catalogue Pokémon avec les références documentaires fournies.

**Architecture:** Normaliser les données par catalogue et composer le manifeste
avec les entrées déjà vérifiées. Conserver le moteur transactionnel et les clés
métier du seeder. Référencer les documents locaux sans URL artificielle.

**Tech Stack:** PHP/Laravel, PostgreSQL, tests PHPUnit dans Docker via Make.

**Spec:** `docs/superpowers/specs/2026-11-28-pokemon-seed-design.md`

## Global Constraints

- Aucun changement de schéma, aucun import réseau pendant le seed.
- Tests exclusivement dans `gamestackr_testing`.
- Conserver les données, identifiants et médias existants.
- Ne pas extrapoler les langues, dates ou regroupements régionaux.

## Review Focus

- Titres absents en français : aucune édition française créée.
- Compilations KO/TW : aucune fausse extension japonaise correspondante.
- Mois seuls : aucune date artificielle au premier du mois.
- Collections McDonald's France/Québec : fiches distinctes quand le contenu diffère.
- Seed exécuté deux fois ou sur une base déjà remplie : identifiants conservés.

## Task 1: Manifeste documentaire Pokémon

Fichiers : `database/seeders/data/tcg/pokemon/`,
`database/seeders/PokemonCatalogData.php`, `database/seeders/data/tcg/pokemon.php`,
`database/seeders/data/tcg-sources.php`, `database/seeders/data/tcg.php`,
`database/seeders/TcgCatalogSeeder.php`, `tests/Feature/Catalog/TcgCatalogSeederTest.php`.

Interface : le manifeste conserve `game`, `catalogs`, `series`, `expansions`,
`editions` et `releases`, consommés par `TcgCatalogSeeder`.

- [x] Écrire les assertions de couverture et de précision dans les tests du seed.
- [x] Exécuter les nouveaux tests ciblés et constater les absences.
- [x] Normaliser les lignes utilisables, conserver les cas ambigus dans le registre des limites.
- [x] Composer les nouvelles entrées avec les références existantes prioritaires.
- [x] Faire passer les tests ciblés et ceux du seeder existant.

## Task 2: Vérification et livraison

Fichiers : `README.md`, `docs/superpowers/pokemon-seed-verification.md`.

- [x] Documenter la couverture effective, les sources et les limites.
- [x] Exécuter `make check`, le seed et le parcours public dans le navigateur.
- [ ] Vérifier le diff complet, committer et faire relire le SHA par un agent distinct.
- [ ] Publier la branche et la PR, attendre `tests / ci`, fusionner en squash après vérification.
- [ ] Vérifier le workflow du commit résultant sur `main`.

Les résultats de publication, le SHA revu et les contrôles distants sont suivis
dans la description de PR ; ce fichier décrit l’état avant publication.
