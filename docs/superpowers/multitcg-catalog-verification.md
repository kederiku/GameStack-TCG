# Catalogue multi-TCG — vérifications du 15 novembre 2026

## Périmètre

Le catalogue sépare jeu, catalogue éditorial, série facultative, ensemble,
traduction d’affichage, édition linguistique et sortie par marché/canal.
Les séries et éditions possèdent leurs propres images AVIF. Filament gère ces
fiches et leurs rapprochements avec des droits réservés aux administrateurs
vérifiés. Les cartes, variantes, tirages, produits scellés et pages publiques
du catalogue restent les étapes suivantes décrites par la conception.

Conception : [spécification](specs/2026-11-15-multitcg-catalog-design.md),
[audit des jeux présents](multitcg-compatibility-audit.md),
[plan](plans/2026-11-15-multitcg-catalog.md).
Branche de travail : `codex/multitcg-catalog-design`, base `8183537`.

## Contrôles réalisés pendant l’implémentation

Toutes les commandes PHP, Composer et Node passent par Docker via Make.
Les tests utilisent exclusivement `gamestackr_testing`.

| Étape               | Preuve rouge                                                                              | Résultat ciblé           | Suite PHP complète          |
| ------------------- | ----------------------------------------------------------------------------------------- | ------------------------ | --------------------------- |
| Domaine, `7b9c611`  | 15 tests échouent avant les nouveaux modèles                                              | 15 tests, 92 assertions  | 220 tests, 1 369 assertions |
| Images, `6d962a1`   | 8 tests échouent avant la gestion dédiée                                                  | 8 tests, 50 assertions   | 228 tests, 1 420 assertions |
| Filament, `139491d` | 12 tests échouent avant les ressources ; régressions de sélecteurs reproduites séparément | 14 tests, 215 assertions | 242 tests, 1 635 assertions |

Pint et PHPStan ont réussi à chaque étape. Les revues indépendantes de ces
trois étapes ont approuvé leur conformité et qualité. Le seeder a également
été approuvé sur `97ee55a`. La PR consigne le SHA et la conclusion de la revue
globale de la branche, ainsi que les contrôles CI avant fusion.

Après `make up`, `make test-storage` sur `6d962a1` : **3 tests, 27 assertions**
sur Garage réel, avec nettoyage des seuls objets créés par les tests.
`make assets` sur `139491d` : succès. Le plugin de polices émet l’avertissement
déjà présent sur la dépendance optionnelle `fontaine` ; aucune erreur de build.

Le premier `make check` a confirmé les 248 tests PHP, Pint et PHPStan, puis
signalé le formatage de quatre documents Markdown. Le formateur a corrigé
ces documents sans changement applicatif. Le deuxième `make check` sur
`97ee55a` a réussi : **248 tests, 1 730 assertions**, Pint (189 fichiers),
PHPStan (144 fichiers), formatage frontend (127 fichiers), lint (69 fichiers)
et TypeScript. `make test-storage` a de nouveau réussi : **3 tests, 27 assertions**.

Après le correctif d’intégration `b057846`, `make check` réussit :
**250 tests, 1 772 assertions**, Pint, PHPStan, formatage, lint et TypeScript.
Le contrôle ciblé Filament compte **16 tests, 257 assertions**. La revue
indépendante du correctif n’a relevé aucune nouvelle régression.

La revue globale sur `6ef551b` a relevé une redirection de connexion Inertia
vers Filament incompatible avec une navigation normale, ainsi qu’un nom de
connexion absent du nettoyage d’image après rollback. Le correctif `230ab38`
résout ces deux points. Les régressions ont été reproduites avant correction
(trois échecs), puis les contrôles ciblés ont réussi : **32 tests, 184 assertions**.
Le dernier `make check` sur cette version a réussi : **254 tests, 1 803 assertions**,
Pint, PHPStan, formatage, lint et TypeScript. `make test-storage` a également
réussi sur cette version : **3 tests, 27 assertions** sur Garage réel.

## Échantillon du seeder

Le manifeste contient 167 fiches : 12 jeux publiés, 6 langues, 7 marchés,
19 catalogues, 7 séries, 8 localisations de série, 29 ensembles,
11 localisations d’ensemble, 34 éditions linguistiques, 27 sorties et
7 rapprochements. Quatre images sont enregistrées sous des chemins AVIF
indépendants. Les fiches existantes sont adoptées, sans réécriture de leurs
valeurs. Le Naruto de Bandai reste une note d’annonce ; aucune édition
non documentée n’est créée.

Les sources primaires et leur portée sont conservées dans le manifeste
de sources. Les dates ou langues non vérifiées restent absentes.

## Parcours navigateur

Le contrôle manuel a utilisé le port temporaire `8010`, un compte jetable et
la base `gamestackr_testing`. Un routeur temporaire vérifie cette base avant
chaque requête applicative. Le compte et les images de test ont été nettoyés,
et le conteneur temporaire arrêté après le parcours.

- Création d’un catalogue Pokémon et d’une série contenant des caractères japonais.
- Création d’une localisation française et téléversement de son image AVIF.
- Création d’une extension : seules les séries de son catalogue sont proposées.
- Création d’une édition anglaise avec un visuel différent, puis ouverture de
  sa fiche et retour possible à l’extension.
- Ajout d’une sortie publiée, marché États-Unis, distribution générale et
  date civile ; l’édition est publiée en base.
- Refus de suppression de la série encore utilisée, avec notification :
  « Cette série est rattachée à des extensions. Détachez-les avant la suppression. »

Les libellés et actions ajoutés sont français. Les textes génériques de
Filament suivaient alors la langue anglaise du panneau. Le registre des langues du site configure désormais les panneaux localisés.

Le parcours a révélé un état de formulaire périmé après publication d’une
sortie : l’édition est publiée en base mais son sélecteur affichait encore
« Annoncée » jusqu’au rechargement. Le correctif `b057846` actualise ce seul
champ à partir de la base. Le second parcours navigateur confirme le passage
immédiat à « Publiée », la conservation du nom et du code saisis avant l’ajout
de la sortie, puis une sauvegarde réussie du formulaire parent.

Le dernier parcours confirme aussi la connexion depuis une URL d’édition
Filament : après saisie des identifiants, le navigateur ouvre directement
la fiche demandée, sans fenêtre d’erreur Inertia. La connexion avec deuxième
facteur et la conservation des destinations Inertia ordinaires sont couvertes
par les tests automatisés. Le serveur temporaire a ensuite été arrêté, et
son compte et ses images supprimés de la base de tests.

Deux corrections ont concerné uniquement le banc d’essai : `artisan serve`
filtrait les variables de configuration du sous-processus (première connexion
refusée, avant toute mutation du catalogue) ; le routeur dédié impose désormais
la base avant le bootstrap. L’environnement HTTP `qa` évite le disque fictif
Livewire réservé aux tests unitaires, et le cache en base permet de vérifier
les notifications entre requêtes. Aucun garde-fou de `tests/TestCase.php` n’a
été modifié.

## Installation locale des exemples

Après revue du seeder et contrôles, `make migrate` a installé la migration
additive des associations du manifeste. Le seeder a ensuite été lancé
explicitement avec `make artisan ARGS='db:seed --class=TcgCatalogExamplesSeeder'`.

- Première exécution : **155 fiches créées, 12 conservées, 0 recréée**.
- Deuxième exécution : **0 créée, 167 conservées, 0 recréée**.
- Les empreintes des **13 jeux existants** sont identiques avant et après :
  noms, slugs, images, visibilité et autres attributs conservés.
- Le nombre d’utilisateurs est inchangé. Le seeder ne crée aucun compte.

## Données et limites

La migration initiale du catalogue a été appliquée localement après revue
et tests : elle ajoute des tables vides. Les empreintes des 13 jeux existants
et le nombre d’utilisateurs ont été relevés pour contrôler leur conservation
lors de l’installation explicite des exemples, sans journaliser les données
des utilisateurs.

Les visuels d’exemple Pokémon proviennent de sources officielles. Le français
utilise l’aperçu public de 240 × 65 px, l’anglais un visuel de 360 × 103 px.
La provenance et les empreintes figurent dans
[le dossier des assets](../../database/seeders/assets/tcg-catalog/README.md).
Le logo de l’extension de base sert aussi de présentation de sa série, choix
éditorial documenté ; chaque propriétaire reçoit un fichier indépendant.

Une panne disque ou un arrêt brutal reste une limite à l’atomicité entre
PostgreSQL et le stockage, comme pour les images de jeux existantes.

## Décisions d’exécution

- Réutiliser le checkout et la branche de la tâche préserve le montage Docker
  existant ; cette décision évite de dupliquer l’environnement.
- L’instruction de commencer le développement approuve l’exécution de la
  conception ; aucune nouvelle validation n’a été demandée entre les étapes.
- Le parcours manuel prévu utilise une instance temporaire sur la base de
  tests et un compte jetable, afin de vérifier les mutations sans toucher
  aux fiches et comptes de l’application.
- Le seeder refuse un conflit qui imposerait de promouvoir une édition
  existante : cela préserve le statut choisi par l’utilisateur, au prix
  d’une correction manuelle de cette fiche avant de relancer le seeder.
- Le défaut préexistant de connexion Inertia vers Filament est corrigé dans
  ce lot pour que l’accès direct aux nouvelles pages fonctionne après
  connexion ; cette extension limitée du périmètre est couverte par des
  tests de connexion classique, Inertia et avec deuxième facteur.
