# Multi-TCG Catalog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Administrer les catalogues, séries, ensembles, éditions linguistiques et sorties des TCG, avec un seeder explicite de données réelles.

**Architecture:** Des modèles Eloquent relationnels portent les identités et contraintes PostgreSQL. Une action d’écriture commune et des règles de domaine partagées sont utilisées par Filament et le seeder. Les images réutilisent la conversion AVIF et les garanties transactionnelles existantes, avec un espace de noms propre au catalogue.

**Tech Stack:** Laravel 13, Filament 5, PostgreSQL 18, Garage, PHPUnit 12 ; outils dans Docker via Make. Aucune dépendance supplémentaire.

**Spec:** `docs/superpowers/specs/2026-11-15-multitcg-catalog-design.md`, complétée par `docs/superpowers/multitcg-compatibility-audit.md`.

## Global Constraints

- Les tests utilisent exclusivement `gamestackr_testing` ; ne jamais neutraliser les garde-fous de `tests/TestCase.php`.
- Réutiliser la branche dédiée `codex/multitcg-catalog-design` et préserver les données, images, noms, slugs et visibilités existants.
- Aucun modèle de carte, variante, tirage, produit, prix ou collection dans cette livraison ; aucune nouvelle page React publique.
- Une traduction d’affichage ne crée jamais une édition imprimée. Une série est facultative. Les catalogues sont configurables par jeu.
- Une seule édition linguistique par couple extension/langue ; une sortie par édition/marché/canal pour le premier lancement.
- Les rattachements d’identité et clés sont immuables après création ; la série d’une extension reste modifiable dans son catalogue.
- Les sorties utilisent les canaux `general`, `specialist`, `prerelease`, `crowdfunding`, `event`, et les états `announced`, `released`, `cancelled`. Aucun changement automatique au passage d’une date.
- Les types d’ensemble sont `main`, `special`, `promo`, `deck`, `reprint` ; les types de regroupement sont `series`, `block`.
- Les nouveaux exemples sont masqués de publication par défaut. Le seeder ne crée aucun utilisateur, ne contacte aucun réseau et n’est pas appelé par `DatabaseSeeder` ou `make init`.
- Le seeder conserve les champs modifiés/vidés et recrée seulement les fiches absentes ; ses associations persistent malgré un renommage du slug d’un jeu.
- L’administration est en français et réservée aux administrateurs vérifiés. Réutiliser les conventions existantes.

## Review Focus

- Une requête falsifiée associe une série d’un autre catalogue : refus serveur et contrainte PostgreSQL (tâche 1).
- Une ancienne fiche en mémoire modifie un rattachement ou rétablit un état incohérent : relecture verrouillée, erreur de validation, aucune écriture partielle (tâche 1).
- Un échec après écriture d’image ou une suppression de parent perd un fichier encore utilisé : rollback/nettoyage après commit et protection des chemins (tâche 2).
- Une action Filament ouverte avant révocation du droit admin reste exécutable : autorisation au moment de l’action, y compris les relations (tâche 3).
- Une relance du seeder après renommage, effacement volontaire ou collision réécrit une fiche utilisateur : associations persistantes, conservation et rollback complet (tâche 4).

---

### Task 1: Modèles et invariants du catalogue

**Files:**

- Create: `database/migrations/2026_11_15_140000_create_tcg_catalog_tables.php`.
- Create: `app/Models/CatalogRecord.php`, `Language.php`, `Market.php`, `Catalog.php`, `Series.php`, `SeriesLocalization.php`, `Expansion.php`, `ExpansionLocalization.php`, `ExpansionEdition.php`, `ExpansionRelease.php`, `ExpansionRelation.php` (tous sous `app/Models/`).
- Create: `app/Actions/Catalog/SaveCatalogRecord.php`, `app/Support/Catalog/CatalogRules.php`, `app/Support/Catalog/CatalogPresentation.php`.
- Modify: `app/Models/Game.php` (relation catalogues et refus de suppression si dépendance).
- Test: `tests/Feature/Catalog/CatalogDomainTest.php` et utilitaires de construction dans `tests/Concerns/BuildsCatalog.php`.

**Interfaces:**

- Consumes: `Game`, conventions de `SaveGame`, `ValidationException`.
- Produces: `SaveCatalogRecord::handle(CatalogRecord $record, array $data): CatalogRecord`, avec annotation générique conservant le type concret ; `CatalogRecord::isEffectivelyVisible(): bool` ; relations Eloquent nommées `game`, `catalog`, `series`, `expansion`, `edition`, `language`, `market`, `localizations`, `editions`, `releases`, `relations` selon le modèle.
- Produces: `CatalogPresentation::name(Series|Expansion $record, string $languageCode): array{name:string,language:?string,fallback:bool}` ; `CatalogPresentation::seriesImage(Series $record, string $languageCode): ?string`.
- Fields: noms de colonnes `key`, `name`, `description`, `sort_order`, `is_visible` ; FK `*_id` ; langue `code`, `name` ; marché `key`, `name` ; catalogue `default_language_id` ; édition `official_code`, `image`, `status` ; sortie `channel`, `released_on`, `announced_period`, `status`, `source_url` ; relation `source_expansion_id`, `target_expansion_id`, `type`, `notes`, `source_url`.

- [x] **Step 1: Write failing domain tests.** Exemple de contrat :

```php
$catalog = $this->catalog();
$otherSeries = $this->series($this->catalog());
$this->expectException(ValidationException::class);
app(SaveCatalogRecord::class)->handle(new Expansion, [
    'catalog_id' => $catalog->id, 'series_id' => $otherSeries->id,
    'key' => 'set', 'name' => 'セット', 'type' => 'main',
]);
```

Ajouter assertions pour duplication extension/langue, unicité sortie, caractères CJK, édition sans sortie, période seule, aucun avancement automatique, sortie publiée rendant l’édition publiée, refus de rétrogradation de l’édition, liens entre jeux interdits, symétrie canonique `related_to`, absence de transitivité, identité immuable, série détachable, suppression bloquée et visibilité héritée. Pour le repli : nom français absent -> nom anglais et `fallback=true`, image française absente -> `null`, sans création d’édition française.

- [x] **Step 2: Run `make test ARGS='tests/Feature/Catalog/CatalogDomainTest.php'`.** Échec attendu sur les nouvelles fonctionnalités absentes ; conserver la preuve RED.
- [x] **Step 3: Implement migration and models.** IDs techniques, uniques scoped et FK restrictives ; composite FK `(series_id,catalog_id)` empêchant une série d’un autre catalogue. Localisations cascade du parent sans image pour l’extension ; sorties cascade édition. Aucun compteur de cartes. Relations requièrent même jeu, deux ensembles différents, source URL et notes. Les codes officiels restent non uniques. Clés ASCII stables proposées si absentes ; noms non latins inchangés. Les tags de langue acceptent BCP 47 dont `zh-Hans`/`zh-Hant` (pas un enum fermé).
- [x] **Step 4: Implement shared writes, deletes and presentation.** Validation des champs, des FK, des uniques et des identités pour les écritures Eloquent ordinaires ; transaction et verrouillage dans l’action pour les changements d’état. Une date utilise un jour civil. Les modifications de sortie et d’édition sérialisent sur le même parent pour préserver les états. Une erreur de collision devient une validation lisible. Les suppressions passent par Eloquent, rendent les dépendances compréhensibles et n’effacent pas de sous-arbre éditorial implicitement. L’état d’édition peut être saisi sans sortie ; une sortie publiée suffit à le publier. Repli d’affichage sans faux visuel ni édition inventée.
- [x] **Step 5: Run focused tests then `make test` and PHPStan.** Formatter avec Pint via Docker. PASS attendu, sans modifier les garde-fous ni les tests existants pour masquer une régression.
- [x] **Step 6: Commit domain and tests.** Revue indépendante de cette tâche avant la suivante.

### Task 2: Images de séries et éditions

**Files:**

- Create: `app/Services/CatalogImages.php`, `app/Observers/CatalogImageObserver.php`, `tests/Feature/Catalog/CatalogImagesTest.php`.
- Modify: `app/Services/GameImages.php`, `app/Actions/Catalog/SaveCatalogRecord.php`, `app/Models/SeriesLocalization.php`, `app/Models/ExpansionEdition.php`, `tests/Integration/GarageStorageTest.php`.
- Optional extraction with one responsibility: `app/Services/AvifImages.php` if composition is clearer than protected hooks in GameImages.

**Interfaces:**

- Consumes: Task 1 action/models; existing GameImages image validation and transaction callbacks.
- Produces: `CatalogImages::diskName(): string`, `disk(): FilesystemAdapter`, `store(UploadedFile $upload, string $name, Connection $connection): string`, `deleteUnused(?string $path): void`, `isManagedPath(string $path): bool`.
- Existing `GameImages` public API must remain compatible. Same disk setting `game-images.disk`, namespace `catalog/{uuid}/{slug}.avif`.
- `SaveCatalogRecord::handle` accepts `image` UploadedFile, current path, null, or omitted for image-bearing models only. Image replacements are immutable revisions; no rename on unrelated title change required for catalogue images.

- [x] **Step 1: Write tests for localized independent images, replacement, explicit removal, rollback and delete.** Assert `image/avif`, `catalog/` namespace, old image retained on outer transaction rollback, new object removed, French and English paths independent. Test malicious foreign path and failed validation leave storage untouched. Test deleting a Series with image localizations cleans children through Eloquent after success. Add a real Garage lifecycle case to existing integration suite, tracking and removing only test UUID objects.
- [x] **Step 2: Run `make test ARGS='tests/Feature/Catalog/CatalogImagesTest.php'` and record RED.**
- [x] **Step 3: Reuse the conversion pipeline and add catalogue lifecycle.** Preserve 5 Mio/4096px, AVIF quality 85, EXIF, SVG sanitization/resource limits and rollback guards. Do not copy the entire conversion implementation. Shared deletion checks both image-bearing catalogue tables. Parent deletion must invoke image cleanup rather than relying solely on SQL cascade. Unrelated Game behavior and legacy paths remain intact.
- [x] **Step 4: Run focused tests, existing image tests and `make test`.** Pint/PHPStan clean. Real Garage test is also run in the final checks after `make up`.
- [x] **Step 5: Commit image support and tests.** Revue indépendante avant Filament.

### Task 3: Administration Filament

**Files:**

- Create resources under `app/Filament/Resources/Catalogs/`, `Series/`, `Expansions/`, `Languages/`, `Markets/` with each resource, `Pages/List*`, `Create*`, `Edit*` and focused schema/table classes as needed.
- Create hidden navigation resource `app/Filament/Resources/ExpansionEditions/ExpansionEditionResource.php` with edit page for managing releases.
- Create relation managers for series/expansion localizations, expansion editions/relations and edition releases under their respective resources.
- Create shared helpers in `app/Filament/Support/Catalog/` for saving/mapping validation errors, fields and deletion notifications, rather than duplicating business logic.
- Create/register catalogue policies in `app/Policies/` or `AppServiceProvider`; modify `GameResource`/`EditGame` for catalogue access and guarded-delete feedback.
- Test: `tests/Feature/Filament/CatalogResourcesTest.php`.

**Interfaces:**

- Consumes Task 1/2 action, models, CatalogImages. All writes use shared domain action; maps errors into page/relation form paths. Policies require `is_admin && hasVerifiedEmail()`.
- Produces French admin navigation: Catalogues, Séries, Extensions et ensembles, Langues, Marchés. Editions accessible from extension; no technical-table navigation flood.

- [x] **Step 1: Write Livewire tests for create/edit main records, localization without printed edition, edition and market releases, image upload, relation validation, filters and rights.** Assert a non-admin/unverified user cannot render pages or execute previously opened mutations. Check immutable controls/server validations and invalid series selection. Check protected deletion yields a visible dependency message rather than an unhandled SQL error. Use installed Filament 5 test API and existing GameResourceTest patterns.
- [x] **Step 2: Run `make test ARGS='tests/Feature/Filament/CatalogResourcesTest.php'` and record RED.**
- [x] **Step 3: Build resources and nested management.** French labels, searchable lists, filters by game/catalog/series/type/visibility where relevant, dependent selectors, catalogue/language context, editable CJK strings, stable keys read-only on edit. Group presentation and publication as existing Game UI. FileUpload uses `storeFiles(false)` and action conversion, never arbitrary persisted paths. Errors from relation actions must display in their own form. Include links to related catalogues from games, editions from expansions and back to parent. Preserve rights on all create/edit/delete/bulk actions.
- [x] **Step 4: Run focused tests, `make test`, Pint and PHPStan.** No dependency/build changes expected; final assets build and real browser check still required.
- [x] **Step 5: Commit admin and tests.** Independent task review before seeder.

### Task 4: Real-data seeder, documented usage and delivery

**Files:**

- Create: `database/seeders/TcgCatalogExamplesSeeder.php`, `database/seeders/data/tcg-catalog-examples.php`, `database/seeders/data/tcg-catalog-sources.php`, `database/seeders/assets/tcg-catalog/` (small verified official AVIF fixtures + provenance).
- Create: `database/migrations/2026_11_15_150000_create_catalog_example_bindings_table.php`, `app/Models/CatalogExampleBinding.php`, `app/Actions/Catalog/SeedCatalogExamples.php`.
- Create: `tests/Feature/Catalog/TcgCatalogExamplesSeederTest.php`, `docs/superpowers/multitcg-catalog-verification.md`.
- Modify: README catalogue usage; spec status approved/implementation complete only after evidence. No DatabaseSeeder call.

**Interfaces:**

- Consumes shared domain writes, image upload interface, existing SaveGame.
- Produces `SeedCatalogExamples::handle(): array{created:int,preserved:int,recreated:int}` and `TcgCatalogExamplesSeeder` console summary. Stable manifest binding key -> type/id persists even if record removed (no cascading deletion of binding). Explicit type whitelist; no arbitrary model from data input.

- [x] **Step 1: Verify and prepare fixtures.** Preserve the 19-ensemble baseline and its dates/names in the spec. Add sourced published examples for Cyberpunk, FaB (WTR/History Pack 1), Riftbound (Origins/OGS), Mythos (Konoha Shidō), FFTCG (Chapter and Opus), SWU and Altered. Record Naruto Bandai announcement as a sourced manifest note, no invented edition. Each factual field/group maps to primary-source URL and consultation date; classification choices are marked editorial. Verify uncertain additional language/date/code fields before writing them, else leave null/absent. Include French/English official series visuals and extension logos, prepared locally as AVIF with provenance (no network at seed runtime). Root may prepare assets/provenance while earlier agents implement; avoid touching application code concurrently.
- [x] **Step 2: Write failing seeder tests.** First run creates real examples across the required games; repeat creates zero duplicates and zero users; names/images/visibility and intentionally null fields stay unchanged; renamed existing game remains bound; deleted example recreated and counted; conflicting key identity triggers rollback with diagnostic. `Http::preventStrayRequests()` plus no HTTP code in seeder. Assert Pokémon JP splits, Chinese catalogs/editions, no false French printing from display localization, OTS/general dates distinct, Foundations series null, no invented Bandai edition. Test existing Games stay visible/unchanged and new games hidden.
- [x] **Step 3: Run `make test ARGS='tests/Feature/Catalog/TcgCatalogExamplesSeederTest.php'` and record RED.**
- [x] **Step 4: Implement deterministic seeding.** Transaction/advisory lock serializes the explicit seed. Resolve binding first, then unique natural identity to adopt only an unambiguous matching record; fail on mismatched relationships. Preserve all existing values, including nulls. Missing records restored with checked dependencies; no deletes. Persist bindings without local hardcoded IDs; use existing game slugs from audit. Store fixture images only when creating their owning records through action/UploadedFile. Restore files on rollback. Report conflicts by exception and summary counts on success. No default execution hook.
- [x] **Step 5: Run seeder tests, `make test`, Pint/PHPStan; document usage and constraints.** The root agent migrates locally and explicitly runs the requested example seeder after independent review and checks; the task implementer only writes to the dedicated test database. No automatic destructive operation. Document exact new record counts and preservation behavior without dumping user data.
- [ ] **Step 6: Verify delivery.** Root runs `make up`, `make check`, `make assets`, `make test-storage`, browser admin path (catalogue/series/edition/releases/image/invalid association), and `git diff --check`. Record evidence and actual limits. Independent task review plus whole-branch review against updated origin/main.
- [ ] **Step 7: Commit, publish PR, wait green CI, squash-merge per AGENTS.md and verify main CI.** Re-review any added delta; no force/admin bypass. Preserve credentials/user data; no public page changes.

## Execution decisions and progress

The user's “maintenant place au développement” approves the design and authorizes execution. Reuse the clean task checkout; a new worktree would duplicate Docker mounts unnecessarily. Execute with fresh implementers and scoped reviews, then a whole-branch review; do not request another go-ahead for already authorized work. The SDD ledger tracks task status, commits, evidence and rulings.
