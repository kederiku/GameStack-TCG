# GamestackrTCG

Socle Laravel 13, Filament 5 et React 19 avec TypeScript, Inertia 3,
Tailwind CSS 4 et shadcn/ui. PostgreSQL 18 stocke les données, les sessions,
le cache et les jobs. L’environnement Docker est prévu pour le développement.

## Démarrage

Prérequis : Docker Desktop démarré (ou Docker Engine avec Compose 2.30+) et Make.
PHP, Composer et Node sont fournis dans les conteneurs.
Sur Linux, Make reprend automatiquement l’UID/GID de l’utilisateur hôte pour
les permissions des fichiers. Ils peuvent être remplacés avec `DOCKER_UID` et
`DOCKER_GID` lors de la construction ; Docker Desktop utilise 1000 par défaut.

```sh
make init
```

Cette commande crée `.env` si nécessaire, construit l’image PHP 8.4/Node 24,
installe les dépendances verrouillées, génère la clé applicative si elle manque,
initialise Garage et son interface, applique les migrations Laravel et
synchronise les langues actives (`fr` et `en`). Elle compile le frontend et
démarre tous les services sans effacer les données ni les images. Pour charger
le catalogue de référence, exécuter [`make seed`](#catalogue-tcg-dans-filament).
Pour créer un compte administrateur, utiliser [`make admin`](#authentification-et-administrateur).

Les lancements suivants utilisent simplement :

```sh
make up
make down
```

`make down` conserve les données PostgreSQL et les images Garage. `make init`
peut être relancé sur une base créée avec l’historique de migrations actuel.

| Service                            | Adresse                              |
| ---------------------------------- | ------------------------------------ |
| Application                        | http://localhost:8000                |
| Inscription                        | http://localhost:8000/fr/inscription |
| Connexion                          | http://localhost:8000/fr/connexion   |
| Administration Filament            | http://localhost:8000/fr/admin       |
| E-mails de développement (Mailpit) | http://localhost:8025                |
| PostgreSQL depuis l’hôte           | localhost:5432                       |
| Garage Web UI                      | http://localhost:3909                |
| API S3 depuis l’hôte               | http://localhost:3900                |

Vite tourne sur le port 5173 et recharge React à chaque modification.
Ouvrir l’application sur **localhost**, comme configuré pour le rechargement Vite.

### Installation utilisant l’ancien historique de migrations

Le schéma est désormais créé directement par les migrations Laravel. Pour
adopter cette version sur une autre installation qui utilise l’ancien historique,
réinitialiser explicitement sa base applicative avant `make init`. Cette remise
à zéro efface les comptes et le catalogue existants. Si ces données doivent
être conservées, garder l’installation sur sa version précédente, car aucune
migration de reprise n’est fournie.

Arrêter les écritures et vérifier la base et les emplacements d’images visés
avant la remise à zéro. Repartir d’une base vide et retirer les anciens médias
de l’application stockés sur Garage ou sur le disque `public`. Conserver les
volumes Docker, `.env`, la clé applicative et la configuration privée de Garage.
Relancer `make init`, puis utiliser `make seed` et `make admin` selon les besoins.

## Authentification et administrateur

Le starter kit officiel fournit inscription, connexion, déconnexion,
vérification d’adresse e-mail, réinitialisation de mot de passe, profil,
authentification à deux facteurs et passkeys. Les e-mails sont capturés dans
Mailpit ; aucun e-mail n’est envoyé sur Internet. Après inscription, suivre
le lien de vérification dans Mailpit pour accéder au dashboard.

Les sept écrans d’authentification utilisent le design de GameStackr :
logo et formulaire dans un panneau à gauche, trois rangées de logos des jeux
défilant en continu à droite sur ordinateur, formulaire seul sur mobile.
Les images suivent le catalogue administré ; le défilement peut être mis en pause
et devient une grille fixe lorsque les animations sont réduites.
Les textes applicatifs, la validation serveur et les e-mails sont disponibles en
français et en anglais. Les messages natifs du navigateur suivent sa langue. Passkeys, double
authentification et récupération restent disponibles. Les vérifications sont
consignées dans [le dernier rapport de design](docs/superpowers/auth-game-marquee-verification.md).

Créer un administrateur avec une adresse non encore utilisée :

```sh
make admin
```

La commande demande un nom, une adresse e-mail, un mot de passe d’au moins
12 caractères et sa confirmation. Le compte créé est vérifié et dispose de
`is_admin=true`. La création est interactive et exige une adresse inutilisée.

`/admin/login` redirige vers la connexion React/Fortify. Le panneau Filament
utilise ensuite la même session, ce qui conserve notamment le parcours 2FA.
Les comptes issus de l’inscription publique n’ont jamais accès à Filament,
y compris en environnement local. Filament lui-même utilise Livewire ;
l’application et les écrans d’authentification utilisent Inertia/React.

Le logo Filament reprend celui de l’application avec un badge vertical **ADMIN**.
Le lien **Voir l’application** du menu ouvre l’accueil public dans un nouvel onglet.

## Services de développement

En environnement `APP_ENV=local`, les administrateurs vérifiés disposent du groupe
**Développement** dans Filament. **État des services**
(`/admin/development-services`) vérifie PostgreSQL, Mailpit et Garage et présente
séparément leur disponibilité, la durée du contrôle et l’état du conteneur Docker.
Les conteneurs de l’application, du worker, de Vite et de Garage Web UI sont aussi
affichés. La page s’actualise toutes les 15 secondes pendant sa consultation ;
**Actualiser** permet un contrôle immédiat.

Les liens **Mailpit** et **Garage Web UI** du menu ouvrent leurs interfaces dans
un nouvel onglet, en respectant `MAILPIT_PORT` et `GARAGE_UI_PORT`. PostgreSQL n’a
pas d’interface web installée. Hors `local`, la section disparaît et les accès
directs ou Livewire sont refusés, même avec `APP_DEBUG=true`.

Après mise à jour, lancer `make up` : le conteneur `service-status` collecte toutes
les 5 secondes les états des seuls services du projet Compose courant. Il n’a
ni réseau ni port publié. Lui seul monte le socket Docker ; l’application lit
son rapport minimal dans un volume en lecture seule. Make active le profil Compose
`local` ; le collecteur reste arrêté si `APP_ENV` a une autre valeur. Sa garde
d’environnement interdit aussi la collecte lors d’un lancement explicite hors local.
Aucune commande d’arrêt, de redémarrage
ou de modification de conteneur n’est proposée dans l’admin.

Un rapport absent, invalide ou vieux de plus de 30 secondes affiche un état Docker
**Indisponible**, sans empêcher les contrôles de réponse. Les sondes n’écrivent
ni dans la base, ni dans les e-mails, ni dans les objets Garage. Le contrôle Garage
vérifie la santé du cluster local, pas les permissions du bucket S3 configuré.
Une panne PostgreSQL peut empêcher l’ouverture de l’admin, dont les comptes et
sessions dépendent déjà de cette base : la page ne remplace pas un moniteur externe.

## Jeux

Les administrateurs vérifiés gèrent les jeux dans **Filament → Jeux**
(`/admin/games`) : nom, slug unique et image optionnelle. Le slug est proposé
à la création et reste modifiable ; renommer un jeu ne change pas son slug.

L’année de création, l’année de fin, le nom de l’éditeur et une description
en texte simple sont également éditables. Ces quatre champs sont facultatifs.
Les années se saisissent seules, sur quatre chiffres ; lorsque les deux sont
renseignées, la fin ne peut pas précéder la création. Laisser la fin vide pour
un TCG encore actif ou dont l’année de fin est inconnue. L’éditeur est limité
à 255 caractères.

L’interrupteur **Afficher sur le site**, activé par défaut, permet de masquer
un jeu tout en le gardant dans Filament. Un jeu masqué disparaît des listes de
l’accueil, des menus, du dashboard et des écrans d’authentification ; son URL
publique renvoie 404. Le réactiver le rend de nouveau accessible. La liste
Filament indique sa visibilité. Cette option est indépendante de l’année de fin
et conserve les informations et l’image du jeu.

Les formulaires regroupent les champs en sections **Présentation**, **Historique**,
**Visuel** et **Publication**. Sur grand écran, l’image et la visibilité occupent
une colonne latérale ; sur petit écran, les sections s’empilent. La liste affiche
le slug sous le nom, l’éditeur et un badge de visibilité. Le sélecteur de colonnes
permet de retrouver les colonnes Slug et Modifié le. Sur mobile, les visuels et
l’éditeur sont masqués pour alléger le tableau, qui conserve son défilement horizontal.

Les images JPEG, PNG, WebP et AVIF sont acceptées jusqu’à **5 Mio** et **4096 ×
4096 pixels**. Le serveur les réencode en **AVIF** (qualité 85), conserve la
transparence et corrige l’orientation EXIF des JPEG. Le fichier porte le nom
normalisé du jeu, par exemple `games/{uuid}/pokemon.avif`, sur le disque choisi
par `GAME_IMAGES_DISK` : `s3` pour Garage, `public` pour le stockage local
dans `storage/app/public`. Le dossier unique évite les collisions et le cache
d’une ancienne image. Le lien `public/storage` est créé par
`make init`. L’aperçu des imports AVIF est également disponible dans Filament.

Les **SVG** sont acceptés jusqu’à **5 Mio**. Leur contenu est nettoyé avec
`enshrined/svg-sanitize`, puis rasterisé par librsvg en conservant les proportions
et la transparence, avec un côté maximal de 2048 pixels, avant encodage AVIF.
Le fichier enregistré n’est donc plus vectoriel. Le rendu est limité à dix
secondes ; les SVG invalides ou impossibles à convertir sont refusés.
Les images intégrées dans un SVG sont limitées à 4096 × 4096 pixels chacune et
16 777 216 pixels au total. Le processus de rendu dispose d’un plafond de
512 Mio d’espace d’adressage via `prlimit` (util-linux, fourni par Docker).

Une nouvelle image remplace l’ancienne après sauvegarde ; retirer une image ou
supprimer un jeu la supprime du disque, y compris lors d’une suppression groupée.
Renommer le jeu renomme aussi son image. Annuler le formulaire ne supprime rien.
Les erreurs de validation, d’écriture et les transactions annulées préservent
l’image précédente. Une panne disque ou un arrêt brutal reste une limite à
l’atomicité entre les fichiers et PostgreSQL ; les erreurs de nettoyage sont
signalées dans les logs Laravel.

Pour mettre à jour une installation déjà sur l’historique actuel de migrations,
exécuter les commandes ci-dessous. L’image Docker fournit GD avec AVIF, EXIF et
librsvg. Pour une autre installation, suivre les consignes relatives à
[l’ancien historique de migrations](#installation-utilisant-lancien-historique-de-migrations).

```sh
make build
make composer ARGS='install --no-interaction'
make up
make migrate
```

Côté code, utiliser `App\Actions\Games\SaveGame::handle($game, $data)` pour
créer/modifier un jeu et son image. `image` accepte un `UploadedFile`, le chemin
actuel pour le conserver, ou `null` pour le retirer ; omettre la clé conserve
l’image. Le service valide `name`, `slug`, `start_year`, `end_year`, `publisher`
et `description`. Omettre un champ d’information facultatif conserve sa valeur ;
transmettre `null` ou une chaîne vide l’efface. `is_visible` accepte un booléen ;
omettre la clé conserve la visibilité actuelle (activée à la création).
Pour supprimer,
utiliser `$game->delete()` ou `Game::destroy($ids)` : les mises à jour/suppressions
SQL de masse et les méthodes `*Quietly()` contournent les événements Eloquent
responsables du nettoyage.

## Stockage S3 local et interface

[Garage](https://garagehq.deuxfleurs.fr/) 2.4.0 fournit le stockage compatible S3.
[Garage Web UI](https://github.com/khairul169/garage-webui) 1.1.0 permet de
parcourir les buckets et les fichiers, voir leur taille, les télécharger et
prévisualiser les images prises en charge par le navigateur, dont les AVIF.
Les deux logiciels tournent dans Docker, sans compte cloud ni abonnement.

Ouvrir **http://localhost:3909**. Le compte est `admin` ; son mot de passe
aléatoire est enregistré dans le fichier local privé :

```sh
cat storage/app/private/garage/login.txt
```

`make storage-password` génère un nouveau mot de passe et redémarre l’interface.
`make storage-init` prépare uniquement le stockage ; `make up` l’appelle déjà.
Les secrets, la configuration et les accès S3 sont générés sous
`storage/app/private/garage/`, ignorés par Git, avec des permissions privées.
Une relance conserve les clés, le mot de passe, le layout et les objets.
Ne pas supprimer ce dossier pour « réinitialiser » une installation existante.

Dans l’interface, choisir **Buckets → gamestackr-images → Browse → games**.
Les modifications métier passent par **Filament → Jeux** : déposer, renommer
ou supprimer directement un objet dans Garage ne met pas à jour PostgreSQL.
L’application fonctionne indépendamment de l’interface Web UI.

Laravel utilise `http://garage:3900` dans le réseau Docker. Le navigateur lit
les images via `http://gamestackr-images.localhost:3902/games/...`. Ces adresses
sont distinctes. Les ports 3900, 3902 et 3909 sont liés à `127.0.0.1` ; RPC et
API d’administration restent internes. Le bucket contient uniquement des
images publiques ; les fichiers privés de Laravel gardent leur disque local.
Le CORS autorise les lectures depuis `APP_URL`.

Le fichier `.env.example` choisit `GAME_IMAGES_DISK=s3`. En l’absence de cette
variable, l’application utilise `public`.
Les paramètres Garage locaux proviennent de `storage/app/private/garage/s3.json`.
Les variables `AWS_*` **non vides** de `.env` ont priorité. Pour utiliser un autre
service S3, renseigner un ensemble cohérent : clé, secret, région, bucket,
endpoint, URL publique et `AWS_USE_PATH_STYLE_ENDPOINT`. Ne pas mélanger des
identifiants externes avec les valeurs locales de repli. L’initialisation
Garage ne réécrit pas ces variables. Les ports et le nom du bucket local sont
réglables avec `GARAGE_*` dans `.env`. Après un changement de région, arrêter
puis redémarrer les services pour recharger la configuration Garage.

### Persistance et vérifications

Sauvegarder ensemble la base PostgreSQL, les volumes Docker `garage-data` et
`garage-meta`, ainsi que `storage/app/private/garage/`. Faire une sauvegarde
cohérente services arrêtés. Inclure `storage/app/public` si le disque `public`
est utilisé. `make down` conserve les volumes.

La configuration utilise un seul nœud et une seule copie des objets : elle
est destinée au développement. Un serveur de production demandera une
configuration dédiée pour HTTPS, domaines, accès, sauvegardes et disponibilité.
L’API S3 permet de garder l’intégration Laravel lors de ce changement.

La suite habituelle utilise des disques simulés et une base de test séparée.
Pour vérifier explicitement Garage réel, après `make up` :

```sh
make test-storage
```

Ces tests utilisent `gamestackr_testing`, créent leurs propres chemins UUID et
les nettoient. Voir aussi [le rapport historique du stockage](docs/superpowers/local-image-storage-verification.md).

## Navigation publique

`/fr` et `/en` présentent le projet de gestion de collections multi-TCG. Le menu
et les tuiles utilisent les jeux créés dans Filament. Choisir un jeu ouvre
`/{locale}/{slug}`, par exemple `/fr/pokemon` ou `/en/yu-gi-oh`. Une URL inconnue
renvoie 404. Modifier un slug change son URL publique, sans redirection depuis
l’ancien slug.

Les segments utilisés par l’application (`admin`, `login`, `settings`, etc.)
sont réservés par `App\Support\GameSlug`, qui inclut aussi les préfixes des
routes enregistrées. Filament et `SaveGame` refusent ces slugs. Un ancien jeu
utilisant un segment réservé est masqué de la navigation publique ; lui donner
un autre slug dans Filament le rend accessible.

Les pages publiques partagent un layout React avec les composants shadcn/ui
existants. Le logo utilise Big Shoulders Display avec « TCG » vertical, chargée
par le pipeline de polices Vite. Le site React utilise uniquement le mode sombre,
y compris au premier chargement. Une image absente ou impossible à charger
laisse place aux initiales du jeu.

La page d’un jeu affiche les extensions publiées dans Filament, regroupées par
série. Catalogue, langue d’édition, type, recherche et tri sont conservés dans
l’URL ; la pagination affiche 36 extensions à la fois. Les visibilités héritées
du jeu, du catalogue et de la série s’appliquent aussi aux accès directs.
Un catalogue vide affiche « Le catalogue se prépare ».

Chaque fiche `/{locale}/{jeu}/{catalogue}/{extension}` présente les éditions visibles et
leurs sorties par marché et canal. `edition=fr` sélectionne une édition ; un
choix indisponible déclenche un repli annoncé. Les logos restent propres aux
éditions. Les cartes, collections et prix restent à venir.

Le sélecteur de langue ouvre la même page sous `/fr` ou `/en`, indépendamment
de la langue des éditions. Le préfixe détermine la langue de la réponse. Le
paramètre historique `lang` intervient sur les anciennes URL. Le nom d’affichage
utilise la langue demandée, puis l’anglais, puis la langue par défaut du catalogue
pour les séries et extensions ou celle de l’application (le français) pour les
jeux, catalogues et marchés. Le nom de référence intervient en dernier. Les textes
descriptifs passent directement de la langue demandée à la langue par défaut du
catalogue ou de l’application, puis au texte de référence.

Voir [les vérifications de la navigation publique](docs/superpowers/public-navigation-verification.md).

## Catalogue TCG dans Filament

Les administrateurs peuvent gérer les catalogues, séries et extensions dans
`/admin`. Les fiches d’extension distinguent le nom d’affichage, les éditions
imprimées et les sorties de chaque édition par marché et canal.

Après `make init`, charger le catalogue de référence sur demande :

```sh
make seed
```

Sur une base neuve, le seed charge 12 jeux, 19 catalogues, 7 séries, 119 fiches
d’extension, 327 éditions imprimées dont 78 en français, 213 sorties, 10 langues
de contenu et 9 marchés. Un même titre peut avoir une fiche dans plusieurs
catalogues régionaux, comme les versions japonaises et internationales de
ONE PIECE et Yu-Gi-Oh!. Les fiches sont visibles dès le chargement.
Le seed utilise les fichiers versionnés, sans accès réseau, et ne crée ni compte
ni relation entre extensions. Les deux visuels officiels servent aux images de
série et d’édition. Les jeux sans logo affichent leurs initiales.

Le catalogue retient cinq extensions Altered distribuées en boosters d’origine,
cinq extensions Riftbound et le coffret Premiers pas, deux extensions NARUTO
Mythos et une extension Cyberpunk. Les produits annoncés et les packs Altered
imprimés à la demande sont documentés dans le registre de sources. Chaque
édition correspond à une impression physique attestée et chaque sortie datée
à un couple marché et canal confirmé. Le libellé d’interface anglais d’un
booster japonais peut être une traduction éditoriale du produit imprimé en
japonais. Les langues d’interface disponibles restent le français et l’anglais.

**Une réexécution rétablit les valeurs fournies par le manifeste**, y compris
après une modification manuelle. La commande utilise les identifiants métier,
dont le slug du jeu, pour retrouver les fiches. Elle recrée une fiche manquante,
réutilise les médias identiques et conserve les fiches et traductions absentes
du manifeste. Un jeu dont le slug a été changé reste indépendant du jeu recréé
sous le slug du manifeste. Les écritures s’effectuent dans une transaction.

La provenance est détaillée dans le [registre de sources](database/seeders/data/tcg-sources.php),
les [fichiers par jeu](database/seeders/data/tcg/) et le [dossier des visuels](database/seeders/assets/tcg-catalog/README.md).

## Dashboard et composants partagés

`/dashboard` propose **Mon espace** aux comptes connectés et vérifiés : accueil
personnalisé, panneau de collection en préparation, accès aux jeux de Filament
et raccourcis vers les réglages. Aucun chiffre de collection ou prix fictif
n’est affiché. Le dashboard fonctionne aussi sans jeu enregistré.

La navigation du compte et les réglages partagent la palette GameStackr de
l’accueil et de l’authentification, dans un thème sombre commun.
La sidebar se replie sur ordinateur et devient un tiroir sur mobile.

Les pages **Mon profil** et **Sécurité** prolongent cette identité
avec des panneaux partagés et une navigation compacte. Les formulaires, fenêtres
de confirmation, clés d’accès et étapes de double authentification sont en
français ou en anglais selon le préfixe de l’URL, comme le parcours public et
l’authentification. Les messages natifs du navigateur suivent sa langue. Les
anciens liens d’apparence restent accessibles, sans proposer de changement de
thème.

Pour les nouveaux écrans React, réutiliser :

- `components/brand-logo.tsx` et `components/card-fan.tsx` pour la marque ;
- `components/games/` pour les images, le sélecteur et la grille de jeux ;
- `components/empty-state.tsx` pour les états vides ;
- `components/settings-section.tsx` pour les panneaux des réglages du compte ;
- `components/catalog/`, `filter-select.tsx` et `segmented-picker.tsx` pour les
  catalogues, les éditions et leurs filtres ;
- les primitives `components/ui/` et les tokens de `resources/css/app.css`.

La grille de jeux est commune à l’accueil et au dashboard. Les états vides
sont partagés avec les pages de jeu ; les menus utilisent les mêmes couleurs,
même lorsqu’ils sont rendus en dehors du layout.

Voir [les vérifications du dashboard](docs/superpowers/dashboard-verification.md).
Voir aussi [les vérifications de l’harmonisation](docs/superpowers/design-harmonization-verification.md).

## Commandes

| Commande                     | Effet                                            |
| ---------------------------- | ------------------------------------------------ |
| `make help`                  | Liste des commandes                              |
| `make init`                  | Installation complète et démarrage               |
| `make up` / `make down`      | Démarrer / arrêter en conservant les données     |
| `make stop` / `make restart` | Suspendre / redémarrer les conteneurs            |
| `make ps`                    | État des services                                |
| `make logs`                  | Logs en continu ; `ARGS=app` pour un service     |
| `make admin`                 | Créer un administrateur                          |
| `make migrate`               | Exécuter les migrations                          |
| `make seed`                  | Charger ou rétablir le catalogue de référence    |
| `make test`                  | Suite de tests sur PostgreSQL dédié              |
| `make check`                 | PHP/Pint, PHPStan, tests, frontend et TypeScript |
| `make format`                | Formater le code PHP et frontend                 |
| `make assets`                | Compiler les assets                              |
| `make build`                 | Reconstruire l’image Docker                      |
| `make shell`                 | Shell dans l’application                         |

Exécuter les outils sans installation locale :

```sh
make artisan ARGS='route:list'
make artisan ARGS='make:model Card -m'
make composer ARGS='require vendor/package'
make npm ARGS='install package'
make npm ARGS='exec shadcn@latest add button'
make test ARGS='--filter=AdminAccessTest'
```

Les composants shadcn/ui sont dans `resources/js/components/ui`, les pages
Inertia dans `resources/js/pages`, la configuration Filament dans
`app/Providers/Filament/AdminPanelProvider.php`.

## Configuration

Modifier `.env`, puis relancer `make up` pour appliquer les ports Compose.
`APP_PORT`, `VITE_PORT`, `MAILPIT_PORT` et `DB_FORWARD_PORT` permettent d’éviter
les conflits. En changeant `APP_PORT`, adapter également `APP_URL` (par exemple
`APP_PORT=8080` et `APP_URL=http://localhost:8080`). Le domaine HMR reste localhost.

Depuis les conteneurs, PostgreSQL est accessible par `postgres:5432` et Mailpit
par `mailpit:1025`. Pour un client SQL sur le Mac, utiliser les identifiants
`DB_USERNAME`, `DB_PASSWORD` et `DB_DATABASE` de `.env`, avec `localhost` et
`DB_FORWARD_PORT`. Ces identifiants d’exemple sont destinés au développement local.
Modifier les variables PostgreSQL après création du volume ne change pas les
identifiants déjà enregistrés dans la base.

Le worker `queue` traite les jobs avec la queue en base de données. Après une
modification du code des jobs : `make artisan ARGS='queue:restart'`, puis
`make up` pour relancer le worker s’il est arrêté.

## Tests et intégration continue

```sh
make check
make assets
make test-storage
```

Les tests utilisent **uniquement `gamestackr_testing`**, créée au premier
démarrage PostgreSQL. `phpunit.xml` force cette base et le bootstrap des tests
refuse une configuration pointant vers une autre base. Les migrations des tests
ne touchent pas `gamestackr`.

Le workflow GitHub Actions `tests` s’exécute sur les PR vers `main` et les push
sur `main`. Son job `ci` installe le projet depuis les lockfiles avec `make init`
(qui compile aussi les assets), puis lance `make check` et `make test-storage`
contre Garage réel. Les anciens runs d’une même PR sont annulés et chaque job
est limité à 30 minutes.
`composer.lock` et `package-lock.json` fixent les versions installées.

Le parcours des agents est décrit dans [AGENTS.md](AGENTS.md) : branche dédiée,
revue indépendante tracée, PR, CI verte sur le commit revu, puis fusion squash.
Le propriétaire autorise cette fusion sans nouvelle confirmation une fois ces
conditions remplies. GitHub protège `main` avec PR et check `ci` obligatoires,
branche à jour et discussions résolues, y compris pour l’administrateur.

Si un volume PostgreSQL préexistant ne contient pas la base de tests, la créer
avec les identifiants de `.env` (valeur par défaut ci-dessous) :

```sh
docker compose exec postgres createdb -U gamestackr gamestackr_testing
```

La suppression du volume PostgreSQL efface les données : elle ne fait partie
d’aucune commande Make habituelle. Ne pas utiliser `docker compose down -v`
pour un simple arrêt.

## Origine

Starter officiel : [laravel/react-starter-kit](https://github.com/laravel/react-starter-kit),
commit `012f4248f70e255e32ad93f540aca5fb7e8c96fe` (14 novembre 2026).
Voir `docs/superpowers/verification.md` pour les vérifications effectuées.

## Traductions de l’interface

Les neuf domaines de `lang/fr` et `lang/en` constituent la source éditable PHP.
Utiliser des clés sémantiques (`admin.languages.code_help`,
`validation.catalog.immutable_identity`) et résoudre les textes pendant le rendu,
jamais dans une propriété statique ou une constante traduite. React consomme
`useLocale().t(key, replacements)` et `tChoice(key, count, replacements)` ; les
noms de routes passent par `useLocalizedRoutes`. Les paramètres restent du texte
échappé par React ou Blade, sans HTML dans les traductions.

`make translations` génère les exports et types frontend ignorés par Git ; seuls
`common`, `auth`, `account` et `catalog` sont exportés. `make artisan
ARGS='localization:check'` vérifie les clés, paramètres et pluriels. La compilation
et les vérifications frontend régénèrent aussi ces exports. Ne pas modifier les
JSON générés ni créer une seconde source JSON éditable.

Fortify, Passkeys et le résumé de validation Laravel demandent certains messages
par leur phrase anglaise. `VendorTranslationLoader` adapte uniquement leur espace
de clés littérales (`*`) à `lang/vendor/{fortify,passkeys,laravel}/{locale}/messages.php`.
Ces fichiers PHP sont les overrides applicatifs de cette API vendor ; le loader
délègue les autres domaines et namespaces sans modifier l’authentification. Le
contrôle de parité couvre aussi ces trois fichiers. Les vues applicatives sous
`resources/views/vendor` traduisent l’habillage HTML et texte des e-mails.

Pour préparer une autre langue d’interface, fournir les neuf domaines et ces trois
fichiers vendor, avec les placeholders identiques, puis l’ajouter au registre
`config/localization.php`, contrôler les traductions et reconstruire les assets.
Les traductions des contenus et les langues des éditions imprimées restent des
notions distinctes. Les comptages applicatifs sont non négatifs ; FR/EN sont les
langues actuellement livrées. Le contrôle visuel complet après migration fait
partie du parcours de livraison.

Le [guide de localisation](docs/localization.md) détaille la synchronisation
des langues, la compatibilité des liens et l’activation d’une langue.
