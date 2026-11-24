# Schéma neuf et catalogue TCG réel — vérifications

Date : 2026-11-24. Branche : `codex/fresh-schema-and-tcg-seed`.
Statut : implémentation et vérifications en cours. La remise à zéro locale et
la livraison ne sont pas encore effectuées.

## Périmètre autorisé

La [conception approuvée](specs/2026-11-24-fresh-schema-and-tcg-seed-design.md)
et le [plan](plans/2026-11-24-fresh-schema-and-tcg-seed.md) prévoient des migrations
Laravel directement multilingues, un seed de TCG existants aux valeurs sourcées,
le retrait des conversions historiques et une remise à zéro locale incluant
comptes, images, données de test et archives de migration désignées. Aucun compte
de remplacement n’est créé par le seed.

## Schéma initial

Implémentation : `cb3048aadae176d9fc19391fc5ec96969d03ea86`.
Revue indépendante : agent `fresh_schema_review1`, conformité et qualité approuvées.
Un point mineur sur l’ordre des assertions du seeder de langues est transmis à
la tâche de seed pour renforcer la preuve de son fonctionnement autonome.

- Référence avant modification : 448 tests PHP, 5 648 assertions.
- Nouveau schéma : échec attendu de trois tests sur l’ancien historique, puis
  trois tests réussis, 49 assertions, sur les migrations réécrites.
- Vérification ciblée complémentaire : 65 tests, 298 assertions.
- `make composer ARGS='test'` : Pint, PHPStan et 400 tests PHP réussis,
  3 982 assertions.
- `make test-storage` : trois tests, 30 assertions, sur Garage réel.

Ces contrôles portent exclusivement sur `gamestackr_testing`. Le contrôle de
destination de `Tests\TestCase::createApplication()` est conservé.

## Seed réel

Implémentation : `1eb15e21867f8a0861d0cc31cce4e55c45543d19`.
Revue indépendante par `real_tcg_seed_review2`, puis correction des noms localisés
et des sources au commit `12a814e295ecdc7e1cc8f33e1d7cf8ddaf570866`.
Relecture ciblée par `real_tcg_seed_fix_review` : tous les constats corrigés,
aucune nouvelle régression relevée. Les huit tests du seed passent avec
107 assertions après correction ; les sources utilisées sont au nombre de 69.

- Sept nouveaux tests échouaient avant l’implémentation ; les tests du seed et
  du schéma passent ensuite ensemble : dix tests, 121 assertions.
- `make check` : 407 tests PHP, 4 054 assertions, 19 tests frontend ; Pint,
  PHPStan, lint, dictionnaires et TypeScript réussis.
- `make up` puis `make test-storage` : trois tests, 30 assertions.
- Les tests couvrent les clés naturelles, les valeurs réellement publiées,
  la conservation des traductions étrangères au manifeste, la restauration des
  médias et le rollback des lignes et nouveaux fichiers.
- La vérification du seeder de langues part désormais d’une table vide, avant
  l’appel de synchronisation CLI ; elle répond au point mineur de la tâche 1.

Le manifeste contient 12 jeux, 19 catalogues, sept séries, 29 extensions,
35 éditions, 30 sorties, six langues et neuf marchés. Les sources attestent
les attributs importés ; les années, éditeurs et relations facultatifs non
retenus restent absents. Les images proviennent des deux fixtures officielles
dont la provenance et les empreintes sont conservées dans leur README.

## Contrôles restant à terminer

Retrait des conversions d’images, validation globale,
installation et réexécution, inventaires avant/après purge, parcours navigateur,
revue complète, PR et CI. Aucun résultat n’est présumé réussi.
