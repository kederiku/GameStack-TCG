# Schéma neuf et catalogue TCG réel — vérifications

Date : 2026-11-24. Branche : `codex/fresh-schema-and-tcg-seed`.
Rapport arrêté avant publication de la PR. L’implémentation, la remise à zéro
locale et les contrôles ci-dessous sont terminés. Les résultats de publication,
de fusion et de CI sont suivis dans la PR associée à cette branche.

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

La précision documentaire sur la portée de `make migrate` a été ajoutée au commit
`738d78700e04a6af3d74c887477b9ae013a6c1e4` et approuvée dans la relecture finale.

## Revue complète

L’agent indépendant `fresh_final_review` a examiné les 124 fichiers du diff
depuis `5cdf438c1cdb82b30e6bc4650cf6c89213f8ad35` jusqu’à
`b31fd0d0b5356e437105b547a55faf9aa0ae5286`. Standards et spécification du code
versionné approuvés, aucun défaut critique.

Deux remarques importantes sur les scripts ponctuels de purge ont été corrigées
avant exécution : le filtre des schémas PostgreSQL utilise un souligné littéral,
et l’inventaire Garage parcourt les clés S3 brutes avec pagination, marqueurs de
dossier compris. L’inventaire réel a retrouvé les mêmes 22 objets sur cinq pages.
La relecture du correctif par le même reviewer indépendant a approuvé le SHA
`738d78700e04a6af3d74c887477b9ae013a6c1e4` et les scripts, sans nouvelle régression.
Ces scripts opérationnels restent hors du code livré.

## Remise à zéro et réinstallation observées

L’application était en maintenance et le worker arrêté. Les destinations réelles
ont été contrôlées : PostgreSQL du service `postgres`, bases `gamestackr` et
`gamestackr_testing`, seul schéma applicatif `public` dans chacune ; Garage du
service `garage`, bucket `gamestackr-images`. Aucun autre environnement visé.

- Suppression des 22 anciens objets Garage, puis vérification native du bucket
  vide avant le seed. Suppression des copies locales, des uploads temporaires,
  des fichiers de test, caches, vues compilées, sessions et journaux applicatifs.
- Suppression des deux archives locales explicitement autorisées, contenant
  respectivement 13 anciens originaux et 45 fichiers de migration. Aucun nouvel
  archivage des anciennes données n’a été créé.
- Recréation du seul schéma applicatif `public`, puis application des cinq
  migrations et chargement du seed. Aucune ancienne table ou fonction de
  conversion ne subsiste.
- Deuxième exécution du seed : mêmes identifiants, quatre mêmes clés S3 et mêmes
  empreintes SHA-256, sans utilisateur. Les quatre objets correspondent aux
  références d’images de série et d’édition.
- `make init` relancé avec succès sur ce catalogue. L’inventaire avant toute
  nouvelle exécution du seed conserve les identifiants et les médias à l’octet
  près ; le seed demeure ensuite stable.
- La réinstallation ayant reconstruit le conteneur PHP/Node, `make check` et
  `make test-storage` ont été rejoués dans ce conteneur : mêmes résultats,
  393 tests PHP / 4 012 assertions, 19 tests frontend et Garage 2 / 24.

Les tests HTTP de cette dernière passe ont tourné avec le serveur web et le
worker arrêtés, sans le marqueur de maintenance partagé qui empêcherait leurs
requêtes simulées. Les tests ont continué à utiliser exclusivement
`gamestackr_testing` avec le garde-fou existant.

## Parcours navigateur

Contrôle dans le navigateur à sa taille par défaut, avec le catalogue réellement
chargé dans l’application locale :

- Accueil français : 12 jeux visibles. Catalogue Pokémon : dix extensions
  réparties entre les cinq catalogues régionaux.
- Fiche Écarlate et Violet : visuel officiel français chargé, deux langues
  d’édition, sortie française du 31 mars 2023.
- Passage de l’interface FR à EN : même ressource et sélection `edition=fr`
  conservées. Le titre et l’interface changent, tandis que le nom imprimé et le
  visuel de l’édition restent français. Choisir l’édition EN affiche son nom
  officiel, son visuel anglais et sa sortie américaine du 31 mars 2023.
- Connexion anglaise et espace membre vérifiés avec un compte temporaire.
  Filament français affiche les comptes de catalogue attendus : 12 jeux,
  19 catalogues, 29 extensions et 35 éditions.
- Ouverture de la fiche d’extension dans Filament, passage à l’interface anglaise
  en conservant la fiche, puis lecture de la traduction anglaise préremplie.
  La table des éditions affiche les deux impressions FR et EN. Aucun changement
  de catalogue n’a été enregistré pendant la QA.
- Aucun message d’erreur dans la console JavaScript observée. L’onglet de QA a
  été fermé avant le nettoyage final.

## État final local

Le compte temporaire a été supprimé. L’inventaire final vérifie :

| Élément                                       | Résultat                                                                        |
| --------------------------------------------- | ------------------------------------------------------------------------------- |
| Utilisateurs, sessions, tokens, passkeys      | 0                                                                               |
| Jobs, lots, échecs et entrées de cache        | 0                                                                               |
| Jeux / catalogues / séries                    | 12 / 19 / 7                                                                     |
| Extensions / éditions / sorties               | 29 / 35 / 30                                                                    |
| Langues / marchés / relations                 | 6 / 9 / 0                                                                       |
| Garage                                        | 4 objets référencés, mêmes clés et SHA-256 que le seed initial ; aucun marqueur |
| Base de tests                                 | Schéma `public` vide, aucun autre schéma applicatif                             |
| Mailpit                                       | 0 message                                                                       |
| Anciennes tables et fonctions de conversion   | Absentes                                                                        |
| Archives autorisées et anciens uploads privés | Absents                                                                         |

Les fichiers de test recréés par la dernière suite, les vues compilées, le cache
PHPUnit, les journaux et le mot de passe temporaire ont été supprimés. `.env`,
la configuration privée Garage et les volumes sont conservés. L’application et
le worker ont été remis en service ; les services attendus sont démarrés et la
sonde `/up` répond HTTP 200. Les nouvelles visites peuvent normalement recréer
des sessions et des caches applicatifs.

## Limites conservées

Le catalogue est représentatif, sans cartes individuelles ni inventaire
exhaustif. Les attributs facultatifs non étayés restent absents. Le fichier source
du visuel français n’était plus accessible sans authentification lors de cette
passe ; sa provenance historique, sa date et son empreinte sont conservées, et
son rendu local a été vérifié. L’effacement est logique dans l’environnement
du projet, sans effacement forensique des supports ni suppression de sauvegardes
extérieures au périmètre autorisé.

La description de PR porte le SHA de tête relu et les résultats distants. La
fusion reste soumise à la CI `tests / ci` sur ce SHA, puis au contrôle du commit
résultant sur `main`, conformément à `AGENTS.md`.

## Décisions du journal d’exécution

Les décisions ci-dessous sont conservées intégralement avant suppression du
répertoire de travail temporaire, avec leur justification et le coût indiqué
en cas d’erreur. Leurs contrôles et corrections sont relatés plus haut.

- Ruling: Reuse the existing clean dedicated-branch checkout, with no new worktree — desktop instructions prefer a suitable checkout and user requested a branch, already created; dependencies and Docker are configured here — cost if wrong: less filesystem isolation, mitigated by one implementer at a time and branch/status checks.

- Ruling: Treat « go » as authorization to execute the approved design and its implementation plan without another approval stop — the user has already approved scope, deletion and approach, and asked to start — cost if wrong: reversible code changes on the dedicated branch, destructive scope already explicitly approved.

- Ruling: Retain the requested subagent execution preference from this session — no later request supersedes it — cost if wrong: review/context overhead only.

- Ruling: Task 4 is coordinated by the controller with scoped verification/review agents — its external effects and mandatory independent review cannot be delegated to a worker that also spawns reviewers — cost if wrong: controller carries more operational context.

- Ruling: Task 1 may read the plan File Structure lines to resolve « five files above » omitted by task-brief extraction — exact migration paths are required; no design change — cost if wrong: additional reading only.

- Ruling: Keep Composer scripts unchanged in task 1; Makefile is the contractual setup entrypoint and receives locale synchronization — scope follows approved spec — cost if wrong: direct Composer setup still requires the documented language-sync step.

- Ruling: Seed no expansion relations initially — approved spec allows empty tables, and existing relation examples mixed editorial comparisons with facts; preserve runtime relation functionality — cost if wrong: a few optional real links remain unseeded.

- Ruling: Allow documented regional markets, omit unconfirmed national release rows and optional fields rather than fill quotas — explicit real-data requirement dominates former example counts — cost if wrong: fewer release dates visible until more primary sources are supplied.

- Ruling: Carry Task 1 minor seeder-test coverage into Task 2 — Task 2 already owns direct seeder behavior and will be reviewed independently — cost if wrong: small additional test edit.

- Ruling: Task 2 may extract protected AvifImages::encode(UploadedFile): string and add CatalogImages::matchesStoredUpload() — store re-encodes AVIF, so comparing original bytes would not provide idempotent real-media seeding; share the encoder instead of duplicating it — cost if wrong: a small shared image-service delta requiring regression tests and review. Approved to implementer.

- Ruling: Include remaining old stored-format support in Task 3 cleanup: GameImages managed paths/rename and AvifImages write-extension parameter can become AVIF-only, while JPEG/PNG/WebP/SVG uploads remain supported — legacy stored WebP/SVG exist solely because older data was retained, now explicitly disposable — cost if wrong: manually imported non-AVIF database paths will no longer be treated as managed current images. Update task brief and plan before dispatch.

- Ruling: Omit the national France release row for Pokémon 151 unless its source explicitly establishes the territory; retain the real French printed edition — the Pokémon research report's initially accepted row inferred too much from a French publisher page, conflicting with the strict market rule — cost if wrong: one genuine release date remains unseeded until territorial evidence is available. Sent to Task 2 implementer before completion.

- Ruling: Accept a documented worldwide Pokémon 151 launch instead of an inferred France launch if the newly read official Pokémon press communiqué establishes it — the implementer found launch-day primary evidence at https://the-pokemon-company-international.prezly.com/lextension-ecarlate-et-violet-151-du-jeu-de-cartes-a-collectionner-pokemon-sort-aujourdhui-mettant-a-lhonneur-les-151-tout-premiers-pokemon — cost if wrong: worldwide is broader than a country market, explicitly documented in the source note and subject to task review.

- Ruling: Include DashboardTest.php in Task 3's AVIF fixture update — implementer found the same obsolete stored SVG example there, alongside the named public/auth fixtures — cost if wrong: one extra test fixture edit, with unchanged business assertions and task review.

- Ruling: Accept final review's declined-to-judge boundaries — old-data preservation, exhaustive card catalogue/unsupported variants, repeated live availability checks of all previously verified sources, and forensic/off-project erasure are outside the approved task — cost if wrong: those additional operations need a separate scoped request; the recorded original-source access limit remains visible.

- Ruling: rerun make check and test-storage after this runtime rebuild, then clean testing residues again — runtime inputs changed during the required install repeat, so earlier checks alone do not validate this final local environment — cost if wrong: one additional full verification run.

- Ruling: Use the repository’s existing explicit authorization for branch publication, PR and exact-SHA squash merge after independent review and green CI, rather than request the finishing skill’s choice menu again — AGENTS.md and user approval already select the delivery workflow — cost if wrong: publication follows the recorded authorization; merge remains gated by review and CI.

- Ruling: Record post-publication statuses in PR metadata and the final response rather than add a commit claiming its own future CI/merge — versioned verification is explicitly a pre-publication local report — cost if wrong: readers of the local snapshot must consult the linked delivery evidence for GitHub completion.
