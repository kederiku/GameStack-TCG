# Localisation

Les URL canoniques commencent par `/fr` ou `/en`. Le préfixe décide de la langue,
y compris pour un utilisateur connecté ayant une autre préférence. Le sélecteur
enregistre le choix de l’utilisateur, conserve la ressource et les critères de
navigation applicables. Les identifiants de jeu, de catalogue et d’extension sont
stables. Les langues de contenu et d’édition viennent de la table `languages`.
L’activation de l’interface dépend de `config/localization.php`.

## Installation neuve

`make init` arrête les services applicatifs, puis appelle `localization:install`.
Sur un schéma PostgreSQL vide, cette commande place Laravel en maintenance, exécute
les migrations et vérifie la copie avant de retirer les deux tables historiques.
Elle rouvre l’application seulement si elle a elle-même activé la maintenance et
si la finalisation a réussi. Une maintenance préexistante conserve ses options.
Une installation déjà finalisée accepte les migrations suivantes. Sur tout autre
schéma existant, l’initialisation s’arrête et la procédure ci-dessous s’applique.

### Première installation avec maintenance en cache PostgreSQL

Avec `APP_MAINTENANCE_DRIVER=cache` et un store de type `database`, la lecture
de l’état de maintenance demande une table de cache existante. Si elle manque,
`make init` s’arrête avant toute migration. Pour une **base neuve**, garder le
trafic isolé, les workers, imports et tâches planifiées arrêtés. Le premier
`make init` a installé les dépendances et démarré PostgreSQL. Les commandes
suivantes utilisent des conteneurs ponctuels et la configuration réelle du projet.

Pour le store standard, amorcer uniquement les tables du cache. Ensuite activer
la maintenance dans ce même store, terminer la migration et contrôler la preuve.

```sh
artisan_init() {
    make artisan EXEC='docker compose --profile local run --rm --no-deps app' ARGS="$*"
}
(
    set -e
    artisan_init migrate --force --path=database/migrations/0001_01_01_000001_create_cache_table.php
    artisan_init down
    artisan_init migrate --force
    artisan_init localization:finalize --dry-run
    artisan_init localization:finalize
    artisan_init localization:audit
    make init
)
```

Vérifier chaque sortie avant la commande suivante. Le second `make init` reconnaît
le reçu finalisé et conserve la maintenance pendant le build et le démarrage.
Après validation, exécuter `artisan_init up` et rétablir le trafic. Si une
maintenance est déjà active sur le store, garder ses options et omettre `down`
et `up` jusqu’à la réouverture décidée par son propriétaire. Pour un store sur
une connexion ou une table personnalisée, provisionner cette table sur sa
connexion avant `make init`. La migration fournie crée `cache` et `cache_locks`
sur la connexion applicative. Une base contenant déjà des données suit la
procédure de bascule ci-dessous.

## Bascule d’une installation existante

1. Consigner la révision exacte de la dernière version déployable utilisant les
   anciennes tables et conserver son artefact. Mettre le site en maintenance
   avec `make artisan ARGS=down`, suspendre le worker, les tâches planifiées et les
   imports, attendre la fin des écritures en cours. Dans l’environnement Compose,
   utiliser `docker compose stop queue` et conserver le service `app` accessible
   pour les commandes Make. Si une tentative de `make init` a déjà arrêté
   le service, isoler d’abord le trafic, puis lancer uniquement
   `docker compose up -d --no-deps app` et activer la maintenance. Sur plusieurs
   instances, isoler toutes les entrées.
2. Sauvegarder la base PostgreSQL dans un emplacement privé, les images locales
   de `storage/app/public`, les volumes de données et métadonnées Garage et sa
   configuration privée. Conserver la configuration applicative et les accès de
   restauration séparément. Vérifier la lisibilité des archives et la restauration
   sur une instance isolée. Le README décrit les volumes de stockage. Conserver
   cette sauvegarde avec le SHA déployable relevé à l’étape 1.
3. Installer la nouvelle version en conservant la maintenance. Utiliser son
   audit sur le schéma historique, puis appliquer la migration uniquement si le
   diagnostic est bon. Conserver les sorties en privé.

    ```sh
    make artisan ARGS='localization:audit'
    make artisan ARGS='migrate --force'
    make artisan ARGS='localization:audit'
    make artisan ARGS='localization:finalize --dry-run'
    ```

    La migration copie les traductions et les chemins d’images, synchronise les
    langues actives, migre les liaisons d’exemples et établit les empreintes V1.
    Elle rend les tables historiques accessibles en lecture seule. Jusqu’à la
    finalisation, garder toute écriture du catalogue suspendue, y compris les
    seeders. L’audit contrôle les références et le schéma. La simulation exige le
    schéma converti et le reçu vérifié V1. Elle contrôle les colonnes introduites
    ou renommées par la conversion, dont le type JSONB des traductions, même pour
    une table vide. Elle compare les sources, les cibles et tous leurs compteurs
    aux preuves initiales. Elle peut s’exécuter
    avant la maintenance sur une base déjà convertie et prend des verrous de
    lecture cohérente pouvant attendre les écritures. Elle garde les données,
    la preuve et le stockage intacts.

4. Examiner les compteurs, les écarts éventuels, les chemins d’images et le code
   de retour de chaque commande. En cas d’écart, garder la maintenance, retrouver
   la cause à partir de la sauvegarde et conserver le diagnostic. Toute modification
   de la preuve invaliderait le contrôle. Avec la simulation réussie et la nouvelle
   version installée sur toutes les instances, finaliser.

    ```sh
    make artisan ARGS='localization:finalize'
    make artisan ARGS='localization:audit'
    make artisan ARGS='localization:check'
    make assets
    make artisan ARGS='config:cache'
    make artisan ARGS='route:cache'
    ```

    Le finaliseur resérialise la vérification avec les écritures et les autres
    finaliseurs. Dans la même transaction, il retire `series_localizations`,
    `expansion_localizations` et leur fonction PostgreSQL de protection, puis
    renseigne `finalized_at` dans `localization_conversion_verifications`.
    Une dépendance SQL restante empêche la suppression et annule la transaction.
    Les comptes, empreintes et dates sont conservés. Une relance reconnaît le reçu
    finalisé, y compris après des modifications de contenu autorisées. Aucun objet
    disque ne participe à ces opérations SQL.

5. Vérifier les routes publiques, les variantes et images, l’authentification et
   Filament dans les deux langues. Après validation, rétablir le trafic avec
   `make artisan ARGS=up`, redémarrer les workers et les tâches suspendus.

### Retour et reprise

Avant la migration, une annulation consiste à reprendre la version précédente et
ses services. Après le début de la conversion et jusqu’à la réouverture, conserver
le site en maintenance, restaurer la sauvegarde cohérente sur une base isolée,
y associer le **code de la précédente version déployable** et ses dépendances,
vérifier les références et les images, puis basculer vers cet ensemble. Le
`down()` de la conversion est volontairement bloqué. Après la réouverture,
privilégier une correction en avant. Une restauration impose de rapprocher
au préalable les écritures reçues depuis la sauvegarde.

En cas d’échec d’une installation neuve, la maintenance reste active. Examiner
l’erreur et l’état des migrations. Si la conversion a réussi, suivre les audits
et la simulation ci-dessus, puis finaliser la preuve originale. Si des migrations
restent en attente, diagnostiquer leur échec avant de les reprendre sous
maintenance. Préserver les volumes et les données de diagnostic.

## Ajouter une langue

1. Préparer en dehors du registre actif son code de préfixe, sa locale Laravel,
   son tag HTML, son libellé et sa direction (`ltr` ou `rtl`). Réunir les
   neuf domaines PHP `common`, `auth`, `account`, `catalog`, `admin`,
   `validation`, `passwords`, `notifications`, `routes` dans
   `lang/{localeLaravel}/`, ainsi que
   `lang/vendor/{fortify,passkeys,laravel}/{localeLaravel}/messages.php`.
   Préserver les placeholders, les paramètres des chemins et les pluriels.
   Traduire les segments de navigation et vérifier les textes natifs de Filament.
2. Sur une installation isolée, ajouter atomiquement les dictionnaires et
   l’entrée du registre à la version à tester. Exécuter `make artisan
   ARGS='localization:check'`, `make artisan ARGS='route:cache'` et
   `make artisan ARGS='localization:audit'` pour relever les clés manquantes,
   erreurs de construction et collisions avec les slugs existants. Contrôler le
   public, l’authentification, Filament, le sélecteur et le repli. Prévoir un
   contrôle visuel supplémentaire pour une langue RTL.
3. Déployer les fichiers et le registre ensemble sous maintenance. Régénérer la
   configuration (`make artisan ARGS='config:cache'`), les routes
   (`make artisan ARGS='route:cache'`), les assets (`make assets`) et synchroniser
   le référentiel de contenu avec `make artisan ARGS='localization:sync-languages'`.
   Redémarrer les processus persistants avant réouverture. `make translations`
   génère à la demande les routes Wayfinder, l’adaptateur typé et les dictionnaires
   React. Les fichiers générés sont ignorés par Git.
4. Les traductions de contenu peuvent ensuite être complétées dans Filament.
   Une traduction de texte n’ajoute aucune édition imprimée. La langue du catalogue
   sert de repli aux séries et extensions. Les autres entités utilisent le français
   puis leur référence. Un texte descriptif explicitement vide peut supprimer le
   repli de ce champ.

## Référencement

L’accueil, les jeux et les fiches d’extension ont une URL canonique dans la
langue visitée. Chaque page indexable publie les autres langues pour la même
ressource et `x-default` en français. Une page de liste conserve `page=2` et
les pages suivantes. Les zéros initiaux sont normalisés. Une page hors plage prend
l’identité de la dernière page affichée et une valeur invalide celle de la première.
Les filtres, la recherche et le tri actifs, ainsi qu’une
édition explicitement sélectionnée, donnent `noindex,follow` et une canonique
pour cette sélection normalisée. Les valeurs par défaut et paramètres de suivi
disparaissent. Les écrans auth/compte, les liens signés et Filament ne publient
aucune variante SEO. Le document définit sa langue et sa direction dès la
réponse HTML puis les actualise lors des navigations Inertia.

## Compatibilité des anciens liens

| Entrées conservées                                                                                                                                                                                                                                                                        | Usage                                                                                                                                                                                                                                                                                            |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `/`, `/{jeu}`, `/{jeu}/{catalogue}/{extension}`, `/dashboard`, `/settings`, `/settings/{profile,security,appearance}`                                                                                                                                                                     | GET/HEAD redirige vers la page localisée. Le choix historique suit `lang`, le profil, le cookie, puis FR. Les liens du catalogue vérifient la visibilité.                                                                                                                                        |
| `/admin`, `/admin/login`, `/admin/development-services`, `/admin/{ressource}` et ses pages `create` et `{record}/edit` disponibles                                                                                                                                                        | Redirection des favoris vers le panneau de même identité. Les sept ressources sont jeux, catalogues, séries, extensions, éditions, langues et marchés.                                                                                                                                           |
| Vues Fortify `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/email/verify`, `/user/confirm-password`, `/two-factor-challenge`                                                                                                                                     | Les GET/HEAD de présentation redirigent. Les noms historiques continuent de satisfaire les appels du framework.                                                                                                                                                                                  |
| Actions Fortify d’inscription, connexion et déconnexion, mot de passe oublié/réinitialisé/confirmé, vérification d’e-mail et renvoi, mise à jour profil, challenge/activation/confirmation/désactivation 2FA, QR code, clé secrète et codes de récupération, options et mutations passkey | Les alias non préfixés gardent verbes, contrôleurs et middlewares pour les anciens formulaires, clients JSON et liens signés. L’inventaire exact par nom et URI figure dans `AuthRouteDefinitions::legacyPaths()`. Une ancienne URL de vérification signée est exécutée à son adresse d’origine. |
| PATCH/DELETE `/settings/profile`, PUT `/settings/password`                                                                                                                                                                                                                                | Compatibilité des anciennes mutations de compte avec les protections d’origine.                                                                                                                                                                                                                  |
| `/.well-known/passkey-endpoints`                                                                                                                                                                                                                                                          | Point d’entrée standard pour la découverte des passkeys. Les destinations générées sont localisées.                                                                                                                                                                                              |

Les clés historiques `series-localization:…` et `expansion-localization:…` du
manifeste identifient durablement les exemples administrés. Leurs liaisons
pointent désormais sur un parent et une locale. Les anciennes tables restent
nommées dans les migrations figées, le finaliseur et les fixtures de conversion.
Les trois fichiers de messages vendor adaptent les clés textuelles attendues par
Fortify, Passkeys et Laravel à la source éditable PHP.
