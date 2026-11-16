# Multilingue complet — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Livrer une application FR/EN entièrement traduite, avec chemins localisés, fichiers de langue centralisés et contenus administrés via Spatie, sans perte de données.

**Architecture:** Un registre de langues et des identités de routes stables pilotent les URL traduites, Fortify et les variantes du panel Filament. Les fichiers Laravel alimentent aussi React via une passerelle existante. Les contenus utilisent JSONB/Spatie, un résolveur explicite de repli et des écritures par langue sous verrou ; les éditions imprimées restent indépendantes.

**Tech Stack:** Laravel 13, Filament 5, Fortify, Inertia 3, React 19/TypeScript, Wayfinder, Vite Plus 1.4, PostgreSQL 18, Garage, Spatie Translatable v6 ; passerelle React candidate `@innoge/laravel-react-i18n` 3.0.1.

**Spec:** [Conception validée](../specs/2026-11-16-localization-design.md).

## Global Constraints

- « Les préfixes canoniques sont `/fr` et `/en`, conformément au choix utilisateur. »
- « FR et EN sont actives au départ ; FR est la langue par défaut et de repli. »
- « Sur une route préfixée, **l’URL fait autorité**, devant cookie et utilisateur. »
- « Les **segments de navigation** sont traduits ; les **identifiants métier** existants restent stables. »
- « Livrer FR et EN complets pour les textes de l’application : public, auth, compte, administration, erreurs applicatives, validation, notifications et e-mails. »
- « Les fichiers PHP sont la source éditable. »
- « Ne pas maintenir deux sources actives de traductions. »
- « Conserver chemins et objets Garage ; la migration ne réencode, ne déplace et ne supprime aucun fichier. »
- « La table `languages` conserve son rôle de référentiel des langues des contenus et éditions ; elle ne pilote pas l’activation des langues du site. »
- « Les besoins RTL demandent en plus une vérification visuelle ; seuls FR/EN sont livrés et validés ici. »
- PHP, Composer et Node dans Docker via Make ; tests exclusivement sur `gamestackr_testing`, sans modifier ses garde-fous. Aucun volume détruit, secret ou donnée locale versionné.
- Réutiliser `codex/localized-routes-and-translations` et le checkout propre actuel. Vérifier `git status` avant toute modification. Aucune fusion sans revue indépendante et CI verte selon `AGENTS.md`.
- Un seul plan : routes, traductions et schéma partagent trop de consommateurs pour des livraisons indépendantes. Les commits intermédiaires ne sont pas des versions à déployer. Aucune migration applicative réelle avant la tâche 12.

## Review Focus

1. Formulaire ou requête de recherche en attente lors d’un changement de langue : conserver les filtres, annuler la visite obsolète et protéger la saisie non sauvegardée — tâches 3, 5 et 6.
2. Ancien lien signé reçu avant déploiement : valider l’URL originale ; refuser une signature modifiée, conserver la destination après connexion/2FA — tâche 4.
3. Deux administrateurs modifient deux langues du même JSON : préserver les deux changements et empêcher une suppression de langue concurrente — tâche 8.
4. Contenu volontairement vidé, texte de langue inconnue et image absente : préserver ces trois états après migration et relance du seeder — tâches 7 à 10.
5. Troisième langue, cache des routes actif et deux panels utilisés successivement : aucune locale ni permission héritée de la requête précédente — tâches 1, 3, 5 et 11.

## Structure et contrats communs

Les chemins ci-dessous sont relatifs à la racine du dépôt. Les dossiers de tests
`tests/Feature/Localization` et `tests/Frontend` contiennent les nouveaux tests.
Pour chaque tâche : écrire les assertions indiquées, constater leur échec pour
la raison attendue, implémenter, les relancer, puis committer les seuls fichiers
concernés après `git diff --check`. Ne jamais neutraliser une assertion existante.

| Zone | Fichiers principaux et responsabilité |
| --- | --- |
| Langues | `config/localization.php`, `app/Support/Localization/Locales.php` : registre et validation ; `SyncContentLanguages.php` : référentiel de contenu. |
| Routes | `RouteDefinitions.php`, `LocalizedRoutes.php`, `RegisterLocalizedRoutes.php` dans `app/Support/Localization` ; `routes/localized.php`, `routes/legacy.php`, `routes/auth.php`. |
| Middleware | `SetSiteLocale.php`, `SetAdminLocale.php`, nouveau `NormalizeLocalizedRedirects.php` : langue et réponses, sans modifier la logique d’authentification. |
| Dictionnaires | `lang/{fr,en}/{common,auth,account,catalog,admin,validation,passwords,notifications,routes}.php` ; exports frontend dérivés et ignorés par Git. |
| Frontend | `resources/js/i18n/provider.tsx`, `resources/js/hooks/use-localized-routes.ts`, `resources/js/lib/localized-navigation.ts`, évolution de `use-locale.ts`. |
| Admin | `app/Support/Localization/AdminPanels.php`, `app/Filament/Concerns/HasLocalizedRoutes.php`, `app/Filament/Support/LocalizedPageRegistration.php`. |
| Contenus | `app/Support/Localization/TranslatedContent.php`, `TranslationPatch.php`, `ContentLanguageUsage.php` ; `app/Actions/Catalog/SaveTranslations.php`. |
| Migration | `app/Console/Commands/AuditLocalization.php`, migrations nouvelles et support versionné `database/migrations/support/LocalizedContentV1.php`. |
| Images | Nouveau `app/Models/SeriesImage.php` ; services et observer catalogue existants adaptés. |

Types partagés à définir dans les fichiers propriétaires, et à réutiliser :

- `LocaleDefinition` (PHPDoc / type TS généré) : `{code, laravelLocale, htmlLang, label, direction}`, tous strings, direction `ltr|rtl`.
- `LocalizedRouteDefinition` : `{key, pathKey, methods, action, middleware, constraints, feature}` ; `feature` nullable, `action` classe/méthode sérialisable.
- `ResolvedText` : `{value: ?string, locale: ?string, fallback: bool}`.
- `TranslationOperation` : `{attribute: string, locale: string, operation: 'set'|'remove', value: ?string}` ; `value` vaut null pour remove.
- `LocalizationAudit` : `{ok: bool, counts: array<string,int>, problems: list<string>}` ; aucune valeur personnelle ni secret dans les rapports versionnés.

Chaque commande ciblée attend un code 0 avec les assertions exécutées. Les
commandes marquées « échec attendu » doivent échouer sur le nouveau contrat,
pas à cause d’un service arrêté ou d’une erreur d’import accidentelle.

### Task 1: Registre des langues et validation des dictionnaires

**Files:** créer `config/localization.php`, `app/Support/Localization/Locales.php`, `TranslationFiles.php`, `app/Console/Commands/CheckLocalization.php`, `SyncContentLanguages.php`, `tests/Feature/Localization/LocaleRegistryTest.php`, `TranslationFilesTest.php` ; créer les fichiers FR/EN listés ci-dessus avec les premières clés ; modifier `config/app.php`.

**Interfaces:** `Locales::all(): array<string,LocaleDefinition>`, `supports(string): bool`, `default(): string`, `definition(string): array` ; `TranslationFiles::validate(): list<string>` et `frontend(string $locale): array<string,string>` ; commande `localization:check` et commande idempotente `localization:sync-languages` (insert manquants, noms existants inchangés).

Assertion repère (`test_active_locales_are_independent_from_content_languages`) :
```php
$this->assertSame(['fr', 'en'], array_keys(Locales::all()));
$this->assertSame('fr', Locales::default());
$this->assertFalse(Locales::supports('ja'));
```

- [ ] Écrire `test_active_locales_are_independent_from_content_languages` : registre `['fr','en']`, défaut `fr`, `ja` présent en base n’active pas `/ja`. `test_sync_works_without_example_seeder` : deux exécutions, deux lignes FR/EN, nom administré préservé.
- [ ] Écrire `test_route_keys_and_placeholders_are_complete` : retirer `auth.login` des routes EN ou changer `{token}` en `{id}` produit un diagnostic précis ; paramètre manquant dans un texte produit une erreur. Vérifier les formes plurielles attendues, pas l’égalité brute du nombre de formes entre langues.
- [ ] Lancer `make test ARGS='tests/Feature/Localization/LocaleRegistryTest.php tests/Feature/Localization/TranslationFilesTest.php'` — échec attendu.
- [ ] Implémenter le registre sans accès DB au boot, la lecture des fichiers et le contrôle des clés ; tester `de` temporaire et une correspondance `pt-BR`/`pt_BR`. Autoriser les clés backend standard Laravel/Fortify même si React ne les consomme pas.
- [ ] Exécuter les tests ciblés et `make artisan ARGS='localization:check'` ; aucune traduction de routes ne peut utiliser un fallback implicite.
- [ ] Commit `feat: centralize locale and translation contracts`.

### Task 2: Passerelle Laravel vers React, testée avant conversion massive

**Files:** créer `app/Console/Commands/ExportFrontendTranslations.php`, `resources/js/i18n/provider.tsx`, `resources/js/i18n/types.ts`, `tests/Feature/Localization/FrontendTranslationsTest.php`, `tests/Frontend/translations.test.ts` ; modifier `package.json`, `package-lock.json`, `vite.config.ts`, `Makefile`, `.gitignore`, `resources/js/app.tsx`, `resources/js/hooks/use-locale.ts`, `resources/js/types/global.d.ts`.

**Interfaces:** `localization:export` produit des JSON plats et `TranslationKey`/`LocaleCode` dans `resources/js/i18n/generated/` (ignoré) depuis `TranslationFiles::frontend()`. `useLocale()` fournit `locale`, `t(key,replacements?)`, `tChoice(key,count,replacements?)`, `date`, `languageName`. Le provider reçoit la locale des props Inertia à chaque navigation.

Assertion repère (`test_frontend_export_excludes_backend_domains`) :
```php
$messages = TranslationFiles::frontend('en');
$this->assertSame('Log in', $messages['auth.login.title']);
$this->assertSame([], array_filter(array_keys($messages), fn ($key) => str_starts_with($key, 'admin.') || str_starts_with($key, 'routes.')));
```

- [ ] Écrire les tests avec `common.items = '{0} Aucun élément|{1} :count élément|[2,*] :count éléments'` et sa version EN : assertions pour 0, 1, 2, interpolation et repli FR ; l’export n’inclut aucune clé `admin.*` ni `routes.*`. Définir aussi `auth.login.title` dans les deux langues.
- [ ] Lancer `make test ARGS='tests/Feature/Localization/FrontendTranslationsTest.php'` — échec attendu, puis installer via `make npm ARGS='install --save-exact @innoge/laravel-react-i18n@3.0.1'`. Vérifier la résolution avec l’alias Vite Plus existant ; ne pas utiliser `--force`, `--legacy-peer-deps` ni changer de build system. Un conflit doit être résolu/documenté avant conversion des vues.
- [ ] Implémenter l’export Laravel avec liste blanche de domaines ; utiliser le runtime de la bibliothèque, sans son exporteur Vite global qui inclurait l’admin. Alimenter ses `files` avec les JSON générés, charger les langues actives sans rendu transitoire de clés brutes. Les noms exacts des fichiers suivent le contrat du runtime vérifié dans le package.
- [ ] Placer la synchronisation des props Inertia dans un composant à l’intérieur de son contexte. Ne pas appeler `usePage()` dans un wrapper situé au-dessus du provider Inertia. Éviter un store SSR partagé concurrent : ne pas ajouter le streaming SSR.
- [ ] Conserver une surcharge transitoire explicitement dépréciée `t(fr: string, en: string)` pour les composants pas encore migrés ; ne pas casser TypeScript à cette étape. La tâche 6 supprime cette surcharge après conversion de tous les appelants.
- [ ] Brancher l’export avant dev, build, frontend tests et TypeScript, et son rafraîchissement en développement sur modification des fichiers PHP. Aucun besoin d’éditer `.env` pour sélectionner les clés exportées.
- [ ] Lancer `make npm ARGS='run test:frontend'`, `make npm ARGS='run types:check'`, `make assets` ; constater un changement FR→EN→FR depuis les props sans texte périmé. Convertir le seul accueil dans cette étape pour éprouver l’intégration ; les autres vues suivent la tâche 6.
- [ ] Commit `feat: share Laravel language files with React`.

### Task 3: Routes localisées, liens typés et compatibilité publique

**Files:** créer les trois classes de routes du tableau, `routes/localized.php`, `routes/legacy.php`, `app/Http/Controllers/LegacyRouteController.php`, les deux fichiers frontend de navigation, `tests/Feature/Localization/LocalizedRoutesTest.php`, `tests/Frontend/localized-navigation.test.ts` ; modifier `routes/web.php`, `routes/settings.php`, `bootstrap/app.php`, `SetSiteLocale.php`, `GameSlug.php`, `HandleInertiaRequests.php`, `PublicGames.php`, `PublicCatalog.php`, `PublicCatalogPresentation.php`, `components/language-switcher.tsx`, `components/catalog/catalog-filters.tsx`.

**Interfaces:** `RouteDefinitions::all(): array<string,LocalizedRouteDefinition>` ; `LocalizedRoutes::url(string $key, array $parameters = [], ?string $locale = null, bool $absolute = false): string` ; `alternates(Request $request): array<string,string>` ; `RegisterLocalizedRoutes::register(): void`. `useLocalizedRoutes()` fournit les fonctions Wayfinder correspondant à la locale, avec `.url`/`.form` et paramètres typés. `localization:export` génère le registre TS d’adaptateurs à partir des locales actives, sans dupliquer les templates d’URL.

Assertion repère (`test_legacy_game_url_keeps_selection`) après création d’un jeu visible `pokemon` :
```php
$this->get('/pokemon?lang=en&edition=ja&page=2')
    ->assertStatus(302)
    ->assertRedirect('/en/pokemon?edition=ja&page=2');
```

- [ ] Écrire les assertions : `GET /fr`, `/en`, `/fr/parametres/profil` enregistré, `/de/connexion` inconnu ; cookie EN ne change pas `/fr`. `GET /pokemon?lang=en&edition=ja&page=2` redirige en 302 vers `/en/pokemon?edition=ja&page=2`, sans `lang`, sans boucle, cache privé.
- [ ] Écrire le test frontend : cible FR→EN conserve `q`, `catalog`, `language`, `edition`, `page`, `#releases` ; une recherche différée est annulée avant le changement. La cible vient d’`alternates`, jamais d’un remplacement de `/fr` dans une chaîne.
- [ ] Exécuter `make test ARGS='tests/Feature/Localization/LocalizedRoutesTest.php'` et `make npm ARGS='run test:frontend'` — échec attendu.
- [ ] Enregistrer les variantes `site.{locale}.{key}` avec chemins littéraux traduits et un default de route dédié `_locale`. Le middleware extrait puis retire ce paramètre technique avant dispatch afin de ne pas décaler les arguments des contrôleurs ou du model binding. L’exécuter avant `SubstituteBindings` lorsque nécessaire, restaurer la locale après la réponse.
- [ ] Réserver l’union des segments traduits et codes actifs dans GameSlug. Enregistrer routes fixes avant jeux, redirects historiques explicites après les routes canoniques. Ne pas faire de catch-all redirigeant des chemins inconnus ou des URL externes.
- [ ] Migrer la génération des liens backend publics et la pagination ; fournir `localizedUrls`, `locale`, `availableLocales`, `direction` et `routeKey` à Inertia. Les endpoints techniques restent exclus. Adapter les anciens tests d’URL au nouveau contrat sans supprimer les vérifications de visibilité.
- [ ] Lancer tests ciblés, `make artisan ARGS='wayfinder:generate --with-form'` et TypeScript. Tester le même inventaire avec cache des routes dans un processus de test à chemins de cache temporaires ; ne pas écraser le cache du serveur de développement.
- [ ] Commit `feat: add translated routes and localized navigation`.

### Task 4: Authentification, préférence persistante et e-mails

**Files:** créer `routes/auth.php`, `app/Http/Middleware/NormalizeLocalizedRedirects.php`, `app/Http/Controllers/LocalePreferenceController.php`, `app/Notifications/LocalizedVerifyEmail.php`, `LocalizedResetPassword.php`, `database/migrations/2026_11_16_100000_add_preferred_locale_to_users_table.php`, `tests/Feature/Localization/LocalizedAuthTest.php`, `LocalizedNotificationsTest.php`, `LocalizedPreferencesTest.php` ; modifier `FortifyServiceProvider.php`, `AppServiceProvider.php`, `bootstrap/app.php`, `User.php`, `CreateNewUser.php`, `AdminLoginController.php`, `HandleInertiaRequests.php`, les contrôleurs Settings et les tests de `tests/Feature/Auth` / `tests/Feature/Settings` concernés.

**Interfaces:** `LocalePreferenceController::__invoke(Request): RedirectResponse` reçoit `locale` et une identité de route cible validée ; écrit cookie + préférence du compte uniquement sur action explicite. `User::preferredLocale(): string` implémente le contrat Laravel des notifications localisées. Les classes de notification conservent signatures et tokens des notifications Laravel, avec liens construits par `LocalizedRoutes`.

Assertion repère (`test_url_does_not_overwrite_saved_preference`) :
```php
$user = User::factory()->create(['preferred_locale' => 'fr']);
$this->actingAs($user)->get('/en')->assertOk();
$this->assertSame('fr', $user->fresh()->preferred_locale);
```

- [ ] Écrire `test_url_does_not_overwrite_saved_preference` : visite `/en` avec préférence FR reste EN sans changer le compte ; POST explicite change le compte ; `locale[]=fr`, langue inconnue et cible externe sont refusés sans écriture.
- [ ] Écrire login/register/reset/verify/confirm/2FA FR et EN : contrôleurs et méthodes identiques, protections CSRF/auth/verified/throttle identiques. Assert registration initialise `preferred_locale`; le profil non vérifié n’obtient pas l’accès admin.
- [ ] Écrire les scénarios de signature : lien historique valide traité sur son URL originale ; modifier chemin, ID ou signature renvoie 403 ; nouveau lien contient le préfixe du destinataire. Deux notifications successives FR/EN ne contaminent pas la locale globale. Aucun sélecteur sur l’action signée.
- [ ] Écrire le scénario `/en/settings/security` invité → connexion → 2FA → même destination. La variante admin est testée à la tâche 5, après enregistrement de ses panels. Vérifier passkeys et `/.well-known/passkey-endpoints` sans changer RP ID/origines.
- [ ] Lancer `make test ARGS='tests/Feature/Localization/LocalizedAuthTest.php tests/Feature/Localization/LocalizedNotificationsTest.php tests/Feature/Localization/LocalizedPreferencesTest.php'` — échec attendu.
- [ ] Appeler `Fortify::ignoreRoutes()` dans le provider applicatif, puis enregistrer le tableau des routes Fortify via ses contrôleurs publics et les feature flags. Couvrir tout l’inventaire installé, y compris passkeys/options, QR code et recovery codes ; conserver les noms historiques pour les alias du module legacy. Aucun code vendor copié/modifié.
- [ ] Implémenter `NormalizeLocalizedRedirects::handle(Request,Closure): Response` pour canonicaliser les seules destinations locales reconnues des réponses Fortify/Settings ; conserver status, flash, erreurs et intended. Ne jamais réécrire une URL contenant une signature ; leur notification/contrôleur traite explicitement le bon lien. Les réponses JSON restent inchangées.
- [ ] Implémenter préférence et notifications ; résolution hors URL : ancien lang valide > compte > cookie > FR. POST legacy traité directement avec mêmes protections, pas redirigé avant traitement. Aucun changement de préférence durable sur simple GET.
- [ ] Relancer les tests ciblés puis `make test ARGS='tests/Feature/Auth tests/Feature/Settings tests/Feature/AdminAccessTest.php'`. Ajouter un test d’inventaire actions/middleware contre Fortify pour détecter une route oubliée lors de mises à jour.
- [ ] Commit `feat: localize authentication and notification flows`.

### Task 5: Une administration, panels linguistiques et routes traduites

**Files:** créer `AdminPanels.php`, `HasLocalizedRoutes.php`, `LocalizedPageRegistration.php`, `resources/views/filament/language-switcher.blade.php`, `tests/Feature/Localization/LocalizedAdminTest.php` ; modifier `AdminPanelProvider.php`, `SetAdminLocale.php`, `User.php`, `HandleInertiaRequests.php`, les ressources `Games/GameResource.php`, `Catalogs/CatalogResource.php`, `Series/SeriesResource.php`, `Expansions/ExpansionResource.php`, `ExpansionEditions/ExpansionEditionResource.php`, `Languages/LanguageResource.php`, `Markets/MarketResource.php`, `Pages/DevelopmentServices.php`, `Widgets/CatalogOverview.php` et `tests/Feature/Filament`.

**Interfaces:** `AdminPanels::all(): array<string,string>` associe panel ID à locale ; `locale(string $panelId): string`, `id(string $locale): string`, `url(string $page, array $parameters, string $locale): string`. Le trait résout `getSlug(?Panel $panel = null): string`, les noms de route et labels sans cache de la première langue. `LocalizedPageRegistration::make(string $pageClass, string $pathKey): PageRegistration` résout les chemins à partir du Panel reçu.

Assertion repère (`test_panel_locale_is_explicit`) :
```php
$this->assertSame(['admin-fr' => 'fr', 'admin-en' => 'en'], AdminPanels::all());
$this->assertSame('en', AdminPanels::locale('admin-en'));
```

- [ ] Écrire les accès `/fr/admin/jeux`, `/en/admin/games`, `/fr/admin/jeux/42/modifier` avec même utilisateur et record ; non-admin/non-vérifié refusés dans les deux panels, panel ID `admin-fake` refusé. Après navigation EN puis FR, labels et actions Livewire restent français.
- [ ] Écrire les tests d’enregistrement de routes avec locale ambiante EN puis FR et cache actif : inventaire identique, `creer/create` et `modifier/edit` corrects, paramètres de record conservés. Changement de langue sur édition conserve le record et passe par le garde de saisie non enregistrée.
- [ ] Ajouter le scénario `/en/admin/games/42/edit` invité → connexion → 2FA → même destination (jeu 42 créé en fixture). Assert réponse Inertia `409` et header `X-Inertia-Location` vers cette destination pour la transition complète vers Filament.
- [ ] Lancer `make test ARGS='tests/Feature/Localization/LocalizedAdminTest.php'` — échec attendu.
- [ ] Refactorer le provider pour enregistrer un Panel par locale via une fabrique commune ; default FR explicite. Affecter middleware de locale persistant Livewire. Vérifier l’appartenance à la liste exacte des panels, puis les permissions actuelles ; conserver les restrictions d’environnement des outils de développement.
- [ ] Déclarer les slugs et routes des pages à partir du panel reçu, y compris pages imbriquées et dashboard. Remplacer les références littérales `filament.admin.*` dans widgets, vues et tests par les API des ressources/panels.
- [ ] Ajouter le sélecteur d’interface, activer `unsavedChangesAlerts()` et vérifier que le lien utilisé déclenche effectivement sa confirmation. Migrer les anciens GET admin via un mapping complet ; préserver `tableFilters`, recherche, tri et page. Les actions de transport Livewire restent à leur URL technique.
- [ ] Relancer tests ciblés, `tests/Feature/Filament`, `AdminAccessTest`, et vérifier manuellement le garde avec une saisie non enregistrée dans le navigateur.
- [ ] Commit `feat: localize Filament panels and resource routes`.

### Task 6: Extraction complète des textes de l’interface

**Files:** modifier les neuf fichiers PHP de chaque langue ; `resources/js/pages/welcome.tsx`, `dashboard.tsx`, `games/show.tsx`, `games/expansion.tsx`, les sept pages `pages/auth`, les trois pages `pages/settings`, layouts et composants applicatifs listés par `rg -l 'useLocale|Connexion|Mot de passe|Créer|Supprimer|Settings|Dashboard' resources/js` ; `app/Filament/Support/Catalog/{CatalogFields,CatalogLabels,CatalogActions,CatalogTables,CreateCatalogRecord,EditCatalogRecord,ListCatalogRecords,LocalizationsRelationManager}.php`, schémas/tables/pages des sept ressources, widgets, vues Filament, `CatalogRules.php`, `SaveGame.php`, `SaveCatalogRecord.php`, Requests et notifications ; créer `tests/Feature/Localization/LocalizedCopyTest.php`.

**Interfaces:** tous les consommateurs utilisent `t(TranslationKey, replacements?)`/`tChoice`, `__()`/`trans_choice()` ; les méthodes de CatalogLabels deviennent `seriesTypes(): array`, `expansionTypes(): array`, `relationTypes(): array`, `statuses(): array`, `channels(): array`, avec clés métier inchangées.

Assertion repère (`test_english_login_title`) :
```php
$this->get('/en/login')->assertOk();
$this->assertSame('Log in', __('auth.login.title', [], 'en'));
$this->get('/en/connexion')->assertNotFound();
```

- [ ] Écrire des assertions représentatives : `/en/connexion` n’est pas une route valide ; `/en/login` fournit un titre anglais, une erreur de validation anglaise et un lien anglais ; admin EN contient les aides traduites. Contrôler les textes accessibles de passkeys, 2FA, menus et boutons.
- [ ] Faire échouer `LocalizedCopyTest` et le contrôle de parité sur une clé retirée volontairement en fixture. Vérifier pluriels 0/1/2 et paramètres avec une valeur contenant `<script>` : aucun HTML non échappé ni `dangerouslySetInnerHTML`.
- [ ] Migrer les appels `t(fr,en)` et les textes en dur par domaine. Garder `lang/vendor` pour les overrides des packages. Les titres dynamiques et messages backend sont résolus au moment de la requête, jamais dans une constante traduite au boot.
- [ ] Migrer tous les liens/actions React vers `useLocalizedRoutes`, y compris formulaires, hooks 2FA, passkeys et suppression de compte. Supprimer l’ancien `localizedUrl(path)`, les ajouts manuels de `lang` et la surcharge transitoire `t(fr,en)`.
- [ ] Lancer `make artisan ARGS='localization:check'`, les tests de copie, frontend et TypeScript. Examiner les occurrences restantes de français/anglais en JSX/PHP : les marques, noms officiels et commentaires ne sont pas des oublis de traduction. Documenter les exceptions réelles.
- [ ] Commit `feat: translate the complete application interface`.

### Task 7: Préparer et tester la conversion des données historiques

**Files:** créer `database/migrations/support/LocalizedContentV1.php`, `app/Console/Commands/AuditLocalization.php`, `tests/Feature/Localization/LegacyContentMigrationTest.php`, `tests/Fixtures/Localization/legacy-catalog.php` ; modifier `composer.json`, `composer.lock`.

**Interfaces:** support versionné `LocalizedContentV1::audit(Connection): LocalizationAudit`, `up(Connection): void`, `verify(Connection): LocalizationAudit`. Commande `localization:audit` en lecture seule, code 1 sur problème, code 0 sinon. Elle sait distinguer ancien schéma et schéma final sans supposer une migration déjà appliquée.

Assertion repère (`test_legacy_null_and_reference_are_preserved`) après conversion de la fixture :
```php
$row = $connection->table('expansions')->where('id', 42)->first();
$this->assertSame('参考名', $row->name);
$this->assertSame(['fr' => null, 'en' => 'English description'], json_decode($row->description, true));
```

- [ ] Écrire une fixture d’ancien schéma avec noms japonais sans langue explicite, plusieurs localisations, description FR null/EN remplie, mêmes chemins d’image référencés, bindings d’exemples par langue et jeux renommés. Dans un schéma temporaire créé uniquement sur `gamestackr_testing`, comparer toutes les valeurs et références avant/après `up()` ; aucun appel au disque autorisé.
- [ ] Écrire l’échec d’audit sur collision de slug traduit et langue inconnue ; aucune écriture même en cas de problème. Vérifier les comptes et la présence des nulls JSON, pas seulement le nombre total de lignes.
- [ ] Lancer `make test ARGS='tests/Feature/Localization/LegacyContentMigrationTest.php'` — échec attendu. Installer Spatie via `make composer ARGS='require spatie/laravel-translatable:^6.0 --no-interaction'` ; contrôler le lockfile et la compatibilité Laravel 13 sans mise à jour générale des dépendances.
- [ ] Implémenter la migration versionnée : `display_name` JSONB sur Game/Catalog/Series/Expansion/Market ; descriptions existantes Game/Catalog renommées `reference_description`, nouvelles descriptions JSONB sur les quatre modèles ; période existante renommée `reference_announced_period`, nouveau `announced_period` JSONB ; JSON vides `{}`, noms de référence inchangés.
- [ ] Copier les locales connues dans les JSON, descriptions nulles incluses ; créer `series_images` avec FK série/langue, chemin et unicité série/langue. Copier les images sans réencodage ; conserver temporairement les tables sources en lecture seule pour vérification.
- [ ] Étendre les bindings d’exemples avec locale nullable et index `UNIQUE NULLS NOT DISTINCT(record_type,record_id,locale)`. Transformer les bindings de localisation en parent+locale avant toute suppression de source ; garder le manifest_key. Pour une ancienne localisation déjà supprimée, retrouver le parent uniquement via la liaison de sa clé de manifeste parente, jamais via un nom/slug mutable ; si cette liaison manque, l’audit bloque avec cette clé précise.
- [ ] Prévoir une vérification persistée de la conversion et une procédure de retour avant réouverture ; pas de rollback supprimant silencieusement les nouvelles traductions. Le support de migration est versionné et ne dépend pas des futurs presenters ni des casts Eloquent.
- [ ] Relancer la suite de migration ; commit `feat: prepare verified migration of localized content`. À ce stade, ne pas enregistrer la migration de bascule dans le répertoire exécuté automatiquement et ne pas migrer la base applicative.

### Task 8: Activer les modèles Spatie et les écritures par langue

**Files:** créer `app/Support/Localization/{TranslatedContent,TranslationPatch,ContentLanguageUsage}.php`, `app/Actions/Catalog/SaveTranslations.php`, `app/Models/SeriesImage.php`, `database/migrations/2026_11_16_110000_migrate_localized_content.php`, `tests/Feature/Localization/TranslatableContentTest.php`, `TranslationConcurrencyTest.php` ; modifier Game/Catalog/Series/Expansion/Market/ExpansionRelease/Language/CatalogExampleBinding, `CatalogRecord.php`, `SaveCatalogRecord.php`, `SaveGame.php`, `CatalogRules.php`, `AppServiceProvider.php`, `CatalogImages.php`, `CatalogImageObserver.php`.

**Interfaces:** `TranslatedContent::resolve(array $translations, array $locales, ?string $reference = null): ResolvedText`, `field(Model $record,string $attribute,string $locale): ResolvedText`. `TranslationPatch::between(array $original,array $pending): list<TranslationOperation>` ; `SaveTranslations::handle(Model $record,string $locale,array $values,bool $remove = false): Model`. `ContentLanguageUsage::assertDeletable(Language): void` et `lock(array $codes): void`.

Assertion repère (`test_explicit_null_stops_fallback`) :
```php
$this->assertSame(
    ['value' => null, 'locale' => 'fr', 'fallback' => false],
    TranslatedContent::resolve(['fr' => null, 'en' => 'English'], ['fr', 'en'], 'Base'),
);
```

- [ ] Écrire les assertions exactes de repli : `resolve(['en'=>'English'],['fr','en'],'Base')` donne `{value:'English',locale:'en',fallback:true}` ; `resolve(['fr'=>null,'en'=>'English'],['fr','en'],'Base')` donne `{value:null,locale:'fr',fallback:false}`. `display_name` vide est refusé ; `description` vide devient null présent.
- [ ] Tester deux instances chargées avant sauvegarde : A modifie FR, B modifie EN, les deux survivent ; retirer une locale n’efface pas les autres. Tester les accès Eloquent ordinaires comme `setTranslation()->save()` et le service, pas seulement les formulaires.
- [ ] Tester suppression de langue active, langue utilisée seulement par JSONB, locale de contenu inactive côté site, et course écriture/suppression sur connexions/processus distincts à la base de test. Vérifier que l’opération concurrente ne laisse jamais de clé sans langue référencée.
- [ ] Lancer les tests ciblés — échec attendu ; ajouter la migration fine appelant le support de la tâche 7 et synchronisant les langues actives. Appliquer uniquement via les tests à ce stade.
- [ ] Ajouter HasTranslations aux champs spécifiés, conserver les nulls explicitement, exposer les JSON bruts par `getTranslations()`. Le résolveur applique les règles métier au lieu du fallback arbitraire du package. Garder `name` scalaire et son rôle dans les noms de fichiers.
- [ ] Adapter les pipelines de sauvegarde existants : calculer les opérations par locale depuis les valeurs originales, verrouiller les langues dans un ordre stable puis les entités en respectant le verrou édition→sortie, recharger les valeurs récentes et appliquer uniquement le delta. Valider JSON/locale/champs avant persistance ; ne pas remplacer tout le JSON d’un modèle périmé.
- [ ] Adapter cycle de vie de SeriesImage, suppression d’une série et recherche des références d’images. La copie vérifiée vers SeriesImage reprend toutes les références ; le runtime final ne consulte plus les anciennes tables et ne déclenche aucun observer en les retirant. Aucun fallback d’image entre langues.
- [ ] Relancer tests ciblés et `tests/Feature/Catalog/CatalogImagesTest.php`, puis commit `feat: persist translatable content without lost updates`. Les consommateurs sont finalisés dans les tâches 9–10 avant tout déploiement.

### Task 9: Présentation, recherche et tri sur les traductions

**Files:** modifier `PublicGames.php`, `PublicCatalog.php`, `PublicCatalogPresentation.php`, `CatalogPresentation.php`, `PublicGameController.php`, `resources/js/types/{game,catalog}.ts`, `pages/games/{show,expansion}.tsx`, composants catalogue ; créer `tests/Feature/Localization/TranslatedCatalogTest.php` ; adapter `tests/Feature/Catalog/PublicCatalogTest.php`, `CatalogDomainTest.php`, `PublicGameTest.php`, `GameDetailsTest.php`.

**Interfaces:** DTO publics gardent `name: string` et `description: string|null` ; chaque texte traduit dispose de sa provenance (`nameLanguage/nameFallback`, `descriptionLanguage/descriptionFallback`, équivalents nécessaires pour période). `TranslatedContent::field` est l’unique résolveur ; `CatalogPresentation` délègue pour maintenir ses appelants, sans seconde règle.

Assertion repère (`test_missing_translation_does_not_change_edition`) avec une fixture visible et édition JA :
```php
$this->get('/fr/pokemon/international/base-set?edition=ja')
    ->assertInertia(fn (Assert $page) => $page
        ->where('expansion.selectedEdition.language.code', 'ja')
        ->where('expansion.description', null));
```

- [ ] Écrire les scénarios : description FR null reste absente ; FR manquant puis JA du catalogue puis référence ; données Game/Catalog/Market résolues FR/EN ; référence inconnue sans faux attribut `lang`. Aucun objet JSON brut dans les props.
- [ ] Tester tri affiché avec égalités, recherche accentuée et caractères `%`, `_`, antislash traités littéralement ; résultats masqués exclus de recherche et counts. Pagination toujours 36, tri final déterministe ; recherche d’une traduction japonaise sans interface japonaise active fonctionne.
- [ ] Tester sélection d’édition inchangée : demandée visible > langue du catalogue > première visible selon ordre existant ; logo uniquement de l’édition sélectionnée, indicateur de repli conservé.
- [ ] Lancer `make test ARGS='tests/Feature/Localization/TranslatedCatalogTest.php'` — échec attendu, puis remplacer les jointures anciennes par expressions JSONB paramétrées. Extraire si nécessaire `app/Support/Catalog/LocalizedCatalogQuery.php` pour partager la règle de tri SQL avec les tests de résolution.
- [ ] Résoudre les textes des DTO via le résolveur et charger les relations utiles sans N+1 ; supprimer le chargement des anciennes localisations. Afficher les replis sans inventer de description ou de langue pour les références historiques.
- [ ] Relancer tests ciblés et catalogue public, frontend/types ; commit `feat: present and search translated catalog content`.

### Task 10: Édition des contenus dans Filament et seeder conservateur

**Files:** créer `app/Filament/Support/Catalog/TranslationFields.php`, `app/Filament/Resources/Series/RelationManagers/ImagesRelationManager.php`, `tests/Feature/Localization/TranslatedAdminFormsTest.php`, `TranslatedExamplesTest.php` ; modifier les schémas Game/Catalog/Series/Expansion/Market/ExpansionEdition et ReleasesRelationManager, pages de sauvegarde, tables et sélecteurs de référentiels, `SeedCatalogExamples.php`, `database/seeders/data/tcg-catalog-examples.php`, `CatalogExampleBinding.php`, `tests/Feature/Catalog/TcgCatalogExamplesSeederTest.php` ; supprimer les deux relation managers de localisations et leur base lorsqu’ils n’ont plus d’appelant.

**Interfaces:** `TranslationFields::schema(string $modelClass): array<Component>` déclare langue de contenu et valeurs enregistrées sans fallback ; sauvegarde via `SaveTranslations::handle`. Les champs de référence restent dans une section distincte, éditable ; enlever une traduction est une action différente de vider une description.

Assertion repère (`test_saving_french_keeps_japanese`) avec `$expansion` contenant la traduction JA :
```php
SaveTranslations::handle($expansion, 'fr', ['display_name' => 'Nom français', 'description' => null]);
$this->assertSame('日本語', $expansion->fresh()->getTranslation('display_name', 'ja', false));
$this->assertArrayHasKey('fr', $expansion->fresh()->getTranslations('description'));
```

- [ ] Écrire le scénario admin EN éditant une traduction JA, puis FR : les champs montrent seulement leur langue, la référence reste modifiable, vider FR conserve la clé null, retirer FR supprime ses clés, EN/JA inchangées. Les valeurs de repli ne sont jamais enregistrées par simple ouverture/sauvegarde.
- [ ] Écrire les scénarios seeder après migration : deux langues du même parent sans collision d’unicité ; traduction existante vidée et image absente préservées ; traduction supprimée recréée ; jeu renommé reconnu par binding ; absence de republication ou de réimport d’un fichier déjà présent.
- [ ] Lancer les deux suites — échec attendu ; implémenter les formulaires via les helpers communs, en conservant les identités fixes et les contraintes de visibilité. La langue d’interface est indépendante du choix de contenu.
- [ ] Adapter le manifeste et les bindings parent+locale ; distinguer existence d’une traduction (nom enregistré) et absence volontaire de champs facultatifs. Les nouveaux exemples restent masqués. Ajouter les traductions des nouvelles aides FR/EN dans les fichiers existants.
- [ ] Compléter les listes, recherche admin, titres, options de parents et labels de langues via le résolveur/Intl, avec référence explicite lorsque nécessaire. Autoriser correction/effacement des `reference_description` et `reference_announced_period`.
- [ ] Relancer les tests ciblés, `tests/Feature/Filament` et `TcgCatalogExamplesSeederTest.php` ; commit `feat: manage multilingual content and preserve seeded edits`.

### Task 11: Nettoyage vérifié, métadonnées et ajout d’une langue

**Files:** créer `app/Support/Localization/PublicMetadata.php`, `app/Console/Commands/FinalizeLocalization.php`, `tests/Feature/Localization/LocalizationFinalizationTest.php`, `LocalizedMetadataTest.php`, `ThirdLocaleTest.php`, `docs/localization.md` ; modifier `resources/views/app.blade.php`, layouts React, middleware Inertia, README, Makefile ; supprimer `SeriesLocalization.php`, `ExpansionLocalization.php` et leurs références runtime une fois retirées des tâches précédentes.

**Interfaces:** `PublicMetadata::for(Request): array{canonical:?string,alternates:array<string,string>,robots:?string}` ; commande `localization:finalize --dry-run` / `localization:finalize` retire uniquement les tables historiques après vérification de migration, maintenance active et absence de dépendances runtime. La simulation n’écrit rien.

Assertion repère (`test_french_page_has_reciprocal_alternate`) :
```php
$this->get('/fr')->assertSee('hreflang="en"', false)->assertSee('hreflang="x-default"', false);
$this->get('/en')->assertSee('hreflang="fr"', false);
```

- [ ] Tester que la finalisation refuse une copie incomplète ou une divergence avant bascule, ne supprime aucun objet disque, et se relance sans effet après succès. Préserver les rapports nécessaires au contrôle, sans exiger une égalité avec les tables sources après de nouvelles écritures autorisées : la preuve de conversion est établie avant réouverture.
- [ ] Tester canonical FR/EN et alternates réciproques, `x-default` FR ; aucune fuite de token/signature et absence d’alternates indexables en auth/admin. `/fr/pokemon?page=2` conserve son identité de page. HTML `lang` et `dir` corrects après navigation.
- [ ] Fixer la politique SEO : pages catalogue avec filtres/recherche/édition explicitement sélectionnés en `noindex,follow`, canonical de la même sélection normalisée sans paramètres de suivi ; pages catalogue sans ces filtres indexables, pagination conservée. Les autres pages suivent la spécification.
- [ ] Tester une langue DE temporaire avec fichiers complets et registre modifié dans la fixture : routes publiques/auth/admin, export TS, switcher et repli fonctionnent avec route cache ; aucun composant ne contient une liste FR/EN codée en dur. Une clé de route manquante bloque son activation.
- [ ] Lancer les suites ciblées — échec attendu, puis implémenter metadata, finalisation et documentation des commandes d’activation, de migration et de retour avant réouverture.
- [ ] Ajouter les contrôles de traduction aux commandes `make check` et l’export aux chemins de build pour que CI et installation propre les exécutent. Dans `make check`, générer les routes Wayfinder puis l’adaptateur/export avant les tests frontend et TypeScript ; ne pas dépendre d’artefacts présents d’une exécution précédente. Documenter tous les alias historiques restants et leur raison.
- [ ] Relancer les suites ciblées et `localization:check` ; commit `feat: finalize localization contracts and metadata`.

### Task 12: Vérification complète, bascule et publication

**Files:** créer `docs/superpowers/localization-verification.md` ; ajuster uniquement les défauts constatés ; utiliser le modèle de PR `.github/pull_request_template.md`.

**Interfaces:** les commandes existantes `make check`, `make assets`, `make up`, `make test-storage` et le workflow `tests / ci` restent les critères de livraison ; aucun contrôle anticipé n’est indiqué réussi.

- [ ] Vérifier le diff complet contre `origin/main`, y compris les nouveaux fichiers et dépendances verrouillées. Chercher les anciennes API `t(fr,en)`, ajouts `lang`, anciens modèles, URLs et noms de route codés en dur ; examiner chaque reste au lieu de l’ignorer globalement.
- [ ] Exécuter `make check`, `make assets`, `make up`, puis `make test-storage`. Vérifier installation neuve et migration historique sur la base de test ; ne pas exécuter `make init` localement avant l’audit car cette commande applique les migrations réelles.
- [ ] Faire relire l’ensemble de la branche par un agent distinct avec exigences, plage de commits et résultats. Corriger les défauts importants, relancer les contrôles affectés et faire relire le delta. Conserver le SHA revu.
- [ ] Sur l’installation existante, lancer `localization:audit` avant maintenance. En cas de collision ou anomalie, produire le rapport et ne pas renommer ni masquer des fiches automatiquement. Avec audit sain : maintenance + arrêt des workers, sauvegarde cohérente privée, migration, contrôle d’équivalence, simulation puis finalisation ; maintenir la maintenance pendant le contrôle navigateur suivant. Ne jamais effacer un volume ou un original Garage. Le runbook décrit les commandes exactes et la restauration pré-réouverture.
- [ ] Après migration, vérifier dans le navigateur via l’accès temporaire de maintenance : public/auth/compte/admin FR et EN, desktop/mobile, erreurs, navigation arrière, filtre retardé, formulaire non sauvegardé, édition de contenu dans une autre langue. Tester les notifications via Mailpit seulement, sans envoi externe. Ne pas utiliser l’ancienne base avec le nouveau code pour prétendre vérifier ces parcours. Ne rouvrir et relancer les workers qu’après réussite ; garder le secret temporaire hors Git et des rapports. Noter les limites effectives, dont les interactions matérielles passkeys si elles ne sont pas réalisables.
- [ ] Mettre à jour le rapport avec les résultats réels et le SHA revu ; commit des seuls fichiers autorisés. Toute correction de code supplémentaire nécessite revue du delta et tests applicables.
- [ ] Actualiser `origin/main`, intégrer sans réécrire l’historique si nécessaire, revérifier et faire relire le delta éventuel. Pousser seulement la branche de travail, ouvrir la PR avec problème/résultat/tests et revue indépendante. Attacher la PR au chat via l’outil de l’app.
- [ ] Attendre `tests / ci` réussi sur le SHA courant ; vérifier PR non brouillon, discussions résolues, pas de changement demandé, branche à jour et tête égale au SHA revu. Fusionner uniquement avec `gh pr merge <numero> --squash --match-head-commit <sha-revu>` ; aucun `--admin`, `--force` ou auto-merge armé avant vérification.
- [ ] Vérifier `MERGED` et attendre le workflow `tests` du commit résultant sur `main`. Si succès, synchroniser seulement un checkout propre ; fournir liens PR/CI et limites éventuelles. En cas d’échec, préparer une correction par PR sans réécrire `main`.

## Contrôle du plan avant exécution

- [x] Les décisions `/fr`, chemins traduits, FR/EN partout et séparation édition/interface ont une tâche propriétaire.
- [x] Les fichiers de langue, routes, auth, panels, contenus, images, seeder, caches, SEO, ajout de langue et retour de migration sont couverts.
- [x] Les signatures consommées par les tâches correspondent aux contrats déclarés ; les exemples de tests utilisent les valeurs exactes de la conception.
- [x] Les cinq risques de Review Focus sont rattachés à des assertions concrètes.
- [x] Le découpage reste un plan de décisions et de tests ; il ne recopie ni Fortify ni les corps des futures classes.

## Éléments vérifiés pendant la planification

Le registre npm consulté le 16 novembre 2026 publie `@innoge/laravel-react-i18n`
3.0.1 avec React 18/19 et un peer Vite 6/7/8 ; sa documentation présente un
runtime compatible avec les clés/pluriels Laravel. Le projet utilise toutefois
l’alias `vite` vers `@voidzero-dev/vite-plus-core` 1.4.0 : la tâche 2 doit encore
prouver la résolution npm et le build, sans ignorer les contraintes de peers.
Sources : [package](https://www.npmjs.com/package/@innoge/laravel-react-i18n),
[documentation du mainteneur](https://github.com/InnoGE/laravel-react-i18n).

Seules des lectures et de la documentation ont été réalisées à ce stade.
Les cases de réalisation et leurs validations restent toutes ouvertes.
Le choix d’exécution (directe dans ce chat ou par sous-agents avec revue de
chaque tâche) reste à confirmer après lecture du plan.
