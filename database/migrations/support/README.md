# Conversion de contenu localisé V1

`LocalizedContentV1.php` est un support versionné, chargé explicitement avec
`require_once database_path('migrations/support/LocalizedContentV1.php')`.
Ce fichier n'est pas une migration enregistrée automatiquement. La tâche 7
ne l'exécute que dans des schémas isolés de `gamestackr_testing`. La migration
applicative et la finalisation sont des étapes de déploiement distinctes.

## Contrats

- `audit(Connection): array` et `localization:audit` lisent seulement les
  données. Le résultat est `{ok: bool, counts: array<string,int>, problems:
  list<string>}`. La commande renvoie 0 si l'audit est valide, 1 sinon.
- `up(Connection): void` verrouille les tables historiques, audite, calcule
  l'état attendu depuis les sources, puis convertit et compare dans une seule
  transaction PostgreSQL. Toute erreur annule aussi les DDL. Une seconde
  exécution est refusée pour protéger les traductions ajoutées ensuite.
- `verify(Connection): array` est un contrôle strict d'équivalence **avant
  réouverture et avant suppression des sources**. Il ne modifie jamais la preuve
  pour accepter des données différentes. Il ne remplace pas l'audit général,
  qui autorise les traductions valides ajoutées après la conversion.

Les colonnes JSONB ont `{}` comme valeur initiale. Les noms de référence restent
inchangés ; les descriptions de jeux/catalogues et les périodes de sortie
historiques deviennent respectivement `reference_description` et
`reference_announced_period`. Les descriptions localisées gardent leurs clés
à `null`. L'ordre des clés d'un objet JSONB n'est pas significatif.

Les codes sont canonicalisés sans modifier `languages` : `FR` → `fr`,
`zh-hant` → `zh-Hant`, `pt-br` → `pt-BR`. Les langues de contenu inactives sur
le site restent valides. Les consommateurs doivent utiliser la même
canonicalisation, pas le code brut ni un `strtolower` de tous les tags.

`series_images` contient `id`, `series_id`, `language_id`, `image`,
`created_at`, `updated_at`. Les ID des images copiées reprennent les ID des
localisations correspondantes ; la séquence est ensuite ajustée. Seuls les
chemins non nuls créent une image. Il n'y a aucun accès au stockage.

Les bindings gardent leur `manifest_key`. Une localisation devient un binding
`record_type=series|expansion`, `record_id=<parent>`, `locale=<code canonique>`.
Les autres bindings ont `locale=null`. L'index unique
`catalog_example_bindings_record_locale_unique` utilise PostgreSQL
`NULLS NOT DISTINCT(record_type, record_id, locale)`.
Pour une localisation supprimée, V1 retire le dernier segment de langue de sa
clé et remplace le préfixe `*-localization:` par le type parent pour retrouver
**la liaison de manifeste parente**. Il ne recherche jamais un nom ou un slug.

## Preuve persistante et finalisation explicite

La table `localization_conversion_verifications` contient une ligne `version=1` :

- `source_hashes` : empreintes SHA-256 des deux tables de localisations retenues ;
- `target_hashes` : empreintes SHA-256 des lignes attendues de toutes les tables
  du catalogue, des bindings transformés et des images, avec valeurs et nulls ;
- `counts` : comptes des entités, images, traductions et descriptions nulles ;
- `verified_at` : renseigné seulement après l'égalité réelle source/cible ;
- `finalized_at` : nul tant que les sources n'ont pas été explicitement retirées.

La preuve ne contient ni contenu exporté ni identifiants d'accès. Les objets
JSON sont comparés sans dépendre de l'ordre de leurs clés. Les sources sont
protégées contre INSERT/UPDATE/DELETE/TRUNCATE par les triggers
`localization_v1_read_only` et la fonction de même nom.

La finalisation doit, pendant la maintenance et dans une transaction avec
verrouillage des tables concernées :

1. Exiger la ligne V1 avec `verified_at` et `finalized_at=null`.
2. Appeler `verify()` et refuser toute anomalie ; ne jamais régénérer les hashes.
3. Supprimer `series_localizations`, `expansion_localizations`, puis la fonction
   `localization_v1_read_only()` ; conserver la preuve.
4. Renseigner `finalized_at` dans cette même transaction.
5. Exécuter l'audit général. Il accepte l'absence des deux sources uniquement
   avec cette preuve finalisée. `verify()` reste réservé à l'étape précédente.

## Retour avant réouverture

Avant la bascule réelle, mettre l'application en maintenance, suspendre les
workers et toute écriture externe. Réaliser une sauvegarde PostgreSQL cohérente
et vérifier sa restauration sur une destination isolée. Conserver la sauvegarde,
les exports et les journaux contenant des données dans un emplacement privé
hors Git. Conserver également la version du code et les lockfiles correspondants.
Aucun volume Docker ne doit être supprimé.

Une erreur pendant `up()` annule intégralement sa transaction ; le test
`test_failed_copy_rolls_back_schema_values_and_bindings` injecte une panne
pendant la copie et vérifie données, bindings et ancien schéma. Après une
conversion validée mais avant réouverture, revenir à la sauvegarde cohérente
et au code précédent, vérifier l'ancien schéma et ses références, puis seulement
reprendre les workers et le trafic. Après finalisation, le même retour exige
cette sauvegarde, car les tables sources ont été supprimées.

Il n'existe pas de `down()` destructif : après réouverture, une restauration
écraserait les traductions et autres écritures récentes. Privilégier une
correction en avant ; un retour nécessite d'abord de préserver et réconcilier
ces écritures. La répétition de `up()` n'est pas une procédure de retour.
