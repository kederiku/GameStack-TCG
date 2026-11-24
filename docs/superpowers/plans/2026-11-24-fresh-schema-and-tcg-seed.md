# Fresh Schema and Real TCG Seed Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Recréer une base Laravel directement multilingue, supprimer les conversions historiques et remplacer les données existantes par un catalogue TCG réel et sourcé.

**Architecture:** Migrations initiales Laravel par domaine, seeders Eloquent utilisant les actions métier existantes, données PHP séparées par jeu et sources versionnées. Les tests utilisent les mécanismes Laravel ordinaires ou un schéma isolé lorsque la concurrence le nécessite. La purge locale est ponctuelle et ne fait pas partie de l’installation automatique.

**Tech Stack:** Laravel 13, PHP 8.4, Filament 5, PostgreSQL 18, Spatie Translatable, React/TypeScript, Docker/Make, Garage.

**Spec:** `docs/superpowers/specs/2026-11-24-fresh-schema-and-tcg-seed-design.md`, approuvée le 2026-11-24 par « go ».

**Execution:** Conserver le mode sous-agents demandé dans la conversation. Le lancement est autorisé ; poursuivre sans demander une nouvelle autorisation pour les mêmes opérations.

**Complément utilisateur avant fusion :** [catalogue enrichi et noms anglais](../specs/2026-11-24-tcg-catalog-enrichment-design.md). Les étapes locales de la tâche 4 sont terminées ; sa livraison GitHub attend désormais les tâches 5–7 ci-dessous. La PR 9 reste ouverte et ne doit pas être fusionnée sur son ancienne tête.

## Global Constraints

- Branche `codex/fresh-schema-and-tcg-seed` ; préserver les changements étrangers à cette tâche.
- PHP, Composer et Node exclusivement dans Docker via Makefile.
- Tests exclusivement sur `gamestackr_testing` ; garder intact le garde-fou de `tests/TestCase.php`.
- Aucun volume Docker supprimé ; préserver `.env`, clé applicative et configuration/accès Garage.
- Aucun Faker ni donnée factice dans les seeders applicatifs ; chaque attribut factuel doit avoir une source officielle vérifiée.
- Pas de nouvelle dépendance, d’import des anciennes données ou de dispositif générique de conversion.
- Garder les fonctionnalités multilingues, les replis éditoriaux et les uploads actuels.
- Réexécuter le seed ne duplique ni lignes ni médias ; les attributs fournis sont remis aux valeurs du manifeste, les autres traductions et fiches sont conservées.
- La purge inclut tous les anciens comptes, médias, données de test et archives locales identifiées dans la spec ; aucun compte n’est conservé à la livraison.
- Revue indépendante et CI `tests / ci` verte sur le SHA final avant fusion squash suivant `AGENTS.md`.

## Review Focus

1. Une installation neuve et un rollback complet respectent les clés étrangères ; aucune ancienne table ne réapparaît (tâche 1).
2. Une langue imprimée différente de la langue d’interface, ou un code de langue avec une casse historique, ne produit ni doublon ni édition inventée (tâche 2).
3. Une réexécution après modification d’un champ ou suppression d’un média restaure les seuls éléments prévus, sans multiplier les fichiers (tâche 2).
4. Un échec de validation ou de stockage au milieu du seed annule ses lignes et nettoie ses nouveaux objets (tâche 2).
5. Tests et nettoyage respectent leur destination : zéro écriture de test sur la base applicative, zéro purge de bucket ou base étrangers (tâches 1 et 4).

## File Structure

- `database/migrations/2026_11_24_000000_create_accounts_tables.php` : utilisateurs complets, tokens, sessions, passkeys.
- `database/migrations/2026_11_24_000100_create_cache_tables.php` : cache et verrous.
- `database/migrations/2026_11_24_000200_create_jobs_tables.php` : jobs, lots, échecs.
- `database/migrations/2026_11_24_000300_create_games_table.php` : jeu dans son schéma final JSONB.
- `database/migrations/2026_11_24_000400_create_catalog_tables.php` : catalogue final et contraintes, sans bindings ni conversions.
- `database/seeders/ContentLanguagesSeeder.php` : seules les langues d’interface configurées, utilisable aussi par les tests.
- `database/seeders/TcgCatalogSeeder.php` : références et fiches réelles, transaction et idempotence.
- `database/seeders/data/tcg.php` : références communes et liste explicite des fichiers de jeux.
- `database/seeders/data/tcg/*.php` : un fichier par jeu, valeurs métier, traductions, provenance, références par clés naturelles.
- `database/seeders/data/tcg-sources.php` : URL, consultation effective et faits étayés par source.
- `database/seeders/assets/tcg-catalog/` : les deux visuels officiels existants, provenance vérifiée.
- `tests/Concerns/UsesIsolatedSchema.php` : schéma PostgreSQL possédé pour tests de concurrence ou stockage, sans finalisation historique.
- `tests/Feature/Database/FreshSchemaTest.php` et `tests/Feature/Catalog/TcgCatalogSeederTest.php` : nouveaux contrats.
- README, `docs/localization.md`, Makefile et anciens rapports concernés : commandes actuelles et mention d’historique remplacé.

## Task 1: Schéma final et installation Laravel ordinaires

**Files:** les cinq nouvelles migrations ci-dessus, `ContentLanguagesSeeder.php`, `DatabaseSeeder.php`, `Makefile`, `tests/TestCase.php`, `tests/Concerns/UsesIsolatedSchema.php`, `tests/Feature/Database/FreshSchemaTest.php`, tests utilisant `ForwardOnly*` ou `UsesLocalizedSchema`, `app/Support/Localization/ContentLanguageUsage.php`.

**Remove:** anciennes migrations et support `LocalizedContentV1`, commandes `AuditLocalization`, `FinalizeLocalization`, `InstallLocalizedDatabase`, anciens traits, `tests/Fixtures/Localization/legacy-catalog.php`, `LegacyContentMigrationTest`, `LocalizationFinalizationTest`, ancien seeder/action/modèle de bindings et tests exclusivement dédiés à leur contrat.

**Interfaces:**

- Produit `ContentLanguagesSeeder::run(): void` : synchronise les locales actives via `localization:sync-languages`, sans compte ni catalogue.
- Produit `UsesIsolatedSchema::createIsolatedSchema(bool $registerCleanup = true): void` et `dropIsolatedSchema(): void` : protège `gamestackr_testing`, restaure la connexion et supprime uniquement son schéma.
- `DatabaseSeeder::run(): void` appelle pour l’instant uniquement le seeder de langues ; la tâche 2 y ajoute le catalogue réel.
- `make init` utilise `migrate --force` puis `localization:sync-languages`.

- [x] Écrire `FreshSchemaTest` sur un schéma possédé de `gamestackr_testing` : migrations seules sans données métier, présence de JSONB et des champs d’authentification complets, absence des quatre tables historiques, langues après synchronisation, rollback puis nouvelle migration.
- [x] Exécuter `make test ARGS='tests/Feature/Database/FreshSchemaTest.php'` et constater l’échec sur l’historique actuel.
- [x] Écrire les cinq migrations finales en reprenant toutes les contraintes actuelles utiles. Supprimer l’ancien historique et les commandes de conversion de localisation.
- [x] Retirer l’ancien seeder conservateur et ses bindings. Supprimer uniquement les cas de test de conversion/bindings devenus sans objet ; garder les tests du domaine et des traductions. Le manifeste de faits peut rester lisible comme référence jusqu’à son remplacement en tâche 2.
- [x] Remplacer les traits `ForwardOnly*` par `RefreshDatabase` / `DatabaseMigrations`, avec amorçage des langues dans la base de tests. Adapter les tests de concurrence/stockage à `UsesIsolatedSchema`. Ne pas altérer le contrôle de destination dans `TestCase::createApplication()`.
- [x] Vérifier les protections avec `TestDatabaseIsolationTest` et `FreshSchemaTest`, puis exécuter la suite PHP et Pint/PHPStan via les commandes Composer du projet.
- [x] Mettre à jour le plan et créer un commit limité à cette tâche après vérification du diff.

## Task 2: Catalogue réel, sourcé et réexécutable

**Files:** `TcgCatalogSeeder.php`, `DatabaseSeeder.php`, `database/seeders/data/tcg.php`, `database/seeders/data/tcg/*.php`, `database/seeders/data/tcg-sources.php`, README des visuels, `tests/Feature/Catalog/TcgCatalogSeederTest.php`, `Makefile`.

**Remove:** `database/seeders/data/tcg-catalog-examples.php`, `database/seeders/data/tcg-catalog-sources.php` lorsque leurs données vérifiées ont été reprises.

**Interfaces:**

- Consomme `ContentLanguagesSeeder::run()`, `SaveGame::handle(Game $game, array $data): Game`, `SaveCatalogRecord::handle(CatalogRecord $record, array $data): CatalogRecord` et leurs transactions d’images.
- Produit `TcgCatalogSeeder::run(): void`, appelable directement ou par `DatabaseSeeder` ; aucune requête réseau pendant son exécution.
- Chaque fichier de jeu retourne `game` (attributs), `sources` (IDs de provenance par fait) et `catalogs`. Chaque catalogue porte `attributes`, `default_language`, `series`, `expansions`. Une extension porte `attributes`, `series` (clé ou null), `editions`. Une édition porte `attributes`, `language`, `asset` (nom de fixture ou null), `releases`. Une sortie porte `attributes` et `market` (clé). Les entités factuelles portent leurs `sources`. Les séries peuvent porter `images` indexées par code de langue. Aucun ancien type `*-localization`.
- `tcg.php` retourne les références communes `languages`, `markets` et la liste `games` de chemins de fichiers ; `tcg-sources.php` retourne les sources indexées par ID avec `url`, `consulted_on`, `note`.
- Produit `make seed` → `db:seed`. Cette commande n’efface aucune base et rétablit les attributs explicitement fournis.

- [x] Vérifier les sources officielles de tous les faits retenus pour les douze jeux visés. Préférer un champ absent ou une fiche écartée à une supposition ; documenter les écarts. Retirer les relations dont les sources prouvent seulement l’existence des produits, sans étayer la relation.
- [x] Écrire les tests avant le seeder : zéro utilisateur ; catalogue visible ; `one-piece` / `international` / `romance-dawn`, édition anglaise `OP01`, sortie `2022-12-02` ; Pokémon avec distinction entre texte FR et édition JA ; correction du slug `naruto-mythos`.
- [x] Ajouter les cas de réexécution : mêmes identifiants et nombre de fichiers ; champ fourni remis à sa valeur ; traduction supplémentaire et fiche étrangère conservées ; code de langue `EN` préexistant réutilisé ; média absent recréé une seule fois.
- [x] Ajouter un cas d’échec après création de média : rollback des nouvelles lignes et fichiers, conservation des objets antérieurs. Vérifier par un vrai point d’échec dans le service/action, pas seulement en reproduisant une boucle du seeder dans le test.
- [x] Exécuter `make test ARGS='tests/Feature/Catalog/TcgCatalogSeederTest.php'` et constater l’échec avant implémentation.
- [x] Écrire le seeder et les tableaux. Sérialiser les exécutions par verrou transactionnel PostgreSQL. Résoudre les parents par clés naturelles, appliquer les valeurs fournies avec les actions existantes, comparer les médias avant de les recopier. Pas de registre de bindings ni de mode migration.
- [x] Exécuter les tests du seed, du catalogue, des traductions et des images concernés ; vérifier la provenance de chaque champ et l’absence d’appel aux factories dans les seeders.
- [x] Mettre à jour le plan et créer un commit après vérification du diff et des sources effectivement consultées.

## Task 3: Retrait des anciennes conversions d’images et documentation

**Files:** supprimer `app/Console/Commands/ConvertGameImagesToAvif.php`, `CopyGameImagesToS3.php`, `tests/Feature/ConvertGameImagesTest.php`, `CopyGameImagesTest.php` ; modifier `app/Services/GameImages.php`, `app/Services/AvifImages.php`, `tests/Feature/GameImageStorageTest.php`, `tests/Feature/Auth/AuthGamesTest.php`, `tests/Feature/PublicGameTest.php`, `tests/Feature/DashboardTest.php`, `tests/Integration/GarageStorageTest.php`, `README.md`, `docs/localization.md` et rapports historiques concernés.

**Interfaces:** conserver les uploads JPEG/PNG/WebP/SVG et leur normalisation AVIF, ainsi que le partage de l’encodeur introduit en tâche 2. Les chemins gérés de jeux et leur renommage ne conservent plus les anciens formats stockés WebP/SVG ; `AvifImages::write()` écrit uniquement de l’AVIF, sans paramètre d’extension historique. Les seules commandes de localisation utiles restent check/export/sync-languages ; aucun alias vers une conversion supprimée.

- [x] Retirer les commandes historiques et leur cas d’intégration dédié ; conserver les tests de cycle de vie réel Garage et les tests d’upload AVIF/SVG.
- [x] Retirer la compatibilité des anciens formats stockés dans `GameImages` et `AvifImages`, supprimer le cas historique de `GameImageStorageTest` et actualiser les chemins de fixtures publiques. Préserver les tests de téléversement SVG dans `GameSvgTest`.
- [x] Réécrire les procédures d’installation et de seed : `make init` non destructif, langues actives, `make seed` explicite avec données réelles et comportement de réexécution, création d’admin interactive.
- [x] Documenter la rupture d’historique et la remise à zéro explicite requise sur les autres installations. Retirer les anciennes procédures de conversion du guide actif ; annoter les rapports historiques sans les présenter comme instructions actuelles.
- [x] Exécuter les tests concernés d’images et de stockage ; rechercher les références exécutables aux commandes/tables supprimées. Formater les fichiers touchés avec les outils du projet.
- [x] Mettre à jour le plan et créer un commit après `git diff --check`.

## Task 4: Vérification globale, purge locale, revue et livraison

**Files:** créer `docs/superpowers/fresh-schema-and-tcg-seed-verification.md` ; compléter le plan. Les scripts opérationnels ponctuels restent hors Git, sans secrets dans les sorties.

**Interfaces:** utilise les commandes validées des tâches 1–3. La purge vise seulement les deux bases et le bucket locaux vérifiés, jamais une destination fournie implicitement sans contrôle.

- [x] Examiner le diff complet et les nouveaux fichiers. Exécuter `make check`, `make assets`, `make up`, `make test-storage`. Corriger toute régression, sans retirer un garde-fou.
- [x] Vérifier installation neuve et rollback/réinstallation sur `gamestackr_testing` ; contrôler séparément que l’installation ordinaire réexécutée ne supprime pas des données existantes.
- [x] Réaliser une revue indépendante de la branche. Corriger les défauts importants et faire relire les deltas avant de poursuivre.
- [x] Inventorier les destinations locales et les données à effacer sans exposer leurs contenus privés. Vérifier hôtes, noms des deux bases et bucket Garage ; arrêter les écritures et workers avant toute purge.
- [x] Purger les anciennes données PostgreSQL, objets d’images Garage et copies locales, résidus de tests, cache/sessions/journaux, messages Mailpit et les deux répertoires d’archives explicitement approuvés. Préserver `.env`, accès Garage et volumes.
- [x] Recréer le schéma applicatif et lancer le seed. Vérifier l’absence d’anciens comptes, tables et fichiers ; vérifier une deuxième exécution sans doublon. En cas d’échec, réparer puis relancer avant réouverture.
- [x] Vérifier dans le navigateur les catalogues FR/EN, une fiche et ses langues d’édition, puis l’administration avec un compte de QA temporaire. Supprimer ce compte, ses messages et données, et nettoyer les résidus de test avant livraison.
- [x] Vérifier le résultat final : catalogue réel visible, aucun compte conservé, seuls médias du seed, base de tests sans données métier résiduelles, services disponibles. Consigner les vérifications réellement faites et leurs limites.
- [ ] Actualiser `origin/main`, intégrer si nécessaire et revérifier. Publier la branche et créer la PR avec le SHA revu, la conclusion indépendante et les contrôles. Attacher la PR au chat.
- [ ] Attendre `tests / ci` réussi sur la tête finale, contrôler état/discussions/base/SHA, puis fusionner avec `gh pr merge <numero> --squash --match-head-commit <sha-revu>`.
- [ ] Vérifier `MERGED`, attendre la CI du commit `main`, synchroniser le checkout propre et donner les liens et le résultat à l’utilisateur.

Les trois étapes GitHub ci-dessus sont suivies dans la description de PR et les
checks distants après ce rapport de validation locale, sans ajouter des commits
documentaires circulaires pour attester leur propre fusion.

## Task 5: Repli anglais des noms sans traduction française

**Files:** `app/Support/Localization/TranslatedContent.php`, `app/Support/Catalog/CatalogPresentation.php`, `tests/Feature/Localization/TranslatableContentTest.php`, `tests/Feature/Localization/TranslatedCatalogTest.php`, tests de tri correspondants, paragraphes de repli dans `README.md` et `docs/localization.md`.

**Interfaces:** conserver `TranslatedContent::field(Model $record, string $attribute, string $locale): array` et `resolve(array $translations, array $locales, ?string $reference = null): array`. Le résultat garde `value`, `locale`, `fallback`. Pour `display_name` seulement, les candidats sont langue demandée → `en` → repli existant du modèle → référence. Les autres attributs et le traitement de `null` explicite restent inchangés. La tâche 6 fournira les noms anglais dans les manifestes ; cette tâche ne crée aucune édition ni donnée de seed.

- [x] Ajouter un test avec catalogue de langue japonaise : un nom FR manque mais EN et JA existent, la réponse française utilise EN avec `locale=en` et `fallback=true`. Un vrai nom FR gagne ; sans EN le nom JA reste le repli. Vérifier aussi un modèle utilisant le repli applicatif, sans persister la traduction de repli.
- [x] Vérifier par un test de champ descriptif que l’ajout d’EN ne change pas sa politique de repli et que le `null` français explicite reste respecté. Couvrir les métadonnées publiques de nom/langue/repli dans une réponse Inertia.
- [x] Exécuter les tests ciblés et constater le RED attendu avant modification.
- [x] Modifier la sélection des langues de noms et aligner son équivalent SQL `CatalogPresentation::nameForQuery` pour conserver un tri conforme au nom affiché, avec les mêmes règles de `null`. Vérifier cette parité puis actualiser les deux guides actifs.
- [x] Exécuter les tests de contenu, catalogue traduit et seed ; Pint/PHPStan et le format des documents concernés. Commit limité à cette tâche après auto-revue ; revue indépendante.

## Task 6: Étendre les sets réels et leurs langues imprimées

**Files:** `database/seeders/data/tcg.php`, `database/seeders/data/tcg/*.php`, `database/seeders/data/tcg-sources.php`, `tests/Feature/Catalog/TcgCatalogSeederTest.php`, `README.md`. Le seeder et ses services restent inchangés sauf nécessité démontrée.

**Interfaces:** mêmes tableaux de manifestes et identités naturelles que la tâche 2. Les nouveaux codes de langues de contenu sont ajoutés aux références communes avec leurs sources. Les noms anglais utilisent `display_name.en` ; les noms originaux et les éditions physiques restent dans leurs champs actuels. Les rapports de sources `enrichment-sources-pokemon-bandai.md`, `enrichment-sources-western.md` et `enrichment-sources-new-games.md` du workspace SDD fixent les faits vérifiés à intégrer. Les limites de disponibilité recensées font autorité sur le nombre approximatif demandé ; aucun quota artificiel.

- [ ] Terminer la vérification officielle de toutes les additions et des éditions FR manquantes dans les sets existants. Séparer les noms anglais officiels des rendus éditoriaux de noms non latins dans les notes de source.
- [ ] Écrire les assertions de contrat sur des ajouts attestés dans plusieurs jeux, leurs impressions FR et autres langues, et les noms anglais des séries/extensions non latines. Adapter le test ancien qui attendait un repli japonais en interface EN pour vérifier le nouveau libellé anglais et l’unique impression JA du produit natif.
- [ ] Constater le RED des nouvelles assertions avant enrichissement.
- [ ] Ajouter les valeurs vérifiées dans les manifestes par jeu, sans remplacer les identités existantes, sans images nouvelles non demandées, sans relations ou annonces non vérifiées. Garder les champs facultatifs absents lorsqu’aucune preuve ne les étaye.
- [ ] Vérifier la couverture de provenance, l’existence des langues référencées, les versions FR attendues, la stabilité des identifiants/fichiers à deux exécutions et la conservation des traductions hors manifeste. Actualiser les chiffres et les limites du README.
- [ ] Exécuter les tests du seed et de la localisation concernés, Pint/PHPStan, puis auto-revue et commit. Revue indépendante des données et des contrats.

## Task 7: Appliquer et livrer le catalogue enrichi

**Files:** compléter `docs/superpowers/fresh-schema-and-tcg-seed-verification.md` et ce plan ; scripts de contrôles ponctuels hors Git.

**Interfaces:** utiliser `make seed` sur la base applicative actuelle après validations. Aucune nouvelle remise à zéro ni suppression du catalogue réel déjà créé. La réexécution rétablit les valeurs prévues et ajoute les nouveaux identifiants, conformément au contrat approuvé.

- [ ] Revue indépendante de la branche et de son nouveau delta, contrôles `make check`, `make assets` si nécessaires, `make up` puis `make test-storage`. Ne pas réutiliser la CI d’une ancienne tête pour fusionner la nouvelle.
- [ ] Appliquer le seed et vérifier les nouveaux comptes par jeu/langue, les anciennes identités conservées, la stabilité d’une seconde exécution et les quatre médias inchangés.
- [ ] Contrôle navigateur FR/EN d’un nom non latin disposant maintenant du repli anglais et d’une extension avec une vraie édition française ; vérifier les titres natifs et langues imprimées distincts.
- [ ] Nettoyer les seules données temporaires de QA et de tests ; confirmer zéro compte conservé et services opérationnels. Actualiser le rapport et consigner les limites de disponibilité réelles.
- [ ] Faire relire le SHA final, actualiser la PR 9 et reprendre les étapes de publication/CI/fusion de la tâche 4.

## Self-review

Couverture vérifiée : schéma direct et contraintes (1), valeurs réelles et sources
(2), absence de faux comptes (1–2), conversions historiques retirées (1 et 3),
tests conservés et isolés (1–4), purge totale locale et environnement préservé (4),
documentation, revue indépendante et CI exacte (3–4). Les cinq points de revue ont
chacun des tests ou inventaires dédiés dans la tâche qui les possède. Aucune
nouvelle fonctionnalité de cartes individuelles ou import automatique n’est incluse.
