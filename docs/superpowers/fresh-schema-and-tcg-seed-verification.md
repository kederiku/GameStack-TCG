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

## Nettoyage des anciennes images et validation globale

Implémentation : `e2129aee3e8be399255bb9e6d6951577c32b4d52`.
Revue indépendante : `fresh_cleanup_review3`, conformité et qualité approuvées.
Les formats d’import actuels restent disponibles ; seuls les anciens formats
stockés et leurs commandes de conversion sont retirés.

- Deux tests échouent avant le retrait de la compatibilité des fichiers stockés
  WebP/SVG ; les 81 tests ciblés passent ensuite, avec 680 assertions.
- `make check` : 393 tests PHP, 4 012 assertions et 19 tests frontend réussis ;
  Pint, PHPStan, lint, dictionnaires et TypeScript réussis.
- `make up` puis `make test-storage` : deux tests et 24 assertions sur Garage réel.
- `make assets` : compilation de production réussie sur les mêmes fichiers
  frontend et de build. L’avis de mise à jour npm et les indications de durée
  du bundler n’ont pas nécessité de changement de dépendances.
- Les seules commandes de localisation restantes sont `check`, `export` et
  `sync-languages`. La recherche dans le code exécutable et les guides actifs
  ne trouve aucun appel aux anciennes commandes.
- Diff complet vérifié pour les espaces et les fichiers non suivis ; aucun
  changement de `.env`, d’accès Garage ou de dépendance installée à publier.

Le reviewer relève une précision documentaire mineure possible sur la portée
de `make migrate` dans le paragraphe de mise à jour des images. L’avertissement
principal du README explique déjà la rupture d’historique. Ce point est transmis
à la revue complète.

## Contrôles restant à terminer

Installation et réexécution, inventaires avant/après purge, parcours navigateur,
revue complète, PR et CI. Aucun résultat n’est présumé réussi.
