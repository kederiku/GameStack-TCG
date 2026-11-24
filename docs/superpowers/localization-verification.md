# Vérification de la localisation FR et EN

## Contrôles locaux

La base de comparaison est `c3a57c8029a1063823e9f85f96d1b99cd329a808`.
Le 24 novembre 2026, le schéma applicatif est encore historique. Les contrôles de
cette section utilisent des fixtures dans des schémas isolés de
`gamestackr_testing`. La revue indépendante de la branche jusqu’au commit
`213e7a6963cf0fc7dd96d5a9887cd436c546e2fa` a demandé sept corrections importantes
et quatre mineures. La relecture du delta qui les traite précède la répétition
sur sauvegarde et la bascule locale.

| Vérification                                       | Résultat                                                                                                                                                                                                                                                                                                                                   |
| -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Authentification, routes, dates et traductions     | 31 tests ciblés et 674 assertions avant les derniers ajouts sur les contrats JSON Fortify et la colonne des jeux. La suite complète couvre aussi ces ajouts.                                                                                                                                                                               |
| Installation neuve                                 | Le vrai `localization:install` a créé le schéma, migré, vérifié et finalisé la V1. Les tests contrôlent la réouverture, le maintien des options d’une maintenance existante, le refus d’un schéma historique et la persistance de la maintenance en cas d’échec. Le store de maintenance est isolé pendant ces tests.                      |
| Conversion historique et finalisation              | Les deux suites dédiées passent avec 41 tests et 481 assertions. Elles couvrent deux horloges sur le même historique incomplet, des contraintes manquantes avec données intactes et des restaurations incomplètes avant et après finalisation.                                                                                             |
| `make check`                                       | Pint et PHPStan réussis, 448 tests PHP et 5 648 assertions, 19 tests frontend, lint sans avertissement et TypeScript réussi                                                                                                                                                                                                                |
| `make assets`, `make up`, `make test-storage`      | Build de production réussi, services démarrés, 3 tests Garage réel et 36 assertions                                                                                                                                                                                                                                                        |
| Préaudit en lecture seule de la base applicative   | `make artisan ARGS=localization:audit` réussi. 6 langues, 13 jeux, 7 marchés, 20 catalogues, 8 séries, 30 extensions, 10 localisations de série et 11 d’extension, 36 éditions, 27 sorties, 7 relations, 167 liaisons, 21 traductions, 20 descriptions nulles et 2 images de série. L’audit est à répéter avant et pendant la maintenance. |
| Navigateur et restauration de la sauvegarde réelle | En attente de la relecture des corrections puis de la répétition                                                                                                                                                                                                                                                                           |
| CI de la PR et de main                             | En attente de publication                                                                                                                                                                                                                                                                                                                  |

Les réponses HTML et JSON de succès passkey conservent les destinations locales,
y compris les URL signées. La fixture SSR désactive uniquement les passkeys dans
Fortify, régénère Wayfinder et l’export React, vérifie les types et les formulaires
de connexion et de confirmation. Une cérémonie simulée vérifie la navigation
document vers Filament. La fixture allemande soumet aussi quatre collisions au
validateur et à la reconstruction du cache des routes.

### Dépendances et appels restants

- Comparaison des manifestes et lockfiles avec la base, des fichiers suivis et nouveaux, des chemins de génération, des modèles supprimés et des destinations. `tests/TestCase.php` conserve le garde-fou PostgreSQL. Les artefacts Wayfinder et les traductions JS sont régénérés depuis les sources PHP.
- Le verrou Composer ajoute Spatie Laravel Translatable. La mise à jour ciblée de `web-auth/webauthn-lib` 5.3.12 vers 5.3.13 ne modifie aucune contrainte ni autre paquet durant cette dernière étape. Les six avis signalés sur la version précédente ont disparu de `make composer ARGS='audit --locked'`. La couverture du serveur inclut le RP ID, les options JSON, les middlewares et le contrôle du propriétaire. [Correctif du mainteneur](https://github.com/web-auth/webauthn-framework/releases/tag/5.3.13)
- L’audit npm du 24 novembre signale trois entrées modérées pour `glob`, `minimatch` et leur dépendant `laravel-translator`, aucune élevée ou critique, aucun correctif proposé dans cette résolution. L’application importe `laravel-translator/pluralizer` 1.1.1. Son module et son fragment autonome ne chargent pas le fragment de l’exporteur qui importe `glob`. Ces dépendances restent installées. Le projet utilise son propre exporteur PHP. [Avis glob](https://github.com/advisories/GHSA-3g3m-2fjr-6pw4), [avis minimatch](https://github.com/advisories/GHSA-g3hv-75wg-m249)
- La recherche des appels bilingues `t(fr, en)`, des ajouts de `?lang=`, des anciens modèles et des chemins de navigation littéraux a été rapprochée du registre des routes. Le middleware lit encore `lang` sur les anciens liens. Les noms et chemins Fortify historiques appartiennent au contrat de compatibilité. Le lien CLI `make admin` ouvre l’alias qui négocie la langue de la session. Les imports de composants de paramètres sont des chemins de modules. Les anciennes tables apparaissent dans la conversion V1, les migrations historiques, le finaliseur et leurs tests. Les clés historiques du manifeste demeurent des identifiants stables.
- Les catalogues livrés utilisent les décomptes non négatifs du site et des formes positionnelles ou des intervalles explicites couvrant ces décomptes. Le validateur refuse le mélange d’intervalles et de formes positionnelles. Pour des nombres négatifs, la sélection positionnelle du paquet JS peut différer de Laravel. Une extension à ces cas demandera une vérification de parité.

## Sauvegarde et répétition sous Compose

Cette procédure concerne le projet Compose local PostgreSQL 18, dont les tables
applicatives se trouvent dans `public`. Exécuter les blocs dans le même shell
Bash depuis la racine du dépôt. Garder les sorties détaillées, les archives, les
identifiants QA et le lien de maintenance dans un répertoire privé. Le premier
échec interrompt la chaîne. Conserver les services suspendus et les archives le
temps de diagnostiquer l’échec. La base de répétition est exclusivement
`gamestackr_testing`. Arrêter les autres utilisations de cette base pendant la
répétition.

### Préparation et arrêt des écritures

```bash
set -euo pipefail
umask 077
snapshot=$(mktemp -d "$PWD/storage/app/private/localization-XXXXXXXX")
dc() { docker compose --profile local "$@"; }
pg() { dc exec -T postgres sh -ec 'exec psql -XqAt -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "${1:-$POSTGRES_DB}"' sh "$@"; }
previous=c3a57c8029a1063823e9f85f96d1b99cd329a808
git archive --format=tar "$previous" | gzip > "$snapshot/previous-code.tar.gz"
git rev-parse "$previous" > "$snapshot/previous-sha"
dc ps --services --status running > "$snapshot/running"
mkdir "$snapshot/prior-framework"
for file in down maintenance.php; do
    if test -f "storage/framework/$file"; then cp -p "storage/framework/$file" "$snapshot/prior-framework/"; fi
done
make -s artisan ARGS=localization:audit > "$snapshot/audit-before.log" 2>&1
```

Vérifier que l’environnement effectif est `local`, l’URL se trouve sur
`localhost`, le SMTP effectif est `mailpit:1025` et aucun DSN alternatif
ne le remplace. Relever les ports publiés avec `dc port app 8000` et
`dc port mailpit 8025`. Vérifier la base et le schéma de la connexion Laravel
contre `select current_database(), current_schema()` exécuté via `pg`.
Le contrôle du 24 novembre a trouvé `local`, `localhost`,
`gamestackr/public`, une application ouverte, des caches de configuration et de
routes désactivés, et le SMTP `mailpit:1025`. Refaire ces contrôles pour la
bascule et consigner l’état des producteurs externes. Sur une installation
déjà en maintenance, conserver ses options et son propriétaire. Le secret
existant reste privé. Pour une installation ouverte, établir l’accès temporaire
et capturer sa sortie en privé.

```bash
preflight=$(cat <<'CMD'
tinker --execute='throw_unless(app()->environment("local") && in_array(parse_url(config("app.url"), PHP_URL_HOST), ["localhost", "127.0.0.1"], true) && DB::scalar("select current_database()") !== "gamestackr_testing" && DB::scalar("select current_schema()") === "public" && config("mail.default") === "smtp" && config("mail.mailers.smtp.transport") === "smtp" && config("mail.mailers.smtp.host") === "mailpit" && (int) config("mail.mailers.smtp.port") === 1025 && !config("mail.mailers.smtp.url"), RuntimeException::class); echo DB::scalar("select current_database()")."|".DB::scalar("select current_schema()");'
CMD
)
test "$(make -s artisan ARGS="$preflight")" = "$(echo 'select current_database(), current_schema()' | pg)"
initial_down=$(make -s artisan ARGS="tinker --execute='echo (int) app()->isDownForMaintenance();'")
case "$initial_down" in
    0) make -s artisan ARGS='down --with-secret' > "$snapshot/maintenance-access" 2>&1 ;;
    1) ;; # Le propriétaire fournit son accès temporaire existant.
    *) exit 1 ;;
esac
dc stop -t 100 queue
# Une fois les imports, tâches planifiées et autres producteurs suspendus
pg > "$snapshot/queue-counts" <<'SQL'
SELECT count(*), count(*) FILTER (WHERE reserved_at IS NOT NULL),
       count(*) FILTER (WHERE payload ~* 'SeriesLocalization|ExpansionLocalization|series_localizations|expansion_localizations') FROM jobs;
SELECT count(*), count(*) FILTER (WHERE payload ~* 'SeriesLocalization|ExpansionLocalization|series_localizations|expansion_localizations') FROM failed_jobs;
SQL
make -s artisan ARGS=localization:audit > "$snapshot/audit-quiescent.log" 2>&1
```

Contrôler les compteurs avant de poursuivre. Une réservation non résolue ou une
charge historique réclame un diagnostic privé et une décision explicite avant
reprise. Conserver les jobs en attente ou échoués. Examiner les producteurs de
charges opaques si la file en contient. Vérifier que personne n’utilise le
secret de maintenance pendant la copie.

### Archives et empreintes

```bash
dc exec -T postgres sh -ec 'exec pg_dump -Fc -U "$POSTGRES_USER" -d "$POSTGRES_DB"' > "$snapshot/database.dump"
dc exec -T postgres pg_restore -l < "$snapshot/database.dump" > "$snapshot/database.toc"
dc stop garage-webui garage
garage_id=$(dc ps --all --quiet garage)
test -n "$garage_id"
for volume in data meta; do
    docker run --rm --network none --volumes-from "$garage_id:ro" --user 0 \
        --entrypoint tar postgres:18-bookworm -C "/var/lib/garage/$volume" -czf - . \
        > "$snapshot/garage-$volume.tar.gz"
done
tar -czf "$snapshot/local-files.tar.gz" .env storage/app/public storage/app/private/garage
for archive in "$snapshot"/*.tar.gz; do tar -tzf "$archive" > /dev/null; done
(cd "$snapshot" && shasum -a 256 database.dump *.tar.gz > SHA256SUMS && shasum -a 256 -c SHA256SUMS)
```

Redémarrer uniquement les services Garage précédemment actifs.

```bash
if grep -Fxq garage "$snapshot/running"; then dc up -d --wait --no-deps garage; fi
if grep -Fxq garage-webui "$snapshot/running"; then dc start garage-webui; fi
```

Les objets
conservés restent disponibles pour la vérification navigateur. L’application et
la file restent respectivement sous maintenance et suspendue.

Créer un manifeste qui compare les nombres de lignes et leurs empreintes
ordonnées indépendamment de leur ordre physique, ainsi que l’état des séquences.
La sortie contient uniquement des noms de tables, des compteurs et des empreintes.
Les diagnostics éventuels restent privés.

```bash
cat > "$snapshot/manifest.sql" <<'SQL'
SET TIME ZONE 'UTC';
SELECT format($query$SELECT %L, count(*),
 encode(sha256(convert_to(coalesce(string_agg(d, '' ORDER BY d COLLATE "C"), ''), 'UTF8')), 'hex')
 FROM (SELECT encode(sha256(convert_to(to_jsonb(r)::text, 'UTF8')), 'hex') AS d FROM %I.%I r) s$query$,
 tablename, schemaname, tablename) FROM pg_tables WHERE schemaname='public' ORDER BY tablename
\gexec
SELECT format('SELECT %L, last_value, is_called FROM %I.%I', sequencename, schemaname, sequencename)
 FROM pg_sequences WHERE schemaname='public' ORDER BY sequencename
\gexec
SQL
pg < "$snapshot/manifest.sql" > "$snapshot/original.rows" 2> "$snapshot/manifest-errors"
```

### Restauration dans la seule base de test

Conserver le schéma de test préexistant et son OID. Les noms de réserve fixes
font échouer une seconde tentative si une répétition précédente reste à reprendre.
Le bloc transactionnel contrôle le nom de la base avant tout changement.

```bash
cat > "$snapshot/rehearsal-cleanup.sql" <<'SQL'
BEGIN;
DO $$ BEGIN
 IF current_database() <> 'gamestackr_testing'
    OR (SELECT oid FROM localization_rehearsal_guard.original)
        IS DISTINCT FROM 'localization_original_testing_public'::regnamespace::oid
 OR (SELECT replay_oid FROM localization_rehearsal_guard.original)
        IS DISTINCT FROM 'public'::regnamespace::oid
 THEN RAISE EXCEPTION 'Unexpected restoration target'; END IF;
END $$;
DROP SCHEMA public CASCADE;
ALTER SCHEMA localization_original_testing_public RENAME TO public;
DROP SCHEMA localization_rehearsal_guard CASCADE;
COMMIT;
SQL
restore_test_schema() { pg gamestackr_testing < "$snapshot/rehearsal-cleanup.sql" > "$snapshot/rehearsal-cleanup.log" 2>&1; }
pg gamestackr_testing <<'SQL'
BEGIN;
DO $$ BEGIN IF current_database() <> 'gamestackr_testing' THEN RAISE EXCEPTION 'Wrong database'; END IF; END $$;
CREATE SCHEMA localization_rehearsal_guard;
CREATE TABLE localization_rehearsal_guard.original AS SELECT 'public'::regnamespace::oid AS oid;
ALTER SCHEMA public RENAME TO localization_original_testing_public;
CREATE SCHEMA public AUTHORIZATION pg_database_owner;
ALTER TABLE localization_rehearsal_guard.original ADD COLUMN replay_oid oid;
UPDATE localization_rehearsal_guard.original SET replay_oid='public'::regnamespace::oid;
COMMIT;
SQL
trap restore_test_schema EXIT
dc exec -T postgres sh -ec '
    target=gamestackr_testing
    test "$(psql -XAt -U "$POSTGRES_USER" -d "$target" -c "select current_database()")" = "$target"
    exec pg_restore --single-transaction --exit-on-error --schema=public \
        --no-owner --no-privileges -U "$POSTGRES_USER" -d "$target"
' < "$snapshot/database.dump" > "$snapshot/restore.log" 2>&1
pg gamestackr_testing < "$snapshot/manifest.sql" > "$snapshot/restored.rows" 2> "$snapshot/restored-errors"
cmp "$snapshot/original.rows" "$snapshot/restored.rows"
```

Le schéma restauré contient des données privées. Utiliser seulement les commandes
de conversion. Isoler les fichiers de maintenance et la configuration des caches
de l’application. Les commandes suivantes utilisent le stockage temporaire
persistant dans le répertoire de sauvegarde, avec messagerie en mémoire.

```bash
mkdir -p "$snapshot/rehearsal-storage"/{logs,framework/{views,sessions,cache/data}}
test_exec="docker compose --profile local run --rm --no-deps -T -e APP_ENV=testing -e DB_CONNECTION=pgsql -e DB_HOST=postgres -e DB_PORT=5432 -e DB_DATABASE=gamestackr_testing -e DB_URL= -e APP_CONFIG_CACHE=/tmp/rehearsal-config.php -e APP_ROUTES_CACHE=/tmp/rehearsal-routes.php -e APP_MAINTENANCE_DRIVER=file -e MAIL_MAILER=array -e QUEUE_CONNECTION=sync -e CACHE_STORE=array -e SESSION_DRIVER=array -v '$snapshot/rehearsal-storage:/var/www/html/storage' app"
guard=$(cat <<'CMD'
tinker --execute='throw_unless(app()->environment("testing") && DB::scalar("select current_database()") === "gamestackr_testing" && DB::scalar("select current_schema()") === "public", RuntimeException::class);'
CMD
)
rehearse() {
    make -s artisan EXEC="$test_exec" ARGS="$guard" >> "$snapshot/rehearsal.log" 2>&1
    make -s artisan EXEC="$test_exec" ARGS="$1" >> "$snapshot/rehearsal.log" 2>&1
}
rehearse down
rehearse localization:audit
rehearse 'migrate --force'
rehearse localization:audit
rehearse 'localization:finalize --dry-run'
rehearse localization:finalize
rehearse localization:audit
```

Capturer la preuve avant le nettoyage.

```bash
proof='SELECT source_hashes, target_hashes, counts FROM localization_conversion_verifications WHERE version=1 AND verified_at IS NOT NULL AND finalized_at IS NOT NULL'
echo "$proof" | pg gamestackr_testing > "$snapshot/rehearsal-proof"
test "$(wc -l < "$snapshot/rehearsal-proof")" -eq 1
```

Après succès, restaurer le schéma de test initial et supprimer seulement la
copie privée de répétition. Le piège de sortie restaure aussi le schéma de test en cas d’échec. En cas
d’interruption du processus, reprendre avec le même répertoire privé et le SQL
de nettoyage, qui vérifie les deux OID avant toute suppression.

```bash
restore_test_schema
trap - EXIT
```

### Bascule et réouverture

Sous maintenance, avec la répétition réussie et les producteurs toujours arrêtés,
exécuter dans cet ordre. Consulter chaque résultat avant de poursuivre.

```bash
for operation in 'localization:audit' 'migrate --force' 'localization:audit' \
    'localization:finalize --dry-run' 'localization:finalize' 'localization:audit' 'localization:check'; do
    make -s artisan ARGS="$operation" >> "$snapshot/application-cutover.log" 2>&1
done
echo "$proof" | pg > "$snapshot/application-proof"
cmp "$snapshot/rehearsal-proof" "$snapshot/application-proof"
make assets
make -s artisan ARGS=config:cache
make -s artisan ARGS=route:cache
```

Vérifier la preuve V1, les deux empreintes source, les empreintes cibles et
les compteurs avant toute écriture QA. Avec le même historique de départ, la
répétition et la bascule doivent produire les mêmes preuves, indépendamment des
horodatages. L’ancien schéma de localisation n’est retiré qu’après comparaison
V1 sous verrou par le finaliseur. Utiliser le secret temporaire pour les parcours
public/auth/compte/admin FR et EN. Les mutations et messages Mailpit doivent
viser uniquement les fixtures jetables de cette session. Nettoyer ces fixtures,
vérifier les compteurs et restaurer l’état antérieur des caches de développement.
Seulement après les contrôles navigateur, exécuter `make -s artisan ARGS=up` si
le site était ouvert, puis `dc start queue` si la file tournait. Rétablir les
autres producteurs qui étaient actifs.

### Retour avant réouverture

Garder l’application en maintenance. En cas d’échec de conversion ou de QA,
conserver à part les journaux et l’état courant. Restaurer sur des **volumes
neufs** et remettre le **code antérieur** en service avec sa configuration et
ses dépendances verrouillées. Ces commandes sont destinées au retour réel.
La répétition ci-dessus utilise exclusivement la base de test.

```bash
(cd "$snapshot" && shasum -a 256 -c SHA256SUMS)
recovery=$(mktemp -d "$snapshot/recovery-XXXXXXXX")
tar -xzf "$snapshot/previous-code.tar.gz" -C "$recovery"
tar -xzf "$snapshot/local-files.tar.gz" -C "$recovery"
# Libérer les ports en conservant tous les conteneurs et volumes de la bascule.
dc stop
(
    cd "$recovery"
    export COMPOSE_PROJECT_NAME="localization-recovery-$(date +%s)"
    test -z "$(docker volume ls -q --filter label=com.docker.compose.project="$COMPOSE_PROJECT_NAME")"
    dc create garage
    recovered_garage=$(dc ps --all --quiet garage)
    for volume in data meta; do
        docker run --rm -i --network none --volumes-from "$recovered_garage" --user 0 \
            --entrypoint sh postgres:18-bookworm -ec \
            'cd "/var/lib/garage/$1"; test -z "$(ls -A)"; exec tar -xzf -' sh "$volume" \
            < "$snapshot/garage-$volume.tar.gz"
    done
    dc up -d --wait postgres
    dc exec -T postgres sh -ec 'exec pg_restore --single-transaction --exit-on-error -U "$POSTGRES_USER" -d "$POSTGRES_DB"' \
        < "$snapshot/database.dump" > "$snapshot/recovery-database.log" 2>&1
    pg < "$snapshot/manifest.sql" > "$snapshot/recovered.rows"
    cmp "$snapshot/original.rows" "$snapshot/recovered.rows"
    make composer ARGS='install --no-interaction --prefer-dist'
    make npm ARGS=ci
    make assets
    old_exec='docker compose --profile local run --rm --no-deps -T app'
    make -s artisan EXEC="$old_exec" ARGS=storage:link
    if test -f "$snapshot/maintenance-access"; then
        make -s artisan EXEC="$old_exec" ARGS='down --with-secret' > "$snapshot/recovery-access" 2>&1
    else
        cp -pR "$snapshot/prior-framework/." storage/framework/
    fi
    dc up -d --wait garage
    dc up -d --no-deps app
    # Garder le projet et son répertoire pour le service restauré.
    echo "$COMPOSE_PROJECT_NAME" > "$snapshot/recovery-project"
)
```

Dans le répertoire `$recovery`, exporter le nom enregistré dans
`recovery-project`. Vérifier l’accès privé, les références et les images
sur la version précédente, puis reprendre uniquement les services qui étaient
actifs. Une maintenance préexistante doit conserver ses options d’origine.
Après réouverture, toute restauration demande d’abord un rapprochement des
écritures reçues depuis la sauvegarde.

## Décisions et conséquences

Les décisions ci-dessous conservent les termes utilisés pendant les revues
techniques. Les quatre premières conséquences explicitées portent sur
l’isolation du checkout, le contrat PHP, le suivi des tests intermédiaires et
le report des vérifications navigateur.

- Ruling: reuse current dedicated checkout — plan Global Constraints and desktop preference explicitly require reuse — no additional isolation, serial implementers prevent collisions. **Coût en cas d’erreur.** Conflit de fichiers si deux auteurs écrivent simultanément dans le checkout.
- Ruling: task 8 ResolvedText is an array shape — shared types and exact sample asserts are authoritative — may add alias PHPDoc, no separate object unless controller revises every consumer. **Coût en cas d’erreur.** Rupture pour un consommateur qui attend un objet.
- Ruling: full-suite intermediate failures caused specifically by deferred consumers in 8–10 must be enumerated, never weakened or ignored; complete dependencies before any deployment — plan declares intermediate commits nondeployable — final gate requires all pass. **Coût en cas d’erreur.** Une régression pourrait être attribuée à tort à une dépendance différée.
- Ruling: browser validation needing new schema is deferred to task 12 with private backup/maintenance; do not run migrations early to satisfy an intermediate browser instruction — global no-live-migration rule is stronger — early UI checks may use existing schema only where valid. **Coût en cas d’erreur.** Un défaut visuel peut être découvert tard sous maintenance.
- Ruling: replace candidate runtime if necessary and permit a small tested literal-interpolation shim around an existing library's public plural-selection API — spec delegates library choice to builder and forbids a homegrown linguistic engine, not narrow repair of demonstrated replacement defects; keep plural grammar/rules entirely in the library — cost if wrong: adapter maintenance or replacing runtime, bounded by regression tests. No private subpath imports or shared-store mutation during translation calls. Add $, pipe, repeated-placeholder and fallback-locale tests.
- Ruling: preserve installed Filament query aliases and array filters, alongside legacy keys where needed — plan's tableFilters wording describes table-state preservation, not a requirement to discard the installed protocol; actual user-visible state governs — cost if wrong: an overly permissive query allowlist or stale state, bounded by strict registered-page validation and real-state tests. Canonical translated path still comes from server; client copies current query/hash only.
- Ruling: use a narrow decorator of Laravel's translation-loader contract for vendor literal-key messages in the '*' JSON namespace, sourced from PHP lang/vendor/{fortify,passkeys}/{locale}/messages.php; delegate ordinary/domain/package loading — preserves PHP-only editable source and vendor auth logic while translating literal messages — cost if wrong: interference with fallback/package loading, bounded by namespace-only adaptation and integration tests. App-owned email view overrides use semantic notifications keys. Validate aliases for every active locale and exclude from frontend exports; unrelated Filament override files need not be mirrored indiscriminately.
- Ruling: task7 strict JSONB fixture assertion compares canonically sorted associative keys instead of insertion order — PostgreSQL JSONB does not preserve object insertion order; spec requires exact values, null presence and locale identity, not map order — cost if wrong: an order-sensitive consumer would be exposed later, but JSON object key order is not a supported contract. Keep strict value/type equality and explicit null presence; never relax to coercive comparison or change stored values for test order.
- Ruling: task8 runtime tests may use a guarded test-only finalized-schema fixture in a random owned schema on gamestackr_testing: run historical+new migration, verify proof, then remove only its old sources/function and mark finalized_at before test writes — source readonly cascades intentionally forbid writes before finalization, while actual command belongs to11 — cost if wrong: fixture/real finalizer drift; task11 must exercise actual finalization and align/reuse the fixture. Do not change TestCase guard, app auto-finalization, public/app schema, or weaken image lifecycle assertions. Controlled race connections must explicitly use same owned schema and guarded testingDB.
- Ruling: new SeriesImage upload filenames use the stored display_name for that image’s own language, falling directly back to scalar series reference name — preserves prior localized image naming and keeps image language explicit; Game filenames always use scalar reference — cost if wrong: changed naming expectation for future uploads, not lost data. Migration and translation-only saves must preserve existing image paths without rename/reencode/remove; cover this boundary. Task8 corrected test expectation after removingEN to retainFR=null, per contract.
- Ruling: extend task8 to minimal reusable forward-only test lifecycle support for existing DatabaseMigrations callers — migration down deliberately refuses destructive reversal, so automatic rollback teardown is incompatible and blocks tests9/10; solve setup/cleanup without conditional testing behavior in migration or guard changes — cost if wrong: test isolation/reset regression; preserve assertions, keep all schemas/DB guarded, verify lifecycle and subsequent tests. Worker to identify exact narrow approach before bulk edits; no silent teardown-failure deferral.
- Ruling: adapt old CatalogDomain translation-row assertions to embeddedJSON ownership: repeated FR/fr writes yield one canonicalkey, edition uniqueness and no automatic editioncreation remain; deleting a series with only its own SeriesImage removes that owner/image, while independent editorial-child protections remain unchanged — old localization rows no longer exist by approved model — cost if wrong: accidental weakening of unrelated deletion rules; preserve those assertions and image lifecycle coverage explicitly.
- Ruling: for explicit sort=name preserve SQL globaldisplayedname order through React rendering using contiguousseriesgroups, with stableuniquekeys even repeatedseries; retain established grouping for chronologicalviews — existing regrouping A/B/A reordered globally sorted results, violating requested displayed-name sort — cost if wrong: repeatedseriesheadings in namesort, bounded by clear grouping and regression/browser checks. Cover A/B/A.
- Ruling: add explicit per-field stop-fallback checkbox for currently absent optional translation fields — raw empty input alone cannot distinguish untouched missingkey from intendedpresentnull; preserve no-op open/save and allow deliberate absence without type-save-clear workaround — cost if wrong: additional form control, bounded by shared TranslationFields UI and presence-sensitive regressions. Include announced_period and removal of locale containing only nullablefield=null; remove-action visibility must inspect keypresence, not truthiness.
- Ruling: replace make init's direct migration with a thin explicit installation command: truly empty schema→temporary maintenance+migrate+real finalizer; already-finalized V1 with no sources→normal migrations; any other existing schema→refuse before migration and direct to backup/audit runbook — fresh installs must be usable without leaving readonly-source cascades, while ordinary init must not silently convert existing data — cost if wrong: stricter init requires manual recovery/runbook after partial installation; preserve previous maintenance state on success, keep maintenance on failure, test all branches in guarded testing schemas. Rollback pairs old backup with previous deployable code, never new-model HEAD.
- Ruling: task12 may update only web-auth/webauthn-lib from5.3.12 to patched5.3.13 with minimum necessary lock changes and auth/passkey+full checks — fresh audit identifies actionable advisories on the authentication path being delivered; maintainer confirms patch release https://github.com/web-auth/webauthn-framework/releases/tag/5.3.13 — cost if wrong: patch-level authentication regression, bounded by compatibility/diff review and existingpasskey/auth suites. No blanket dependencyupdate/forced npm overrides; document remaining unused-exporter advisories precisely.
- Ruling: run the single broad whole-branch review after task12 pre-cutover checks/report are committed, before its actual application migration; review later QA/report/code deltas separately — task12 explicitly requires independent review before cutover, whereas generic SDD says review after all tasks; repository/spec safety ordering controls — cost if wrong: later integration defect outside reviewed delta, bounded by exactSHA delta review plus full/currentCI beforemerge. Do not duplicate wholebranch review or skip operationalbrowser evidence.
- Ruling: fresh installation with maintenance cache backed by an absent databasecache table refuses before writes with a concrete bootstrap procedure, rather than silently switching configured maintenance driver — Laravel cannot read/write that maintenance store before its schema exists; reviewer permits explicit diagnostic — cost if wrong: users choosing this configuration need an extra documented startup step, bounded by no-write regression and preservation of existing maintenance. Default file-backed fresh install remains automatic.
- Ruling: rehearse private application backup restoration only in gamestackr_testing by renaming its existing public schema aside, restoring into temporary public, comparing everytable rows+sequences, migrating/finalizing, then restoring originaltestschema in cleanup — satisfies actualrestore/rollbackproof without destroying prior teststate or usinganotherDB — cost if wrong: testing schema pollution or misdirectedrestore, bounded by repeated current_database guard, no pg_restore --create, isolatedconfig/maintenance/mail, serializedoperations and finallycleanup. Actualexecution waitswholebranchreview. NoappDB usedbytests.

- Ruling: V1 migration synthesizes missing active-language rows without created_at/updated_at (existing columns nullable), representing no historical timestamp; preserve all existing dates, ordinary SyncContentLanguages and strict full-row hashes/cmp — two independent conversions become reproducible without invented dates or weakening proof — cost if wrong: these synthesized reference rows lack creation timestamps; document and test two distinct clocks plus date-tampering refusal.
- Ruling: accept documented mixed-plural/negative-count runtime limits for this delivery — current dictionaries use supported grammar and all application counts are nonnegative, confirmed by finalreview — cost if wrong: future text/count features need a runtime/validator adjustment; retain regression coverage and explicit documentation.
- Ruling: retain current pluralizer dependency with its three disclosed moderate installed-graph npm advisories, without a forced major transitive override — audited application entrypoint has no exporter/glob imports and package has no compatible advertised fix — cost if wrong: future entrypoint changes could expose vulnerable exporter behavior; document exact exposure and require reevaluation when changing imports/dependency.
- Ruling: DE remains an activation fixture and RTL remains a documented future visual-validation requirement — approved delivery languages are FR/EN and adding another language is supported through configuration/files — cost if wrong: another requested production language would need actual translation and visualQA before activation.
- Ruling: validate WebAuthn contracts/callbacks without hardware now and test a real ceremony only if browser/authenticator access permits — no hardware-success claim without execution — cost if wrong: authenticator-specific integration remains an explicitly reported QA limit.
- Ruling: finalreview deferral of browser/Mailpit/actualrestore/cutover is a sequencing gate, not a scope exclusion — task12 still requires execution after corrected-code review and before reopening — cost if wrong: operational defects could remain; keep task12 incomplete until evidence or precise external limitation.
- Ruling: retain documented existing Docker SSR connectivity and optionalfont/buildtiming limitations instead of changing environment scope — realSSR tests and eventualbrowserCSR checks provide distinct evidence — cost if wrong: deployment needing SSR service connectivity needs separate configuration; never label CSR checks as liveSSR proof.
- Ruling: finalreview deferral of PR/main CI is temporary — exacthead tests/ci and resultingmain workflow remain required merge/delivery gates underAGENTS — cost if wrong: untestedremote environment; no merge or completion based on localchecks alone.

Le pont de traductions couvre aussi les résumés de validation Laravel. La mutualisation des fixtures de test conserve leurs transactions et le garde-fou de base, avec retrait du schéma isolé après chaque cas. Une dérive de ces extensions toucherait la langue des erreurs ou l’isolation des tests.
