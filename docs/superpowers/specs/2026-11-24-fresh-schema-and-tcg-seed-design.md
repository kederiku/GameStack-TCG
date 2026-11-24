# Schéma neuf et catalogue TCG réel

Date : 2026-11-24. Branche : `codex/fresh-schema-and-tcg-seed`.
Statut : proposition à faire approuver avant implémentation.

Relecture de conception : sous-agent indépendant `fresh_schema_builder_check`,
2026-11-24 ; conclusion « nothing important left ». Cette vérification porte sur
la possibilité de préparer un plan ; la revue du code reste à faire après
implémentation.

## Intention et décisions de l’utilisateur

Repartir sur une installation propre après la refonte multilingue, sans continuer
à maintenir la conversion de données dont le propriétaire n’a plus besoin.

L’utilisateur demande une nouvelle branche, la suppression des données existantes
et des données de test, le retrait du code d’adaptation historique et un seed avec
des données de vrais TCG. Il a confirmé « non supprime tout » lorsque la question
portait sur la conservation des comptes, administrateurs et images. Il a choisi
les **migrations Laravel réécrites** plutôt qu’un instantané SQL PostgreSQL.

La suppression est autorisée. L’approbation attendue porte sur cette conception,
pas sur une nouvelle autorisation d’effacer les mêmes données.

## Approches considérées

1. **Migrations Laravel réécrites, retenues par l’utilisateur.** Décrire directement
   le schéma final par domaines, sans créer puis convertir les anciennes tables.
   Le schéma reste lisible en PHP et les prochaines évolutions utilisent des
   migrations incrémentales ordinaires. En contrepartie, cette version exige une
   réinitialisation des installations reposant sur l’ancien historique.
2. **Instantané SQL PostgreSQL, non retenu.** Charger un schéma initial versionné
   puis les migrations suivantes. Le point de départ serait compact, mais moins
   commode à comprendre et à modifier à la main que les migrations Laravel.

## État actuel et problème

- L’installation appelle `localization:install`, qui migre un schéma historique,
  vérifie sa conversion puis appelle `localization:finalize`.
- `LocalizedContentV1`, ses audits, reçus, déclencheurs et commandes existent pour
  conserver puis démontrer l’équivalence des anciennes données.
- Des traits de tests créent un schéma isolé et rejouent cette finalisation pour
  contourner une migration dont le retour arrière est volontairement interdit.
- `DatabaseSeeder` crée actuellement `Test User` / `test@example.com`.
- Le seed facultatif de catalogue contient déjà des données sourcées pour douze
  jeux, mais les masque et possède un mécanisme de bindings destiné à préserver
  les fiches historiques, même après renommage ou suppression.
- Deux commandes migrent les anciennes images vers AVIF puis vers S3.

Ces chemins historiques deviennent inutiles avec une remise à zéro. Les règles
actuelles de traduction, de validation métier et de stockage restent nécessaires.

## Schéma initial

Remplacer l’historique actuel par un petit ensemble cohérent de migrations Laravel
de création, groupées par domaine : comptes et authentification, cache et files
de travaux, jeux, catalogue TCG. Les noms exacts et le découpage des fichiers sont
laissés à l’implémentation ; ils doivent distinguer clairement le nouvel historique.

Les tables sont créées directement dans leur forme actuelle :

- utilisateurs avec administration, préférence de langue, 2FA, passkeys, sessions
  et réinitialisation de mot de passe ;
- cache, verrous, jobs, lots et jobs en échec ;
- jeux, langues de contenu, marchés, catalogues, séries, images de séries,
  extensions, éditions physiques, sorties et relations entre extensions ;
- traductions Spatie dans leurs colonnes JSONB dès la création, avec `{}` comme
  valeur initiale lorsque c’est le contrat actuel.

Conserver les clés étrangères, contraintes d’unicité et règles actuelles, notamment
`lower(languages.code)`, le rattachement d’une série au catalogue de son extension
et les contraintes des relations entre extensions. Chaque migration a un `down()`
cohérent, dans l’ordre inverse des dépendances.

Ne plus créer les tables `series_localizations`, `expansion_localizations`,
`localization_conversion_verifications` et `catalog_example_bindings`, ni leurs
triggers, index ou fonctions de conversion. Supprimer leurs modèles et références
exécutables devenus inutiles.

Les champs `name`, `reference_description` et `reference_announced_period` restent
des références éditoriales courantes : ils sont encore modifiables dans Filament
et servent au repli des traductions. Les conserver ne maintient pas une conversion
historique. Le seed écrit néanmoins les textes localisés dans les colonnes JSONB.

Les migrations définissent le schéma, sans données d’exemple. Après migration,
`make init` appelle la synchronisation des langues actives, actuellement FR/EN,
afin que l’administration puisse enregistrer ses traductions dès l’installation.

## Suppressions et simplifications du code

Supprimer :

- la conversion `LocalizedContentV1` et sa documentation opérationnelle ;
- `localization:install`, `localization:audit`, `localization:finalize` ;
- `games:convert-images-to-avif` et `games:copy-images-to-s3` ;
- les fixtures et tests exclusivement consacrés à ces conversions ;
- `SeedCatalogExamples`, `TcgCatalogExamplesSeeder`, `CatalogExampleBinding` et
  la protection de suppression de langue fondée uniquement sur ces bindings ;
- les traits de tests `ForwardOnly*` et l’étape de finalisation du schéma de test.

Conserver les tests du produit. Employer les mécanismes Laravel ordinaires pour
les tests transactionnels ou avec migrations. Les tests de concurrence et ceux
qui manipulent un schéma ou le stockage réel gardent, si nécessaire, un petit
outil d’isolation de schéma, indépendant de toute conversion historique.

Conserver les fonctions multilingues actuelles : `/fr` et `/en`, administration,
authentification, e-mails, fichiers de langue, export frontend, Spatie, validation
des locales, synchronisation des langues et protection contre les écritures
concurrentes. Les alias d’anciennes URL ne convertissent pas des données : leur
suppression ne fait pas partie de cette tâche.

Conserver le traitement AVIF des **nouveaux uploads**, le stockage Garage,
la suppression transactionnelle des images et les contrôles de chemins.

## Nouveau seed

### Contenu

`DatabaseSeeder` appelle les seeders de références et de catalogue réels. Il ne
crée aucun utilisateur, administrateur, compte fictif, prix, collection ou carte
individuelle : ces trois derniers domaines ne font pas partie du modèle actuel.

Reprendre le périmètre du manifeste existant : Pokémon, Magic: The Gathering,
One Piece, Yu-Gi-Oh!, Disney Lorcana, Cyberpunk TCG, Flesh and Blood, Riftbound,
Naruto Mythos, Final Fantasy TCG, Star Wars: Unlimited et Altered. Revalider les
faits auprès des éditeurs avant leur reprise ; ne pas importer de fait non
confirmé pour atteindre un quota. L’objectif est un échantillon représentatif,
pas un catalogue exhaustif ni un synchroniseur avec des API externes.

Le contenu comprend langues, marchés, catalogues, séries quand elles existent,
extensions, éditions imprimées, sorties documentées et relations explicitement
éditoriales. Les variantes régionales réellement distinctes restent distinctes.
Une traduction d’interface n’invente jamais une édition physique dans cette langue.

Corriger le slug historique mal orthographié `naturo-mythos` en `naruto-mythos`.
Ne pas créer une édition d’un jeu uniquement annoncé si sa langue d’impression
n’est pas confirmée. Les dates, codes, éditeurs, descriptions et années absents
des sources restent absents ; les textes éditoriaux sont identifiés comme tels.

Les entités importées sont visibles, de sorte que le catalogue est consultable
immédiatement après le seed. Les noms disponibles sont écrits directement dans
les dictionnaires de traduction. Les libellés de navigation internes et marchés
reçoivent FR/EN ; les noms officiels de produits respectent leur langue d’origine
et utilisent les replis existants lorsqu’une traduction officielle manque.

### Structure et réexécution

Choix proposés par l’agent :

- Un point d’entrée `TcgCatalogSeeder`, complété d’un seeder de langues réutilisable.
  Les données sont des fichiers PHP de tableaux organisés par jeu, séparés des
  sources et de la logique d’écriture. Les traductions figurent directement dans
  les attributs actuels ; aucune ligne ne simule une ancienne localisation.
- Le fichier de sources conserve les URL, dates de consultation réelles et notes
  factuelles. Aucune requête réseau n’a lieu pendant le seed ou les tests.
- Utiliser les identifiants naturels du modèle : code de langue insensible à la
  casse, clé de marché, slug de jeu, clé dans son parent, langue d’édition,
  édition/marché/canal pour une sortie. Aucun registre de bindings persistant.
- La réexécution est déterministe pour ces identifiants : pas de doublon ; les
  champs explicitement fournis par le seed sont remis aux valeurs du manifeste.
  Les fiches absentes du manifeste et les traductions non fournies ne sont pas
  supprimées. Un slug renommé hors manifeste devient une fiche indépendante :
  le seed n’effectue aucune réconciliation historique.
- Les écritures passent par les règles métier existantes et une transaction ;
  un échec annule les écritures et nettoie les nouveaux fichiers créés. Sérialiser
  les exécutions concurrentes du même seed sans verrouiller durablement le site.
- Réutiliser les deux visuels officiels déjà versionnés, après vérification de
  leur provenance. Ils sont recopiés comme nouveaux fichiers via les services
  de stockage actuels. Une réexécution sans modification ne recrée pas les mêmes
  images. Les autres fiches peuvent utiliser l’affichage sans image existant.

Le seed n’est pas un outil d’administration quotidienne. Sa capacité à rétablir
les champs fournis doit être clairement annoncée dans le README.

### Commandes

- `make init` installe, migre et synchronise les langues, sans catalogue ni compte
  fictif et sans effacement automatique.
- `make seed` lance explicitement `db:seed`, une fois les services démarrés.
- La remise à zéro de cette tâche recrée la base puis exécute ce seed.
- `make admin` reste la création interactive d’un administrateur. Aucun mot de
  passe prédéfini ni compte de remplacement n’est fourni par le seed.

## Remise à zéro locale autorisée

Il s’agit d’une opération ponctuelle sur l’environnement Docker de ce projet.
Ne pas ajouter un bouton destructif à Filament ni lancer un effacement à chaque
installation. Aucun volume Docker n’est supprimé.

Après validation du code et du schéma sur `gamestackr_testing`, arrêter les
écritures applicatives et les workers. Vérifier les destinations effectives avant
l’opération : base applicative locale `gamestackr`, base `gamestackr_testing`,
bucket Garage de ce projet. Refuser une destination distante ou étrangère au
projet ; toute anomalie doit être résolue avant la purge.

Effacer les anciens enregistrements applicatifs, comptes compris, sessions,
passkeys, tokens, jobs, cache et schémas temporaires de cette tâche dans ces deux
bases. Recréer le schéma final et charger seulement le nouveau seed dans la base
applicative. La base de tests finit sans données métier de test persistantes.

Effacer les anciens objets du bucket d’images du projet, leurs copies locales,
les uploads temporaires, résidus de faux disques de tests, sessions et caches
de fichiers, journaux applicatifs et messages Mailpit de cet environnement.
Les nouveaux fichiers du seed sont ensuite les seuls médias de catalogue.

Le nettoyage inclut les archives locales de migration de ce projet sous
`storage/app/private/localization-0CQk21Qb/` et les anciens originaux sous
`storage/app/private/game-images-before-avif/` ; ils contiennent encore les
données dont la suppression a été demandée. Ne pas générer une nouvelle archive
permanente de ces données. Ne pas effacer d’autres sauvegardes hors du projet.

Préserver l’environnement de fonctionnement : `.env`, clé applicative,
configuration et accès privés Garage, dépendances, volumes et code source.
L’effacement des données n’implique ni rotation de ces accès ni effacement
forensique des supports physiques.

Les contrôles navigateur pourront employer un compte temporaire dédié, supprimé
avec ses messages et ses données avant livraison. Aucun compte n’est conservé
par défaut à la fin. Rouvrir l’application et redémarrer les services après les
contrôles ; en cas d’échec du seed, corriger et le relancer avant réouverture.

## Documentation et limites

Réécrire les instructions d’installation et de seed du README ; retirer les
procédures obsolètes de conversion d’images et de données. `docs/localization.md`
reste le guide de configuration et d’ajout d’une langue, sans ancien protocole
de bascule. Les anciens rapports/specifications sont historiques et doivent
être signalés comme tels lorsqu’ils décrivent des commandes supprimées.

Il n’existe pas de migration conservant les données de l’ancienne installation
vers ce nouvel historique. Cette rupture volontaire est indiquée dans le README
et la PR ; une autre installation doit être explicitement réinitialisée avant
d’utiliser ce nouvel historique. `make init` n’effectue jamais cette purge.

Hors périmètre : nouveau design d’interface, catalogue exhaustif de cartes,
API de synchronisation, import des anciennes données, nouveaux jeux fonctionnels,
réécriture du système de traduction ou des alias d’URL.

## Validation et critères d’acceptation

1. Une base vierge s’installe avec les migrations réécrites, sans aucune table,
   fonction, preuve ou commande de conversion historique. Les migrations peuvent
   être annulées puis rejouées dans la base de tests.
2. Les contraintes et les tests actuels du catalogue, des traductions, des comptes
   et des images restent couverts. Le garde-fou de `tests/TestCase.php` reste intact.
3. `db:seed` crée les références et des fiches TCG sourcées, visibles et cohérentes,
   sans utilisateur. Deux exécutions consécutives n’ajoutent pas de doublons ni
   de fichiers superflus ; les règles annoncées de mise à jour sont vérifiées.
4. Les tests ne nécessitent ni réseau pour les données ni base applicative.
   Les tests des fonctionnalités supprimées sont retirés ; les tests du nouveau
   schéma et du seed remplacent ceux de l’ancien dispositif.
5. Exécuter `make check`, `make assets`, `make up` puis `make test-storage`, ainsi
   qu’un contrôle d’installation neuve et de réexécution sans effacement. Vérifier
   les parcours publics FR/EN et un parcours d’administration avec le nouveau seed.
6. Vérifier la purge locale par inventaires après opération : zéro ancien compte,
   médias correspondant uniquement au nouveau seed, aucune ancienne table de
   conversion ou archive locale ciblée, aucun résidu métier de QA. Les services
   doivent être disponibles à la fin.
7. Examiner le diff complet et les fichiers indexés, lancer `git diff --check`,
   obtenir une revue indépendante du SHA final, publier la PR puis appliquer les
   conditions de CI et de fusion de `AGENTS.md`.

## Points laissés à l’implémentation

Découpage précis des migrations et seeders, nom de l’éventuel outil de schéma
isolé pour tests concurrents, organisation interne des tableaux par jeu, sélection
des assertions utiles et mécanisme ponctuel d’exécution de la purge. Ces choix
ne doivent ajouter ni compatibilité historique, ni infrastructure générique
d’import/export, ni nouvelle dépendance sans nécessité démontrée.

## Références consultées

- [Migrations Laravel 13](https://laravel.com/framework/docs/13.x/migrations), dont
  l’alternative officielle d’instantané de schéma.
- [Seeders Laravel 13](https://laravel.com/framework/docs/13.x/seeding).
- Manifeste et sources actuels : `database/seeders/data/tcg-catalog-examples.php`
  et `database/seeders/data/tcg-catalog-sources.php`. Leur reprise exige la
  revalidation des faits décrite ci-dessus.
- Premières sources officielles revérifiées le 2026-11-24 :
  [One Piece ROMANCE DAWN](https://en.onepiece-cardgame.com/products/boosters/op01.php),
  [Pokémon 151 japonais](https://www.pokemon-card.com/ex/sv2a/),
  [Magic Foundations](https://magic.wizards.com/en/products/foundations),
  [lancement de Cyberpunk TCG](https://press.cdprojektred.com/en/news/1915/cyberpunk-trading-card-game-celebrates-official-release),
  [guide Naruto Mythos](https://www.narutotcgmythos.com/collection-guide),
  [bilan du lancement de Riftbound](https://playriftbound.com/en-us/news/announcements/riftbound-origins-launch-learnings/).
  Ces consultations ne valent pas encore vérification de tous les champs du seed.
