# Stockage local des images avec Garage et interface web

> Archive historique. La commande de copie vers S3 et la conversion des images existantes ont été retirées.
> Voir [les instructions de stockage actuelles](../../../README.md#stockage-s3-local-et-interface).

Statut : approuvé par l’utilisateur le 15 novembre 2026.
Branche : `codex/local-image-storage`, créée depuis `main`.

## Intention et besoins exprimés

Ajouter un stockage d’images gratuit, local et compatible S3 au projet
GamestackrTCG. L’usage immédiat est le développement avec Docker ; un
hébergement sur un serveur de production est envisageable plus tard.
Une interface web doit permettre de parcourir ce qui est stocké. Les
miniatures sont souhaitables, mais ne sont pas indispensables.

## Approches étudiées et proposition

- **Garage avec Garage Web UI** : stockage dédié à l’auto-hébergement et
  navigateur de buckets et d’objets fourni par un projet communautaire séparé.
  Cela ajoute deux services et demande de vérifier leurs versions ensemble.
  C’est la proposition retenue par l’agent pour répondre aux besoins exprimés.
- **SeaweedFS** : stockage S3 et interfaces intégrées, avec davantage de
  fonctions d’administration. Son interface `weed admin` est encore présentée
  comme un travail en cours dans la documentation consultée. Elle ne justifie
  pas à elle seule de préférer cette solution pour le besoin actuel.
- **Stockage Laravel local actuel** : le moins de maintenance, mais il ne
  fournit pas de service S3. Une interface devrait être ajoutée séparément.

MinIO n’est pas retenu pour cette nouvelle installation : son dépôt
communautaire est archivé. L’utilisateur a approuvé la proposition technique
Garage + Garage Web UI avec ce document le 15 novembre 2026.

## Périmètre

Inclure les visuels des jeux administrés par Filament, leur lecture sur le
site et leur cycle de vie : import, conversion AVIF, renommage, remplacement,
suppression et récupération après une erreur.

Installer une interface existante permettant de consulter les buckets et
les objets. Exploiter ses aperçus natifs s’ils fonctionnent, y compris pour
AVIF, sans en faire une condition de livraison. Ne pas développer de galerie
personnalisée, de génération de miniatures ou de fork de l’interface.

Les modifications métier des images continuent à passer par Filament.
L’interface de stockage est un outil d’administration indépendant ; ses
éventuelles actions d’écriture ne mettent pas à jour PostgreSQL. Documenter
cette distinction plutôt que promettre une synchronisation inexistante.

La production, la réplication entre machines, les sauvegardes automatisées,
les images privées et les avatars sont hors du périmètre de cette étape.
Les fichiers temporaires Livewire et les sauvegardes privées des conversions
restent locaux. Aucun changement du schéma SQL n’est nécessaire.

## État du projet

Laravel 13, Filament 5, PostgreSQL et les outils frontend tournent déjà dans
Docker Compose. `GameImages`, `PublicGames`, les composants Filament et la
commande de conversion utilisent le disque `public` en dur. Le service
stocke des clés `games/{uuid}/{nom}.avif`, et la base conserve ces clés plutôt
que des URL absolues. Le disque S3 est esquissé dans `config/filesystems.php`,
mais son adaptateur Composer n’est pas installé.

La conversion existante utilise `disk->path()` pour construire un fichier
uploadé : cette hypothèse devra disparaître pour les disques S3.

## Architecture proposée

### Services Docker et démarrage

Ajouter Garage en mode mono-nœud et Garage Web UI à Compose. Fixer leurs
versions ou leurs digests après vérification de leur compatibilité et de
leur disponibilité pour l’architecture locale. Ne pas utiliser `latest`.

Conserver les données et métadonnées Garage dans des volumes persistants.
`make down` doit conserver ces volumes. Le démarrage ne doit jamais vider
les objets, réinitialiser les clés ou reconfigurer un cluster existant.

L’initialisation locale crée de façon relançable le layout mono-nœud, le
bucket `gamestackr-images`, une clé S3 pour l’application et la publication
en lecture du bucket. Elle s’intègre à `make init` et à `make up`, avec
attente bornée de disponibilité et erreur explicite en cas d’échec.
Un arrêt de Garage Web UI ne doit pas empêcher Laravel d’accéder aux images.

Les clés de l’application sont limitées au bucket d’images. Les secrets
RPC, le jeton d’administration et les paramètres privés sont générés une
seule fois dans des fichiers ignorés par Git, avec permissions restreintes.
Les redémarrages les réutilisent ; une configuration existante non vide ne
doit pas être remplacée silencieusement. Aucun secret ne figure dans les
exemples, les commits ou les sorties de vérification.

L’interface utilise son authentification native. Prévoir une commande locale
documentée pour initialiser ou renouveler son accès. Les ports destinés au
navigateur sont liés à `127.0.0.1` ; les ports RPC et administration de Garage
restent internes au réseau Docker. Le paramétrage est destiné au développement.

### Accès aux images et compatibilité S3

Laravel écrit et lit via l’API S3 interne, par exemple
`http://garage:3900`, avec adressage path-style. Installer l’adaptateur
Flysystem S3 compatible avec les dépendances verrouillées du projet.

Le navigateur accède aux images par le service web de Garage, avec une URL
publique distincte de l’endpoint Docker. Proposition locale :
`http://gamestackr-images.localhost:3902`, avec un domaine web Garage adapté.
Vérifier la résolution de cette adresse sur le poste et dans le navigateur.
Rendre l’URL configurable, indépendamment de l’endpoint S3 interne.

L’accès public au bucket utilise la fonctionnalité de publication web de
Garage. Il ne dépend pas des API AWS d’ACL ou de bucket policy, que Garage
n’implémente pas. Les paramètres d’upload émis par Flysystem doivent être
vérifiés contre le service réel ; aucun appel à `GetObjectAcl` ou
`PutObjectAcl` ne doit être nécessaire au fonctionnement de l’application.

Conserver les types MIME corrects, en particulier `image/avif`, et configurer
le CORS pour les lectures et aperçus depuis l’origine de l’application.
Les URL publiques ne contiennent aucun secret. Les images restent publiques
comme aujourd’hui, y compris si un jeu est masqué dans le catalogue.

### Intégration Laravel

Introduire un choix de disque dédié aux images des jeux, par exemple
`GAME_IMAGES_DISK`, centralisé dans la configuration. `public` reste le
repli pour un environnement existant qui ne déclare pas ce paramètre ;
les nouvelles installations fournies par `.env.example` utilisent `s3`.
Le disque par défaut global `FILESYSTEM_DISK` reste indépendant.

Faire utiliser ce choix à `GameImages`, `PublicGames`, au formulaire et à
la liste Filament, ainsi qu’à la commande de conversion. Conserver les
clés actuelles dans PostgreSQL et les règles de validation/encodage AVIF.

La conversion d’anciens WebP/SVG doit lire le disque choisi puis matérialiser
un fichier temporaire local pour les outils d’image. Le nettoyer même en
cas d’échec. Conserver la sauvegarde privée vérifiée avant conversion.

Conserver les suppressions après commit, le nettoyage après rollback, la
protection des chemins et les contrôles contre le partage d’un même fichier.
Une erreur de lecture ou d’écriture avant commit doit faire échouer la
sauvegarde et préserver l’ancienne référence et son fichier. Si la suppression
de l’ancien objet échoue après commit, l’opération métier reste validée et
l’erreur de nettoyage est journalisée, comme dans le comportement actuel.
L’échec d’un nettoyage après rollback est également journalisé et peut laisser
un objet orphelin. Ne pas ajouter de mécanisme persistant de réessai dans cette
étape. La cohérence reste compensée entre PostgreSQL et les fichiers, sans
transaction distribuée.

Ne pas ajouter de bascule automatique entre les deux stockages en cas de
panne : elle pourrait cacher une migration incomplète ou relire une ancienne
version. Les erreurs doivent rester visibles et diagnostiquables.

### Migration des images existantes

Fournir une commande explicite avec simulation, par exemple
`games:copy-images-to-s3 --dry-run`, puis sans cette option. Elle copie du
disque `public` vers `s3` toutes les clés gérées référencées par les jeux,
visibles ou masqués, en conservant exactement les clés et les contenus.

Comparer les contenus source et destination, avec un hash calculé à la
lecture plutôt que l’ETag S3. Une destination identique est ignorée ; une
destination différente constitue une erreur et n’est pas écrasée.
Un fichier absent, un chemin non géré ou une erreur de lecture/écriture
entraîne un bilan en échec. Les copies réussies restent réutilisables lors
d’une nouvelle exécution. La simulation ne crée aucun objet.

La commande ne modifie ni PostgreSQL ni le disque source, et n’effectue
pas elle-même la bascule de configuration. Sur l’environnement existant :
initialiser Garage, suspendre les éditions/imports/conversions et les workers
qui pourraient écrire pendant la copie, copier, vérifier, puis activer
`GAME_IMAGES_DISK=s3`, vider le cache de configuration et redémarrer les
processus concernés. Reprendre les écritures après validation de l’affichage.
Un échec laisse l’application sur son disque local.

Les originaux locaux sont conservés. Revenir à `public` immédiatement après
la copie est possible tant qu’aucune nouvelle écriture n’a eu lieu sur S3.
Après de nouvelles écritures, le retour demande une copie inverse vérifiée ;
ne pas présenter un simple changement de variable comme une restauration.

## Vérification attendue

- Exécuter les contrôles PHP existants et les tests du cycle de vie des images.
  Les tests ordinaires isolent le stockage avec `Storage::fake` et imposent
  le disque local de test, indépendamment du `.env` du développeur.
- Couvrir le choix du disque, les URL, les erreurs d’écriture, la migration
  relançable et ses conflits, ainsi que la conversion sans chemin local S3.
- Sur Garage réel, vérifier écriture, lecture, liste, remplacement, suppression,
  type MIME et affichage public ; les doubles de test ne prouvent pas la
  compatibilité S3 ni le bon paramétrage des droits.
- Vérifier dans le navigateur un import Filament, son aperçu AVIF, l’image
  sur le site et la présence du fichier dans Garage Web UI. Signaler les
  capacités d’aperçu constatées sans promettre une grille de miniatures.
- Redémarrer les services et relancer l’initialisation : fichiers et accès
  doivent subsister, sans duplication ni modification des clés.
- Ne pas supprimer ou remplacer de données réelles pendant les essais :
  utiliser des jeux/objets de test identifiables et ne nettoyer que ceux-ci.

## Choix laissés à l’implémentation

Les noms définitifs des commandes auxiliaires, la disposition des scripts,
les versions compatibles et les détails des healthchecks sont laissés à
l’implémenteur. Les ports proposés peuvent être ajustés en cas de conflit.
Tout cela doit rester documenté dans le README, avec les étapes pour les
installations neuves et les environnements existants.

L’implémentation sur le poste actuel prévoit la copie vérifiée puis la
bascule effective des images existantes, une fois le stockage et les tests
validés. Elle s’arrête avant cette bascule si un fichier ne peut pas être
copié ou vérifié, et conserve alors le stockage local actif.

## Sources consultées

- [Garage : démarrage mono-nœud](https://garagehq.deuxfleurs.fr/documentation/quick-start/)
- [Garage : compatibilité S3](https://garagehq.deuxfleurs.fr/documentation/reference-manual/s3-compatibility/)
- [Garage : publication web des buckets](https://garagehq.deuxfleurs.fr/documentation/cookbook/exposing-websites/)
- [Garage Web UI : fonctionnalités, Docker et authentification](https://github.com/khairul169/garage-webui)
- [SeaweedFS : Admin UI](https://github.com/seaweedfs/seaweedfs/wiki/Admin-UI)
- [MinIO : dépôt archivé](https://github.com/minio/minio)
