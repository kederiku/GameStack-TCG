# Schéma neuf et catalogue TCG réel — vérifications

Date : 2026-11-24. Branche : `codex/fresh-schema-and-tcg-seed`.
Les sections initiales retracent la remise à zéro et les contrôles réalisés
avant l’enrichissement demandé sur la PR 9. Les résultats de publication,
de fusion et de CI sont suivis dans cette PR.

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

## Seed réel initial

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

Le manifeste initial contient 12 jeux, 19 catalogues, sept séries, 29 extensions,
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

## Revue complète initiale

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

## Parcours navigateur initial

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

## État local après la remise à zéro initiale

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

## Enrichissement du catalogue

Le [complément demandé](specs/2026-11-24-tcg-catalog-enrichment-design.md) porte
le catalogue à 119 fiches, 327 éditions imprimées dont 78 françaises, 213 sorties
et dix langues de contenu. Les douze jeux, 19 catalogues, sept séries et neuf
marchés restent les mêmes. Les interfaces restent en français et en anglais.

| Jeu                  |  Fiches | Éditions | Dont FR | Sorties |
| -------------------- | ------: | -------: | ------: | ------: |
| Altered              |       5 |       10 |       5 |       5 |
| Cyberpunk TCG        |       1 |        2 |       1 |       1 |
| FINAL FANTASY TCG    |      10 |       43 |       9 |      13 |
| Flesh and Blood      |      10 |       26 |       4 |      11 |
| Disney Lorcana       |      10 |       33 |      10 |      24 |
| Magic                |      10 |       60 |      10 |      54 |
| NARUTO Mythos        |       2 |        4 |       2 |       3 |
| ONE PIECE            |      20 |       24 |       4 |      21 |
| Pokémon              |      15 |       25 |       7 |      17 |
| Riftbound            |       6 |       22 |       6 |      15 |
| Star Wars: Unlimited |      10 |       40 |      10 |       9 |
| Yu-Gi-Oh!            |      20 |       38 |      10 |      40 |
| **Total**            | **119** |  **327** |  **78** | **213** |

ONE PIECE et Yu-Gi-Oh! ont chacun dix titres dans deux catalogues régionaux.
Riftbound comprend cinq sets principaux et le coffret Premiers pas ; Star Wars:
Unlimited comprend neuf sets principaux et le mini-set Icons 2027 Edition,
commercialisé en 2026. Altered conserve cinq sets distribués en boosters
d’origine ; les produits imprimés à la demande sont documentés dans les sources.
Les catalogues récents ne sont pas complétés artificiellement. Les sorties dont
la date, le marché ou le canal restent incertains sont omises.

Les langues physiques sont EN 90, FR 78, JA 48, DE 26, ES 21, IT 27, PT 8,
KO 3, chinois simplifié 22 et traditionnel 4. Les titres anglais non latins sont
officiels lorsqu’ils existent, sinon identifiés comme rendus éditoriaux.
Les noms natifs et les langues imprimées sont conservés. Une traduction du
nom n’ajoute jamais une édition physique.

Le commit `8dc76a324ced5747ad74804846bc3afbb40b9b59` ajoute le repli anglais de
`display_name` et aligne le tri SQL sur le nom affiché. Les descriptions et les
valeurs explicitement nulles gardent leurs règles. La revue indépendante
`enrichment_fallback_review5` valide ce changement, avec 30 tests ciblés et
475 assertions, puis deux contrôles Filament et sept assertions.

Les données sont ajoutées au commit `d260842c006d30b20da69613e3bb4b8c98b7b777`,
puis corrigées jusqu’à `85a75db0679d012262781dbf8bcbf0a85038f1ba` après les revues
`enrichment_catalog_review6` et `enrichment_catalog_fix_review6`. La graphie
chinoise complète de Dominaria United est testée. Les noms commerciaux français
des Opus FFTCG 25, 28, 29 et 30 sont étayés par leur distributeur officiel.
Pour les seuls Opus 26 et 27, les liens du distributeur étant indisponibles,
le registre identifie explicitement des textes commerciaux secondaires,
recoupés avec les titres et scans français Square Enix. Aucune date ou portée
territoriale ne découle de ces deux fiches. La relecture a vérifié les pages
accessibles et les cartes françaises ; aucun constat de données ne reste ouvert.

### Vérifications de l’enrichissement

- `make check` sur `d260842` : 400 tests PHP, 16 283 assertions, 19 tests frontend,
  Pint, PHPStan, format, lint, traductions et TypeScript réussis.
- Après correction des données : 12 tests du seed, 12 338 assertions et Pint
  ciblé réussis. Les tests couvrent les éditions réelles, la provenance, les
  traductions, les identités et médias à deux passages et les annulations.
- `make up` puis `make test-storage` sur `d44941d` : deux tests, 24 assertions.
- Le build de production avait déjà réussi par `make assets` puis `make init` ;
  les fichiers frontend, dépendances et configuration de build n’ont pas changé
  pendant l’enrichissement. La CI finale reconstruit néanmoins cette version.

### Revue finale et concurrence

La revue complète de la branche par l’agent indépendant
`enrichment_catalog_review6` (GPT-6.2 Astra), depuis
`5cdf438c1cdb82b30e6bc4650cf6c89213f8ad35`, a relevé un ordre de verrouillage
incompatible entre le seed et les formulaires multilingues concurrents.
Le correctif `6b27ae997494fa7ab632bb7b27c022272f9d12fe` impose le même ordre
canonique des langues, tout en conservant les clés de référence et leur casse.
Le test observe les vrais verrous SQL : échec attendu avant correction, puis
ordre DE, EN, chinois simplifié et réutilisation des identités historiques.

La relecture indépendante approuve ce SHA et la procédure opérationnelle,
sans nouveau défaut. Les 134 tests ciblés passent avec 14 085 assertions.
Le **dernier `make check` sur ce code passe : 400 tests PHP, 16 297 assertions,
19 tests frontend**, avec Pint, PHPStan, lint, format, traductions et TypeScript.
Le tri des noms de repli en administration française a désormais son assertion
Filament directe. Le contrôle automatique de provenance pourrait couvrir aussi
tous les codes facultatifs et libellés latins ; ce renforcement reste une
amélioration mineure différée. Les valeurs actuelles ont reçu une revue des sources.

### Application et navigateur après enrichissement

- Application locale mise en maintenance, queue arrêtée et destinations
  inventoriées avant l’opération. Deux exécutions de `make seed` réussies,
  sans nouvelle remise à zéro : toutes les anciennes identités sont conservées.
  Le second passage retrouve exactement les nouveaux identifiants et les quatre
  mêmes chemins S3 et empreintes SHA-256. Les comptes par jeu et langue relevés
  en base correspondent au tableau ci-dessus.
- Parcours dans le navigateur par l’accès normal et un compte temporaire :
  `/fr/pokemon/japanese/scarlet-ex?edition=ja` affiche « Scarlet ex » et
  « Nom affiché en anglais », tout en conservant `スカーレットex` comme nom
  officiel et une seule édition japonaise. Le sélecteur passe à `/en` en
  conservant `edition=ja` ; le libellé anglais n’a plus de badge de repli.
- Forces Temporelles propose cinq éditions physiques : FR, EN, DE, ES et IT.
  Passer l’interface française en anglais conserve `edition=fr`, le nom
  imprimé français et la bonne ressource. Aucune date non établie n’est ajoutée.
- Filament français affiche 119 extensions et 327 éditions. Son filtre Pokémon
  japonais affiche dans l’ordre « Pokémon 151 (Japon) », « Scarlet ex »,
  « Violet ex ». Les noms de série utilisent aussi leur repli anglais.
  Aucun enregistrement de catalogue n’a été modifié durant ce contrôle.
- Aucune erreur JavaScript observée ; l’onglet de QA a été fermé avant nettoyage.

### État local final après enrichissement

Les inventaires avant et après QA ont distingué les résidus de cette campagne
et une session anonyme préexistante dont l’origine ne pouvait pas être établie.
Cette session a été **conservée**, avec vérification de son identifiant et de
l’empreinte de sa ligne inchangés. Le nettoyage vise uniquement la session du
compte QA. Les deux entrées de cache ont été rapprochées exactement du compteur
et du minuteur de limitation de sa connexion, sans exposer clé ni adresse IP.
Les autres tables transitoires et Mailpit étaient vides.

| Élément                                  | Résultat final observé                                  |
| ---------------------------------------- | ------------------------------------------------------- |
| Utilisateurs, tokens, passkeys           | 0                                                       |
| Session de QA                            | Supprimée                                               |
| Session anonyme préexistante             | 1, conservée sans modification                          |
| Jobs, lots, échecs et cache              | 0                                                       |
| Jeux / catalogues / séries               | 12 / 19 / 7                                             |
| Extensions / éditions / sorties          | 119 / 327 / 213                                         |
| Langues de contenu / marchés / relations | 10 / 9 / 0                                              |
| Garage                                   | 4 objets, chemins et SHA-256 identiques au seed initial |
| Base de tests                            | Seul schéma public, vide                                |
| Mailpit                                  | 0 message                                               |
| Anciennes tables, fonctions et archives  | Absentes                                                |

Les trois fichiers des disques simulés, 121 vues compilées et le journal ne
contenant que des entrées `testing.ERROR` ont été supprimés, ainsi que le compte
et son mot de passe temporaires et le cache PHPUnit. Les médias, `.env`, les accès
Garage et les volumes restent préservés. L’application est sortie de maintenance,
le worker tourne et les services sont disponibles ; `/up` répond HTTP 200.
Les visites ultérieures peuvent naturellement recréer des sessions et du cache.

Le SHA documentaire final, sa revue indépendante et les résultats GitHub sont
consignés dans la [PR 9](https://github.com/kederiku/GameStack-TCG/pull/9).

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

- Ruling: Keep explicit user-approved enrichment requirements without another design approval loop — latest answers specify quantity, physical French coverage and English fallback — cost if wrong: reversible seed/code choices on already isolated branch.

- Ruling: Select five new international Pokémon sets, without the four optional native additions — existing regional distinctions plus these additions meet the approximate distinct-title target, while preserving all current native rows and adding English labels — cost if wrong: four verified optional products remain available for later expansion.

- Ruling: Do not copy global Lorcana EN/FR launch dates to later Japanese/Chinese printings — official local releases demonstrably differ — cost if wrong: unconfirmed release rows stay absent, while physical editions remain visible.

- Ruling: Extend Task 5 to CatalogPresentation::nameForQuery and corresponding sorting tests — implementer found its SQL explicitly promises equivalence to TranslatedContent, so leaving it unchanged would sort by a different name than displayed — cost if wrong: small additional SQL delta, covered by task review and focused tests. Brief/plan updated before implementation.

- Ruling: Add Cyberpunk's actual FR edition using WeirdCo's official print record, but omit its FR date sourced only to a retailer; omit Riftbound Spiritforged zh-Hant while availability rests only on a Hong Kong reseller — strict primary-fact provenance remains binding — cost if wrong: confirmed optional dates/editions can be added later with a primary source.

- Ruling: Permit explicitly identified secondary French commercial-title corroboration ONLY for FFTCG Opus26/27, paired with publisher English titles and publisher French card scans — real French printing and set identity already have primary proof, but dead distributor URLs cannot substantiate the exact commercial title; consultable retailer text is stronger than inventing a French translation or claiming English stock images prove one — cost if wrong: those two title spellings depend on retailer transcription and must remain attributed as secondary, with no release/market fact inferred. User's real-data requirement remains binding; this narrowly refines the plan's all-primary provenance preference.

- Ruling: Keep the final review's optional source-guard expansion as a deferred Minor — all actual manifest values received independent data review and the current required-field checks pass; automatically asserting every optional code and Latin label would broaden this final concurrency fix — cost if wrong: future additions still need source review to catch omissions that the current contract does not reject.

- Ruling: Accept final review boundaries for already-reviewed source pages and historical reset evidence, while requiring actual new seed/QA/cleanup observations — repeat live browsing of every source and reconstructing the completed reset add no new requirement; new operational facts cannot be inferred from a diff — cost if wrong: source availability can later change and remains documented, and final local observations must still be collected before delivery.

- Ruling: Preserve the one pre-existing anonymous application session because its latest activity cannot be attributed confidently to our QA — this enrichment only authorizes test/QA residue cleanup — cost if wrong: one anonymous session remains until ordinary expiry instead of a completely empty sessions table; no account or test user remains. A fingerprint snapshot stores only ID/hash privately; final cleanup will remove only new QA sessions and verify the original row unchanged. Updated scratch finish-enrichment-qa.php and final-enrichment-inventory.php implement this execution-time branch of the approved selective-cleanup procedure.
