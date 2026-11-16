# Multilingue : routes traduites, fichiers de langue et contenus Spatie

Date : 16 novembre 2026. Statut : conception proposée, à valider avant réalisation.

## Intention et décisions utilisateur

Rendre le multilingue cohérent et maintenable sur GamestackrTCG. Ajouter une
langue doit demander des traductions et une entrée de configuration, sans
modifier les composants, les contrôleurs ou les ressources métier.

L’utilisateur a demandé un préfixe de langue dans les URL, des fichiers de
langue, et l’utilisation de `spatie/laravel-translatable` pour les données qui
s’y prêtent. Il a précisé que le périmètre couvre **tout**, administration et
e-mails compris. Il a choisi l’approche B : préfixe et chemins traduits.

Les autres décisions ci-dessous sont des propositions de l’agent, soumises à
la validation de ce document. Le choix B ne vaut pas encore approbation de
tous ces détails.

## Approches considérées

| Approche | Exemple | Compromis |
| --- | --- | --- |
| A : préfixe, chemins stables | `/fr/login`, `/en/login` | Intégration plus simple avec les packages ; segments non traduits. |
| B : préfixe et chemins traduits | `/fr/connexion`, `/en/login` | Correspondance des routes à centraliser ; URL dans la langue de l’interface. |

**B est retenue par l’utilisateur.** L’implémentation utilisera des identités
de routes stables derrière leurs chemins localisés. Les chemins ne seront pas
fabriqués par remplacement de mots dans une URL.

## État actuel vérifié

- `SetSiteLocale` choisit FR/EN via `?lang=` et un cookie ; `SetAdminLocale`
  impose le français dans Filament.
- `useLocale()` contient `t(fr, en)` ; les libellés React sont dispersés dans
  les composants. Authentification, compte et admin sont principalement en dur.
- Laravel 13, Fortify, Filament 5, Inertia 3, React 19, Wayfinder et Vite Plus
  sont installés. Les commandes restent dans Docker via Make.
- Les routes publiques utilisent le slug du jeu et les clés de catalogue et
  d’extension. Les noms de référence sont indépendants de ces clés.
- `series_localizations` contient nom, description **et image** ;
  `expansion_localizations` contient nom et description.
- Les éditions imprimées et sorties ont leur propre identité et leurs
  contraintes. Les services d’images et le seeder référencent les anciennes
  localisations : changer seulement les modèles serait insuffisant.
- Les noms affichés, la recherche et le tri reposent actuellement sur ces
  tables. La règle de repli des séries/extensions existe dans deux presenters.

## Périmètre et limites

Livrer FR et EN complets pour les textes de l’application : public, auth,
compte, administration, erreurs applicatives, validation, notifications et
e-mails. Conserver les composants et le design actuels.

Les contenus administrés deviennent traduisibles, sans inventer de traductions
ni modifier leurs noms officiels. Ajouter le support d’une langue d’interface
ne crée aucune édition imprimée ni aucun marché.

Les **segments de navigation** sont traduits ; les **identifiants métier**
existants restent stables. Ainsi une extension garde `base-set` en français
comme en anglais. La traduction des slugs de contenus, avec historique de
slugs et redirections par fiche, est hors de ce chantier.

Ne pas ajouter de traduction automatique, plateforme externe de traduction,
cartes, collections ou nouveau modèle de permissions. Les messages natifs du
navigateur et des logiciels externes comme Mailpit restent sous leur contrôle.

## Langues et préférence

Un registre `config/localization.php` définit les langues d’interface actives,
leur nom natif, tag HTML/Intl, identifiant Laravel, direction et langue de repli.
FR et EN sont actives au départ ; FR est la langue par défaut et de repli.
Les éventuelles différences `pt-BR`/`pt_BR` ont une correspondance explicite.

La table `languages` conserve son rôle de référentiel des langues des contenus
et éditions ; elle ne pilote pas l’activation des langues du site. Les tags
existants comme `ja` et `zh-Hant` sont conservés, même sans interface correspondante.

Pour les contenus, les langues proposées proviennent du référentiel `languages`.
Une synchronisation idempotente crée les entrées manquantes des langues
d’interface actives, dès la migration initiale puis lors de l’activation d’une
langue, sans dépendre du seeder d’exemples ni écraser les noms déjà administrés.
Les autres langues de contenu restent ajoutables dans Filament. Cette
synchronisation ne crée aucune édition imprimée.

La suppression d’une langue active du site, ou utilisée par une traduction JSONB,
une image, un catalogue ou une édition, est refusée. Compléter les contraintes
FK par des vérifications applicatives pour les références JSONB. Écritures de
traductions et suppression de langue verrouillent les mêmes lignes du référentiel
pour éviter une course. Les suppressions SQL de masse qui contournent ce contrat
ne font pas partie des API d’écriture supportées. Désactiver une langue du site
n’efface pas ses traductions ni sa ligne de référentiel.

Sur une route préfixée, **l’URL fait autorité**, devant cookie et utilisateur.
Sur une entrée historique sans langue, la sélection suit : ancien `lang`
valide, préférence du compte, cookie valide, puis FR. Pas de négociation
automatique via `Accept-Language` dans cette livraison.

Un changement explicite via le sélecteur enregistre la préférence en cookie
et, pour un compte connecté, dans `users.preferred_locale`. Une simple visite
d’un lien reçu ne modifie pas la préférence durable du compte. Une valeur
invalide ne remplace jamais une préférence valide. Une langue désactivée
retombe vers FR pour les préférences, mais son URL n’est pas interprétée comme
une autre langue.

## Routes et génération des liens

Les traductions des chemins résident dans `lang/{locale}/routes.php`, séparées
des libellés. Un registre applicatif associe une identité de route à sa méthode
HTTP, ses paramètres, son action, ses protections et son chemin traduit.
Chaque variante reçoit un nom Laravel unique et déterministe, par exemple
`site.fr.login` et `site.en.login`. Aucun nom dupliqué selon l’ordre de chargement.

| Page | Français | Anglais |
| --- | --- | --- |
| Accueil | `/fr` | `/en` |
| Connexion | `/fr/connexion` | `/en/login` |
| Inscription | `/fr/inscription` | `/en/register` |
| Espace personnel | `/fr/mon-espace` | `/en/dashboard` |
| Profil | `/fr/parametres/profil` | `/en/settings/profile` |
| Sécurité | `/fr/parametres/securite` | `/en/settings/security` |
| Jeu | `/fr/pokemon` | `/en/pokemon` |
| Extension | `/fr/pokemon/international/base-set` | `/en/pokemon/international/base-set` |
| Jeux dans l’admin | `/fr/admin/jeux` | `/en/admin/games` |
| Modifier un jeu | `/fr/admin/jeux/42/modifier` | `/en/admin/games/42/edit` |

`admin` est un segment commun aux deux langues. Les autres chemins visibles,
y compris création, services de développement et parcours d’authentification,
suivent les fichiers de routes. Les paramètres et noms techniques restent
identiques entre langues.

Un service de liens localisés est l’entrée commune pour PHP, notifications,
Inertia et sélecteurs. Côté React, un adaptateur typé s’appuie sur les routes
générées par Wayfinder ; ne pas modifier ses fichiers générés à la main.
Les appels actuels aux routes et actions non localisées sont migrés.

Le changement de langue conserve l’identité de page, les paramètres métier,
les filtres, la pagination, `edition` et le fragment. Il supprime `lang`.
Les valeurs de filtres restent des clés techniques ; changer la langue du
site ne change pas l’édition choisie. Une page inaccessible ne devient pas
accessible via sa variante dans une autre langue.

Les routes fixes précèdent les routes de jeux. Le contrôle des slugs réservés
couvre l’union des segments applicatifs traduits et les préfixes de langues.
Un audit préalable signale les collisions avec des jeux existants ; aucune
fiche n’est renommée ou masquée automatiquement pour résoudre une collision.
Les langues non actives et chemins traduits inconnus renvoient 404.

L’enregistrement des routes et des panels doit être déterministe en CLI,
indépendant de la requête, du cookie, de la base et de la locale ambiante.
`route:cache`, `config:cache` et la génération Wayfinder doivent fonctionner.

### Compatibilité des anciennes URL

Conserver des routes de compatibilité explicites pour les anciens GET/HEAD
publics, compte, authentification et admin. Elles redirigent vers la variante
localisée avec paramètres conservés, sans boucle ni cible externe.
Une redirection dépendant de la préférence utilise 302 et un cache privé ;
une normalisation à destination fixe peut utiliser 301. Ne pas mettre en
cache public un choix dépendant d’un cookie.

Les anciens endpoints de mutation restent temporairement des alias traitant
la requête avec les mêmes contrôleurs et protections, puis redirigeant vers
la nouvelle page. Ne pas convertir un POST en GET avant son traitement.
Documenter cette compatibilité dans un module isolé, avec tests et inventaire,
pour pouvoir la retirer lors d’une décision ultérieure.

Les liens de vérification d’e-mail signés existants sont validés sur leur URL
originale avant toute redirection. Les nouveaux liens sont signés directement
sur leur URL localisée. Le sélecteur ne réécrit jamais une signature ; sur les
pages d’action signée, il est absent jusqu’à l’arrivée sur une page ordinaire.
Les liens de réinitialisation conservent token et e-mail sans les journaliser.

### Fortify, e-mails et endpoints techniques

Réutiliser les contrôleurs, actions, contrats de réponse et protections de
Fortify. L’adaptateur de routage couvre inscription, connexion, déconnexion,
vérification d’e-mail, mot de passe, confirmation, 2FA et passkeys, y compris
leurs requêtes de mutation. Ne pas recopier la logique d’authentification.
Les noms historiques que Fortify attend disposent d’un pont explicite ;
les réponses et notifications génèrent les nouvelles URL directement.

La destination « intended » reste locale et conserve la langue de la page
demandée, y compris après 2FA et lors du passage vers Filament. Le rechargement
complet Inertia nécessaire pour entrer dans Filament reste pris en charge.

Les e-mails utilisent la préférence du destinataire, avec FR en repli ; à
l’inscription, la langue du formulaire initialise cette préférence. La locale
est portée explicitement dans les notifications et restaurée après traitement
d’un job pour éviter les fuites entre utilisateurs. Aucune locale n’est inférée
d’une session HTTP dans un worker. Les tests capturent le courrier sans envoi réel.

Les endpoints techniques restent à leurs emplacements attendus : `/up`,
assets, stockage, transport Livewire, et `/.well-known/passkey-endpoints`.
Leurs réponses et les URL applicatives qu’ils renvoient respectent la locale
lorsqu’elle est pertinente. Ne pas modifier le domaine WebAuthn ni ses origines.

## Fichiers de langue et React

Adopter les fichiers PHP Laravel et des clés sémantiques : `common`, `auth`,
`account`, `catalog`, `admin`, `validation`, `passwords`, `notifications`,
`routes`. Les overrides Filament existants restent dans `lang/vendor`.
Textes, titres, descriptions, aides, placeholders, accessibilité, toasts,
messages de règles métier et états vides entrent dans ce périmètre.

Les composants utilisent des clés telles que `auth.login.title`. Pas de
branche conditionnelle FR/EN dans les vues. Les valeurs des enums métier
restent stables ; seuls leurs libellés sont traduits.

Les fichiers PHP sont la source éditable. Le dictionnaire destiné à React
est exporté de façon déterministe ; ne pas maintenir une seconde traduction
manuelle des mêmes textes. Seuls les domaines utiles à React sont exportés,
sans charger les traductions de l’administration dans le parcours public.

Une bibliothèque compatible avec les conventions Laravel assure interpolation
et pluriels côté React ; éviter un moteur linguistique maison. Le choix et la
version de cette passerelle restent au constructeur, après vérification avec
React 19, Inertia 3 et Vite Plus. `laravel-react-i18n` est une piste documentée,
pas une compatibilité déjà validée. La locale serveur reste l’autorité lors
des navigations Inertia ; le rendu ne doit pas montrer brièvement une autre langue.

Générer ou vérifier les clés TypeScript à partir des fichiers, avec contrôle
de parité FR/EN, paramètres et formes plurielles. Une traduction manquante
utilise le repli en production et fait échouer le contrôle des langues actives.
Les chemins de routes n’acceptent aucun repli silencieux : leur fichier doit
être complet avant activation de la langue. Les dates et nombres utilisent
Intl avec la locale effective ; les jours civils gardent leur sémantique actuelle.

## Administration Filament

Proposition technique : un panel enregistré par langue active (`admin-fr`,
`admin-en`), construit par une même fabrique avec les mêmes ressources,
politiques, widgets et données. Ce sont les variantes linguistiques d’une
seule administration, pas des espaces avec des permissions différentes.

La locale du panel est explicite lors de l’enregistrement des slugs et pages
(`getSlug(Panel)`, enregistrement de pages), ainsi qu’au traitement HTTP et
Livewire. Éviter toute dépendance à `app()->getLocale()` lors du calcul des
chemins en CLI et tout cache statique de libellé dans la première langue.
Les noms de routes internes Filament peuvent suivre sa convention ; les liens
passent par les API des ressources et le registre des panels, sans chaînes
`filament.admin...` éparpillées.

L’accès vérifie le panel dans la liste exacte des panels d’administration
enregistrés, puis les conditions actuelles `is_admin` et e-mail vérifié.
Ne pas remplacer ce contrôle par un préfixe de nom trop permissif. Les
restrictions des outils de développement hors environnement local restent intactes.

Un sélecteur de langue de l’interface ouvre la même ressource/page dans le
panel cible. Activer le garde de formulaire non enregistré fourni par Filament
afin qu’un changement de langue ne perde pas silencieusement la saisie.
Le choix de **langue du contenu à éditer** est distinct de ce sélecteur :
on peut éditer un nom japonais avec une administration française.

Les formulaires multilingues montrent les valeurs réellement enregistrées,
sans injecter une valeur de repli dans un champ vide. Enregistrer une langue
ne recopie pas et n’efface pas les autres traductions. La dernière lecture
est verrouillée avant fusion des changements par langue pour prévenir les
écrasements concurrents de colonnes JSON.

## Données traduisibles avec Spatie

Utiliser `spatie/laravel-translatable` v6, dans une version compatible résolue
et verrouillée via Composer, avec des colonnes PostgreSQL JSONB. Les valeurs
de repli ne doivent pas être enregistrées comme de nouvelles traductions.

Les champs `name` actuels sont conservés comme **noms de référence**, car leur
langue n’est pas toujours connue et ils servent aussi aux imports et images.
Un attribut `display_name` porte les noms d’affichage traduits. Cela préserve
une vraie distinction métier et évite de qualifier arbitrairement de français
un nom japonais ou anglais. Les presenters exposent toujours un simple `name`
résolu aux consommateurs, jamais un objet JSON brut.

| Modèle | Champs Spatie | Données stables ou traitement spécifique |
| --- | --- | --- |
| Game | `display_name`, `description` | `name`, slug, éditeur, années, image et visibilité conservés. |
| Catalog | `display_name`, `description` | `name`, clé et langue de présentation par défaut conservés. |
| Series | `display_name`, `description` | `name` et clé conservés ; images par langue séparées. |
| Expansion | `display_name`, `description` | `name` et clé conservés ; éditions séparées. |
| Market | `display_name` | Nom de référence et clé conservés, y compris zones personnalisées. |
| ExpansionRelease | `announced_period` | Date, marché, canal, statut et source restent des faits stables. |
| Language | Aucun | Nom de langue affiché via Intl ; `name` en repli pour tags non reconnus. |
| ExpansionEdition | Aucun | Nom officiel dans la langue d’impression, code et image conservés. |
| ExpansionRelation | Aucun | Notes de source conservées ; elles ne sont pas un texte public traduit. |

Pour les champs textuels actuels sans langue connue (`Game.description`,
`Catalog.description`, `ExpansionRelease.announced_period`), conserver leur
valeur dans un champ `reference_*` dédié. Initialiser le JSON à vide plutôt
que deviner une langue. Les administrateurs peuvent ensuite saisir les
traductions. Pour les séries/extensions, recopier exactement les localisations
existantes vers les clés de langue explicites.

Les champs de référence restent modifiables et effaçables dans une section
clairement nommée des formulaires Filament, indépendante de la langue éditée.
Effacer une référence retire ce repli partout sans effacer les traductions.
Les noms de référence restent obligatoires comme aujourd’hui ; les autres
champs de référence sont facultatifs.

Un seul résolveur de présentation fournit valeur, langue effective et indicateur
de repli. Pour les séries/extensions : langue demandée, langue du catalogue,
nom de référence. Pour les autres textes : langue demandée, FR, valeur de
référence éventuelle. Une absence de description reste une absence. Les
champs sont résolus séparément et leur provenance reste disponible ; un texte
de référence de langue inconnue n’est pas marqué à tort `lang="fr"`.

Pour les textes facultatifs, **clé absente** signifie « traduction non saisie,
repli autorisé » et **clé présente à `null`** signifie « absence volontaire,
arrêter le repli ». Une chaîne vide saisie est normalisée à `null`. La migration
préserve donc aussi les descriptions nulles des localisations existantes :
une fiche FR sans description ne récupère pas soudain une description EN.
L’action explicite de retirer une traduction supprime ses clés et autorise
à nouveau le repli. Les noms traduits présents sont toujours non vides.
Le résolveur inspecte la présence des clés ; il ne délègue pas cette distinction
au comportement implicite de Spatie. Tester que le stockage conserve les nulls.

Les noms officiels des éditions restent tels quels et portent leur vraie
langue. Pour les textes traduits, aucun fallback arbitraire vers « la première
langue disponible ». La sélection d’une **édition imprimée** garde sa règle
actuelle : édition visible demandée, puis langue du catalogue, puis première
édition visible selon l’ordre stable existant ; le repli demandé indisponible
reste annoncé. Ce choix utilise le logo de l’édition effectivement sélectionnée,
sans le présenter comme le logo d’une édition différente.

La recherche conserve son périmètre actuel : noms d’extensions traduits ou de
référence, noms et codes des éditions visibles. Ne pas l’étendre aux descriptions
ou à d’autres entités. Le tri utilise le nom effectivement affiché. Refaire
les requêtes PostgreSQL en conservant visibilité, recherche latine sans accents,
pagination avant sérialisation et ordre stable en cas d’égalité. Les colonnes
et locales SQL sont validées, jamais interpolées depuis une entrée libre.

### Images localisées

Créer une entité `SeriesImage` avec série, langue et chemin, unicité
série/langue, pour reprendre les images de `SeriesLocalization`. Un texte
traduit et un fichier ont des cycles de vie différents. Les images ne passent
pas dans le fallback automatique Spatie : seule l’image de la langue demandée
peut être choisie, sinon absence explicite.

Conserver chemins et objets Garage ; la migration ne réencode, ne déplace et
ne supprime aucun fichier. Adapter SaveCatalogRecord, observers, nettoyage et
recherche des références pour inclure `SeriesImage`. Garder les garanties de
validation AVIF/SVG, transaction et suppression après commit. Le nom de référence
sert aux chemins d’images de jeux, pour qu’une traduction ne les renomme pas.

## Migration et conservation des données

Procéder en étapes vérifiables avec nouvelles migrations, sans réécrire les
migrations historiques. Préparer un audit en lecture seule et une simulation
comptant entités, traductions, images et liaisons d’exemples. Les anomalies et
collisions bloquent la bascule avec un rapport précis.

Sur une installation existante, prévoir une fenêtre de maintenance et suspendre
les workers pour éviter des écritures entre ancien et nouveau modèle. Ajouter
les colonnes/tables, copier les données en SQL ou Query Builder sans déclencher
les observers de suppression, vérifier l’équivalence des valeurs et relations,
puis activer le nouveau chemin de lecture/écriture. Ne pas maintenir deux sources
actives de traductions. Retirer les anciennes tables et classes seulement après
la validation complète de la copie, dans une étape finale explicite du déploiement.

Adapter aussi le manifeste et `CatalogExampleBinding`. Une traduction n’a plus
l’ID d’une ligne de localisation : la liaison doit identifier son parent et sa
locale. Prévoir `record_type`, `record_id`, `locale` nullable avec unicité adaptée
aux valeurs nulles PostgreSQL. Migrer les liaisons avant suppression des sources ;
plusieurs langues d’un parent ne doivent pas entrer en conflit. Les images sont
rattachées à leur série/langue sans réimport ni perte de chemin.

Le seeder reste facultatif, relançable et conservateur : aucune publication,
aucune réécriture d’un champ existant ou volontairement vidé. Une traduction
explicitement supprimée peut être recréée comme une ancienne localisation
supprimée ; une traduction encore présente conserve tous ses champs et son
absence éventuelle d’image. Tester aussi les liaisons après renommage d’un jeu.

Ne pas promettre un `down()` destructif après de nouvelles écritures. Avant
réouverture, une sauvegarde cohérente permet le retour à la version précédente.
Après réouverture, privilégier une correction en avant ; une restauration exige
de traiter les écritures intervenues entre-temps. Documenter et tester le retour
possible à chaque étape. Les exports et sauvegardes restent privés et hors Git.

## Métadonnées et liens publics

Définir le `lang` et la direction du document dès le rendu initial et après
navigation Inertia. Les pages publiques indexables disposent d’une canonical
dans leur langue et de liens `hreflang` réciproques vers les variantes actives
de la même fiche ; `x-default` pointe vers la variante FR déterministe.
L’admin et les pages de compte/authentification ne reçoivent pas de variantes
indexables. Les signatures, tokens et paramètres de suivi sont exclus des
métadonnées. Les critères de canonicalisation des filtres seront documentés
dans le plan ; ils ne doivent pas perdre l’identité d’une page paginée.

## Ajouter une langue

1. Ajouter son entrée au registre, désactivée pendant la préparation.
2. Fournir les fichiers de texte et tous les chemins de routes ; vérifier les
   traductions Filament/Laravel nécessaires et compléter les overrides manquants.
3. Exécuter les contrôles de clés, paramètres, pluriels, collisions et routes.
4. Activer la langue, synchroniser son entrée de référentiel avec la commande
   idempotente documentée, reconstruire les assets et régénérer les caches de routes.
5. Ajouter librement les traductions de contenus dans Filament ; elles peuvent
   être partielles et utiliser le repli explicite.

Aucune modification de composants React, ressources Filament ou contrôleurs
métier n’est nécessaire pour une troisième langue. Les besoins RTL demandent
en plus une vérification visuelle ; seuls FR/EN sont livrés et validés ici.

## Validation attendue avant livraison

- Tests HTTP FR/EN pour routes, redirections historiques, 404, collisions,
  préférence, query strings, pagination, visibilité et caches.
- Parcours complets Fortify : invité, connexion, inscription, vérification,
  réinitialisation, confirmation, 2FA et passkeys ; signatures historiques
  valides, signatures modifiées refusées et redirections intended conservées.
- Notifications et jobs avec destinataires de langues différentes, sans fuite.
- Admin dans les deux langues : accès, listes, création, édition, erreurs,
  Livewire, outils locaux et passage depuis Inertia. Interface et contenu
  édité indépendants ; deux langues sauvegardées concurremment préservées.
- Migrations sur anciennes fixtures avec plusieurs langues, références sans
  langue, descriptions nulles, images partagées et bindings de seeder ; égalité
  avant/après, absence de suppression Garage, relance du seeder conservatrice.
- Tests de présentation, tri/recherche JSONB et repli, avec contrôles garantissant
  qu’une traduction ne crée jamais une édition et qu’un logo ne change pas de langue.
- Distinction clé absente/null explicite, édition et effacement des références,
  langues FR/EN disponibles sans exemples, et refus de suppression d’une langue
  référencée, y compris face à une écriture concurrente de traduction.
- Test d’ajout d’une troisième langue temporaire de test sans patcher du code
  métier, couverture des pluriels et validation des fichiers des langues actives.
- `make check`, `make assets`, puis `make up` et `make test-storage` ; tests
  exclusivement sur `gamestackr_testing`, avec les garde-fous actuels.
- Vérification navigateur desktop/mobile FR/EN, y compris navigation arrière,
  formulaires et sélecteurs. Revue indépendante, PR et CI selon `AGENTS.md`.

## Choix laissés au constructeur

Noms précis des petites classes/adaptateurs, découpage des commits, bibliothèque
de traduction React compatible, détails de génération des types, stratégie
d’index JSONB après mesure et organisation des fixtures. Ces choix ne peuvent
affaiblir les contrats de langue, d’URL, de conservation ou d’accès ci-dessus.

La réalisation est un chantier transversal et demande un plan par étapes.
Le plan commencera par vérifier les intégrations routes/Filament/Fortify et la
passerelle de traduction avant d’appliquer les migrations aux données réelles.
Ce document n’autorise pas à présenter comme exécutés ces contrôles futurs.

## Relecture de la conception

Le 16 novembre 2026, un agent indépendant a relu cette conception pour vérifier
qu’un constructeur pouvait en tirer un plan. Ses questions portaient sur les
nulls explicites, les langues de contenu et leur suppression, l’édition des
références historiques et le choix d’édition imprimée. Les clarifications ont
été ajoutées ci-dessus ; sa seconde lecture conclut « nothing important left ».
Il s’agit d’une revue de conception, pas de la revue de code ni des tests
nécessaires à la livraison. La validation utilisateur reste attendue.

## Sources et points d’appui

- [Laravel : localisation](https://laravel.com/docs/13.x/localization).
- [Laravel : génération des URL](https://laravel.com/framework/docs/urls).
- [Spatie : introduction et stockage des traductions](https://spatie.be/docs/laravel-translatable/v6/introduction).
- [Spatie : règles de repli](https://spatie.be/docs/laravel-translatable/v6/basic-usage/handling-missing-translations).
- [Filament 5 : panels](https://filamentphp.com/docs/5.x/panel-configuration).
- [Passerelle React/Laravel à évaluer](https://github.com/EugeneMeles/laravel-react-i18n).

Les mécanismes de routage ont aussi été inspectés dans les versions installées
de Fortify et Filament (`routes`, `PanelProvider`, `Resource::HasRoutes`,
`PageRegistration`). Cette lecture établit les points d’extension disponibles,
pas une validation exécutée de la future intégration.
