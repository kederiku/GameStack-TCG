# GamestackrTCG

Socle Laravel 13, Filament 5 et React 19 avec TypeScript, Inertia 3,
Tailwind CSS 4 et shadcn/ui. PostgreSQL 18 stocke les données, les sessions,
le cache et les jobs. L’environnement Docker est prévu pour le développement.

## Démarrage

Prérequis : Docker Desktop démarré (ou Docker Engine avec Compose v2+) et Make.
PHP, Composer et Node sont fournis dans les conteneurs.
Sur Linux, Make reprend automatiquement l’UID/GID de l’utilisateur hôte pour
les permissions des fichiers. Ils peuvent être remplacés avec `DOCKER_UID` et
`DOCKER_GID` lors de la construction ; Docker Desktop utilise 1000 par défaut.

```sh
make init
```

Cette commande crée `.env` si nécessaire, construit l’image PHP 8.4/Node 24,
installe les dépendances verrouillées, génère la clé applicative si elle manque,
exécute les migrations, compile le frontend et démarre tous les services.
Les lancements suivants utilisent simplement :

```sh
make up
make down
```

`make down` conserve les données PostgreSQL. `make init` peut être relancé et
ne réinitialise ni les données ni la clé existante.

| Service                            | Adresse                        |
| ---------------------------------- | ------------------------------ |
| Application                        | http://localhost:8000          |
| Inscription                        | http://localhost:8000/register |
| Connexion                          | http://localhost:8000/login    |
| Administration Filament            | http://localhost:8000/admin    |
| E-mails de développement (Mailpit) | http://localhost:8025          |
| PostgreSQL depuis l’hôte           | localhost:5432                 |

Vite tourne sur le port 5173 et recharge React à chaque modification.
Ouvrir l’application sur **localhost**, comme configuré pour le rechargement Vite.

## Authentification et administrateur

Le starter kit officiel fournit inscription, connexion, déconnexion,
vérification d’adresse e-mail, réinitialisation de mot de passe, profil,
authentification à deux facteurs et passkeys. Les e-mails sont capturés dans
Mailpit ; aucun e-mail n’est envoyé sur Internet. Après inscription, suivre
le lien de vérification dans Mailpit pour accéder au dashboard.

Créer un administrateur avec une adresse non encore utilisée :

```sh
make admin
```

La commande demande nom, e-mail, mot de passe (12 caractères minimum) et
confirmation. Aucun compte ou mot de passe administrateur n’est préinstallé.
Le compte créé est vérifié et dispose de `is_admin=true`. Une adresse déjà
utilisée est refusée : la commande ne promeut pas silencieusement un compte.

`/admin/login` redirige vers la connexion React/Fortify. Le panneau Filament
utilise ensuite la même session, ce qui conserve notamment le parcours 2FA.
Les comptes issus de l’inscription publique n’ont jamais accès à Filament,
y compris en environnement local. Filament lui-même utilise Livewire ;
l’application et les écrans d’authentification utilisent Inertia/React.

## Jeux

Les administrateurs vérifiés gèrent les jeux dans **Filament → Jeux**
(`/admin/games`) : nom, slug unique et image optionnelle. Le slug est proposé
à la création et reste modifiable ; renommer un jeu ne change pas son slug.

Les images JPEG, PNG et WebP sont acceptées jusqu’à **5 Mio** et **4096 × 4096
pixels**. Le serveur les convertit en WebP (qualité 85), conserve la transparence
et corrige l’orientation EXIF des JPEG. Le fichier porte le nom normalisé du jeu,
par exemple `games/{uuid}/pokemon.webp`, sur le disque local `public`
(`storage/app/public`). Le dossier unique évite les collisions et le cache d’une
ancienne image. Le lien `public/storage` est créé par `make init`.

Les **SVG** sont également acceptés jusqu’à **5 Mio** et conservent leur format
vectoriel `.svg`, sans conversion WebP ni limite de dimensions en pixels.
Leur contenu est nettoyé avec `enshrined/svg-sanitize` avant stockage (scripts,
attributs actifs et références distantes). Les SVG invalides sont refusés.
Le renommage, le remplacement et la suppression suivent les mêmes règles que
les images WebP.

Une nouvelle image remplace l’ancienne après sauvegarde ; retirer une image ou
supprimer un jeu la supprime du disque, y compris lors d’une suppression groupée.
Renommer le jeu renomme aussi son image. Annuler le formulaire ne supprime rien.
Les erreurs de validation, d’écriture et les transactions annulées préservent
l’image précédente. Une panne disque ou un arrêt brutal reste une limite à
l’atomicité entre les fichiers et PostgreSQL ; les erreurs de nettoyage sont
signalées dans les logs Laravel.

Pour mettre à jour un environnement existant (GD/EXIF sont ajoutés à PHP) :

```sh
make build
make up
make composer ARGS='install --no-interaction'
make migrate
```

Côté code, utiliser `App\Actions\Games\SaveGame::handle($game, $data)` pour
créer/modifier un jeu et son image. `image` accepte un `UploadedFile`, le chemin
actuel pour le conserver, ou `null` pour le retirer ; omettre la clé conserve
l’image. Les champs `name` et `slug` sont validés par ce service. Pour supprimer,
utiliser `$game->delete()` ou `Game::destroy($ids)` : les mises à jour/suppressions
SQL de masse et les méthodes `*Quietly()` contournent les événements Eloquent
responsables du nettoyage.

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
```

Les tests utilisent **uniquement `gamestackr_testing`**, créée au premier
démarrage PostgreSQL. `phpunit.xml` force cette base et le bootstrap des tests
refuse une configuration pointant vers une autre base. Les migrations des tests
ne touchent pas `gamestackr`.

Le workflow GitHub Actions exécute les mêmes commandes Docker/Make.
`composer.lock` et `package-lock.json` fixent les versions installées.

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
