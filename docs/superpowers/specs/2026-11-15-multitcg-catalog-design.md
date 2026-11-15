# Catalogue multi-TCG, administration Filament et exemples réels

Statut : proposition à valider avant implémentation. Date : 15 novembre 2026.

## Intention et décisions de l’utilisateur

Organiser plusieurs jeux de cartes à collectionner, leurs séries/blocs et leurs
extensions dans Laravel et Filament. Un nom ou un visuel peut varier selon la
langue. Le catalogue doit couvrir les publications occidentales, japonaises,
coréennes, chinoises simplifiées et chinoises traditionnelles sans supposer une
correspondance universelle entre leurs extensions.

L’utilisateur a choisi des catalogues éditoriaux distincts, avec des relations
explicites entre extensions apparentées. Il demande également un seeder avec
des exemples réels de plusieurs TCG et des différents cas de figure.

Les détails ci-dessous, notamment les cinq jeux exemples, les champs, les règles
de suppression et le fonctionnement du seeder, sont des propositions de l’agent.
Ils ne constituent pas encore une validation du document par l’utilisateur.

## Situation actuelle

- `Game`, sa ressource Filament, les permissions administrateur et la navigation
  publique par jeu existent. Les pages publiques des jeux restent des espaces
  réservés au futur catalogue.
- Les images de jeux sont validées et converties en AVIF, stockées sur Garage
  ou le disque historique public et nettoyées par des actions/événements Eloquent.
- `DatabaseSeeder` contient encore un utilisateur de démonstration du starter.
  Le nouveau seeder sera indépendant : son lancement ne doit pas créer cet utilisateur.
- PHP, Composer et Node passent par Docker/Make. Les tests restent exclusivement
  sur `gamestackr_testing`.

## Approches

1. **Une extension mondiale et ses traductions.** Peu de fiches, mais impose une
   identité commune fragile lorsque les cartes, numéros ou regroupements diffèrent.
   Écartée pour ce périmètre international.
2. **Catalogues distincts, éditions linguistiques et rapprochements explicites.**
   Préserve les identités éditoriales et les différences régionales ; demande de
   gérer quelques fiches supplémentaires. Approche choisie par l’utilisateur.

Un catalogue est une organisation de nos données, pas un territoire, une langue,
un format de tournoi, ni une affirmation sur l’organisation juridique de l’éditeur.
Les catalogues sont configurables par jeu, pas codés en dur dans un enum mondial.

## Périmètre

Inclure les modèles, migrations, contraintes, actions métier, politiques d’accès,
ressources Filament, gestion des images, documentation et seeder d’exemples.
L’administration est en français et réutilise les conventions existantes.

Ne pas créer maintenant les cartes individuelles, variantes foil, tirages,
réimpressions physiques, collections personnelles, prix, stocks, produits scellés,
formats/légalité ou synchronisations avec une API tierce. Ne pas développer les
pages React publiques du catalogue dans cette première livraison.

Le modèle ne doit ni déduire une édition imprimée d’une traduction du site, ni
fusionner des extensions sur leur seul nom ou code, ni créer des séries fictives
pour les jeux qui n’utilisent pas ce regroupement.

## Vocabulaire et relations

| Entité | Sens et rattachement |
| --- | --- |
| `Game` | Jeu existant, conservé. Un jeu possède plusieurs catalogues. |
| `Catalog` | Catalogue éditorial appartenant à un jeu. |
| `Series` | Regroupement éditorial dans un catalogue ; présenté comme série ou bloc. |
| `Expansion` | Extension d’un catalogue, avec une série facultative de ce même catalogue. |
| `ExpansionEdition` | Édition réellement publiée d’une extension dans une langue/écriture. |
| `ExpansionRelease` | Sortie d’une édition sur un marché et pour un canal de distribution. |
| `SeriesLocalization` | Nom, description et visuel de présentation d’une série dans une langue. |
| `ExpansionLocalization` | Nom et description d’affichage d’une extension dans une langue. |
| `ExpansionRelation` | Rapprochement documenté entre deux extensions du même jeu. |

Référentiels partagés `Language` et `Market`, administrables dans Filament.
Une langue utilise un tag BCP 47 (`fr`, `en`, `ja`, `ko`, `zh-Hans`, `zh-Hant`).
Un marché possède une clé stable et un libellé : France, Japon, États-Unis,
Taïwan, Corée du Sud, Chine continentale, ou une zone documentée si nécessaire.
Ne pas stocker de liste de pays dans le champ langue. Un marché peut couvrir
plusieurs langues et une édition peut avoir plusieurs marchés.

### Identité et champs

- Les nouvelles entités principales ont un ID technique et une clé interne
  stable, unique dans leur parent et indépendante de leur nom officiel. Cette
  clé est proposée à la création, puis non modifiable dans l’admin en V1.
  Les codes officiels ne servent jamais de clés primaires ou d’identifiants globaux.
- `Catalog` : jeu, clé, nom d’administration, description facultative, langue
  de présentation par défaut, ordre, visibilité.
- `Series` : catalogue, clé, libellé d’administration, type `series` ou `block`,
  ordre, visibilité. Les noms et images localisés sont dans ses localisations.
- `Expansion` : catalogue, série nullable, clé, libellé d’administration, type
  `main`, `special` ou `promo`, ordre, visibilité. Ces types décrivent notre
  classement ; ils ne traduisent pas automatiquement les catégories d’un éditeur.
- `ExpansionEdition` : extension, langue, nom officiel dans son écriture d’origine,
  code officiel facultatif, logo/image facultatif, visibilité. Une seule édition
  par couple extension/langue dans cette V1 ; les différences de tirage sont hors
  périmètre. Des publications distinctes d’une même langue restent représentables
  par des extensions/catalogues distincts.
- `ExpansionRelease` : édition, marché, canal (`general`, `specialist`,
  `prerelease`), date nullable, URL source facultative. Unicité édition/marché/canal.
  La date est un jour civil local, pas un instant UTC. Une date inconnue reste
  inconnue ; ne pas la remplacer par la première date d’un autre marché.
- Localisations : unicité parent/langue, nom, description nullable ; image nullable
  pour les séries. La localisation d’une extension ne crée jamais d’édition.
- Ne pas ajouter de compteur de cartes dans cette V1 : les conventions de comptage
  (secrètes, parallèles, numéros de base) nécessiteront la conception des cartes.

Choix de l’agent après relecture : les rattachements qui définissent l’identité
sont fixes après création (jeu d’un catalogue, catalogue d’une série/extension,
extension et langue d’une édition, édition/marché/canal d’une sortie). Une correction
de ces rattachements passe par la création de la bonne fiche et une suppression
explicite respectant les dépendances. La série facultative d’une extension reste
modifiable à l’intérieur du même catalogue. Les dates, noms, textes, images,
ordres et visibilités restent éditables. Les codes de langue et clés de marché
référencés sont également fixes. Aucun déplacement automatique de sous-arbre.

### Affichage et visuels

Pour les noms de série/extension : langue demandée, puis langue par défaut du
catalogue, puis libellé d’administration. Les noms officiels d’éditions restent
affichés tels quels avec leur langue ; une traduction explicative est identifiée
comme telle. L’interface indique la langue réellement affichée en cas de repli.

Pour une série : image de la localisation choisie, sinon absence d’image explicite.
Pour une édition : son propre visuel, sinon un placeholder. Ne pas faire passer
le logo japonais pour le logo d’une édition coréenne faute d’image disponible.

Étendre ou extraire proprement le traitement d’images actuel sans affaiblir ses
validations et garanties : AVIF, transparence, nettoyage SVG, limites de taille,
protection des chemins, conservation de l’ancienne image si l’enregistrement échoue,
suppression après succès. Les nouveaux chemins ont un espace de noms distinct
des jeux. Un visuel peut être absent ; aucun faux logo officiel ne sera généré.

### Rapprochements entre catalogues

Relations plusieurs-à-plusieurs sans parent mondial obligatoire :

- `related_to` : rapprochement éditorial symétrique, sans promesse de contenu identique ;
- `adapted_from` : adaptation orientée, uniquement lorsque les sources la justifient ;
- `includes_content_from` : reprend du contenu, sans affirmer une inclusion exhaustive.

Conserver une note explicative et au moins une URL source. Une relation doit être
du même jeu, jamais vers soi-même. Refuser les doublons ; canonicaliser la paire
pour `related_to`. Aucune transitivité ni fusion automatiques. Les cycles de
`related_to` sont normaux ; les relations orientées ne sont pas une hiérarchie.
Les relations restent facultatives et ne bloquent pas l’import de fiches indépendantes.
Elles sont aussi autorisées entre extensions d’un même catalogue : la contrainte
porte sur le même jeu, et non sur l’obligation d’avoir des catalogues différents.

### Cohérence et suppression

Imposer en base autant que possible les unicités et l’appartenance commune du
catalogue d’une extension et de sa série ; compléter par une validation métier
pour obtenir un message Filament compréhensible. Les autres points d’entrée,
dont le seeder, utilisent les mêmes règles métier.

Bloquer la suppression d’un jeu contenant des catalogues, d’un catalogue contenant
des séries/extensions, d’une série utilisée ou d’une extension contenant des
éditions/relations. Bloquer celle d’une langue/marché référencé. Les localisations
et sorties dépendantes peuvent être supprimées avec leur parent lorsque les règles
précédentes l’autorisent ; nettoyer leurs images via le cycle de vie prévu.
L’admin peut détacher explicitement une série d’une extension avant suppression.

La visibilité d’un enfant ne rend pas visible un parent masqué. L’héritage effectif
sera testé ; aucune exposition publique nouvelle n’est incluse dans cette livraison.
La visibilité actuelle des jeux et leurs URL publiques restent compatibles.

## Administration Filament

Ressources principales : Catalogues, Séries, Extensions. Référentiels : Langues,
Marchés. Ajouter l’accès aux catalogues depuis un jeu existant.

- Listes recherchables, filtres par jeu/catalogue/série/type/visibilité, et édition
  des noms et caractères non latins. Afficher catalogue et langue pour lever les ambiguïtés.
- Éditer les localisations depuis la série ou l’extension, les éditions et
  rapprochements depuis l’extension, les sorties depuis l’édition.
- Ne pas multiplier les entrées de navigation pour chaque table technique.
- Sélecteurs dépendants et validation serveur pour empêcher les associations
  incompatibles. Messages de suppression indiquant les dépendances restantes.
- Accès réservé aux administrateurs selon les mêmes exigences que `GameResource`.

Disposition détaillée, choix des composants Filament et organisation des classes
laissés au constructeur, en suivant les écrans existants. Pas de refonte graphique.

## Seeder d’exemples réels

Créer `TcgCatalogExamplesSeeder`, invoqué explicitement par Docker/Make, par exemple
`make artisan ARGS='db:seed --class=TcgCatalogExamplesSeeder'`.
Il n’est appelé ni par `DatabaseSeeder`, ni par `make init`, ni par les migrations.
Il ne crée aucun utilisateur et ne lance jamais de suppression/réinitialisation.

### Contrat de relance et de conservation

- Données déterministes et versionnées ; pas de Faker, pas de requête réseau à
  l’exécution. Chaque fiche du manifeste possède une clé stable et ses sources.
- Une deuxième exécution ne crée pas de doublons, ne remplace pas les images et
  n’écrase pas les noms/descriptions/visibilités changés dans Filament, y compris
  les effacements volontaires. Créer uniquement les enregistrements manquants.
- Une fiche d’exemple supprimée entièrement est recréée lors d’une invocation
  explicite ultérieure du seeder, avec ses données initiales et ses dépendances
  nécessaires. C’est un choix de l’agent, distinct de la conservation des champs
  vidés sur une fiche existante. Le rapport de fin distingue ces recréations ;
  pour conserver durablement une fiche masquée, utiliser sa visibilité.
- Réutiliser les jeux existants lorsqu’ils sont identifiés sans ambiguïté
  (slugs conventionnels initiaux `pokemon`, `magic`, `yugioh`, `one-piece`, `lorcana`).
  En cas de collision ambiguë, échouer avec un diagnostic plutôt que fusionner.
  Le suivi des associations entre clés du manifeste et IDs doit résister au
  renommage ultérieur du slug du jeu ; le mécanisme de suivi persistant est laissé
  au constructeur. Aucun ID de base locale n’est inscrit dans le manifeste Git.
- Appliquer une transaction sur les données ; traiter les fichiers avec le même
  mécanisme de rollback/nettoyage que les formulaires. Rapporter les créations,
  les éléments conservés et les conflits.
- Les nouveaux exemples sont visibles dans Filament mais masqués de publication
  par défaut. Ne pas masquer un jeu préexistant. Les données réelles ne sont pas
  des fixtures de test : les tests utilisent leur propre base isolée.

### Jeu de données proposé

19 extensions réparties sur cinq jeux et onze catalogues. Il ne s’agit pas
d’un catalogue exhaustif. Les marchés ci-dessous décrivent seulement les sorties
documentées pour l’échantillon, pas toutes les disponibilités réelles.
Les textes descriptifs seront de courtes synthèses originales, pas des copies
de textes promotionnels. Noms officiels et codes natifs sont conservés.

| Jeu / catalogue | Extensions et éditions à créer | Cas démontré / dates vérifiées |
| --- | --- | --- |
| Pokémon / international | Écarlate et Violet / Scarlet & Violet ; Écarlate et Violet – 151 / Scarlet & Violet—151 ; éditions `fr` et `en` | Une extension avec plusieurs langues, série Écarlate et Violet. France/États-Unis : 2023-03-31 pour la base et 2023-09-22 pour 151. Sources P1–P4. |
| Pokémon / japonais | スカーレットex ; バイオレットex ; ポケモンカード151 ; éditions `ja` | Deux extensions distinctes au lancement, série スカーレット＆バイオレット. Japon : 2023-01-20 pour les deux premières ; 2023-06-16 pour 151. P5–P6. |
| Pokémon / coréen | 포켓몬 카드 151 ; édition `ko` | Catalogue propre, série 스칼렛&바이올렛 ; Corée du Sud : 2023-07-28. P7. |
| Pokémon / chinois traditionnel | 寶可夢卡牌151 ; 眾星雲集組合篇 SET B ; éditions `zh-Hant` | Séries 朱＆紫 et 太陽 & 月亮 ; Taïwan : 2023-06-30 pour 151 ; date SET B non renseignée faute de date vérifiée dans la source retenue. P8–P9. |
| Pokémon / chinois simplifié | 收集啦151 惊 ; 收集啦151 聚 ; éditions `zh-Hans` | Deux extensions distinctes dans 朱&紫 ; Chine continentale : 2025-07-18 et 2025-10-17. Ne pas les traiter comme les traductions de SET B ou comme une édition unique de 151 japonais. P10. |
| Magic / international | Khans of Tarkir ; Fate Reforged ; Dragons of Tarkir ; Foundations ; éditions `en` | Trois extensions dans le bloc Khans of Tarkir, Foundations sans bloc. Les dates des trois premières restent non renseignées dans cet échantillon ; États-Unis : 2024-11-15 pour Foundations. M1–M2. |
| One Piece / japonais | ROMANCE DAWN, code officiel OP-01, édition `ja` | Sans série ; Japon : 2022-07-22. O1. |
| One Piece / international | ROMANCE DAWN, code présenté OP01, édition `en` | Sans série ; États-Unis : 2022-12-02. Même nom, autre catalogue et autre date. O2. |
| Yu-Gi-Oh! / OCG japonais | DARKWING BLAST, édition `ja` | Sans série artificielle ; Japon : 2022-07-16. Y1. |
| Yu-Gi-Oh! / TCG | Explosion de l’Aile des Ténèbres / Darkwing Blast, éditions `fr` et `en` | Sans série ; France : 2022-10-20 ; États-Unis : sortie générale 2022-10-21 et canal boutiques spécialisées/OTS 2022-10-19. Y2–Y3. |
| Disney Lorcana / international | Premier Chapitre / The First Chapter, éditions `fr` et `en` | Un chapitre correspond ici à l’extension, pas à un bloc inventé ; sorties générales France/États-Unis : 2023-09-01. L1–L2. |

Le classement de catalogues, les clés et l’absence de regroupement pour certains
exemples sont nos choix de modélisation ; les noms et dates ci-dessus sont sourcés.
Les pages coréennes P7 et certaines archives L1/L2 ont été retrouvées dans l’index
de recherche officiel mais leur ouverture directe est instable ; conserver cette
limite dans la provenance plutôt que prétendre à une liste de cartes vérifiée.

### Relations, traductions et images de l’échantillon

- Ajouter des relations `related_to` entre les sorties 151 des catalogues, entre
  ROMANCE DAWN japonais/international et entre DARKWING BLAST OCG/TCG. Les notes
  indiquent qu’il s’agit de rapprochements de catalogage, pas d’une identité
  démontrée des listes de cartes. Ne pas amorcer de relations plus fortes sans preuve.
- Une extension étrangère possède une description explicative française identifiée
  comme rédaction du site, sans édition `fr`. Cela démontre la séparation entre
  traduction d’affichage et édition imprimée.
- Prévoir un exemple de série avec noms et visuels français/anglais distincts,
  et au moins une extension avec logos officiels français/anglais distincts.
  Les images sélectionnées seront vérifiées sur les sources officielles durant
  l’implémentation, préparées en AVIF, versionnées comme petites ressources du
  seeder avec leurs URL d’origine, puis copiées sur le disque configuré par les
  actions métier. Pas de hotlink ou de téléchargement au lancement du seeder.
- Les autres images peuvent être absentes pour démontrer le cas sans visuel.
  Ne pas substituer une image inventée lorsqu’un fichier officiel est inaccessible.
  Si les visuels localisés requis ne sont pas récupérables, signaler explicitement
  cette limite et choisir un autre exemple réel documenté.
- Définir un manifeste lisible avec, pour chaque champ factuel, sa source, la date
  de consultation (2026-11-15 pour cette recherche), et une note pour toute
  interprétation. Une donnée non vérifiée reste nulle ou absente.

## Validation attendue lors de l’implémentation

Tests utiles : contraintes de catalogue/série et de langue, éditions distinctes,
sorties par marché/canal, validation des relations, droits Filament, suppressions
bloquées, traductions sans édition imprimée, repli sans confusion de langue,
images et nettoyage transactionnel, héritage de visibilité.

Pour le seeder : première exécution, relance sans doublon, préservation de jeux et
champs existants, renommage de jeu, refus des collisions, données réelles attendues,
absence d’appel réseau et absence de création d’utilisateur. Tester les fichiers
avec les disques simulés puis Garage réel dans la suite dédiée.

Vérifier le parcours administrateur dans le navigateur : création d’un catalogue,
série localisée, extension sans série, édition, sorties multiples, rapprochement,
édition d’image, refus d’association incompatible et relance du seeder.

Avant PR : diff complet, `make check`, `make assets`, `make up` puis
`make test-storage`, `git diff --check`, revue indépendante et workflow GitHub
selon AGENTS.md. La présente étape ne modifie que ce document ; ces contrôles
applicatifs ne constituent pas une validation déjà exécutée.

## Sources de l’échantillon

- P1 — [Pokémon, Écarlate et Violet FR](https://www.pokemon.com/fr/actus-pokemon/mettez-la-main-sur-le-boitier-strategies-et-combats-ecarlate-et-violet-du-jcc-pokemon)
- P2 — [Pokémon, Scarlet & Violet EN](https://press.pokemon.com/en/releases/First-Expansion-of-the-Pokemon-Trading-Game-Scarlet-Violet-Series-Comi)
- P3 — [Pokémon, 151 FR](https://www.pokemon.com/fr/actualites/retrouvez-ectoplasma-tetarte-et-dautres-pokemon-dans-lextension-ecarlate-et-violet-151)
- P4 — [Pokémon, calendrier des sorties EN](https://press.pokemon.com/en/Items/Schedule/Pokemon-Trading-Card-Game?types=3)
- P5 — [Pokémon japonais, début de Scarlet & Violet](https://www.pokemon-card.com/ex/sv/index.html)
- P6 — [Pokémon japonais, 151](https://www.pokemon-card.com/ex/sv2a/)
- P7 — [Pokémon coréen, 151](https://pokemoncard.co.kr/card/551)
- P8 — [Pokémon traditionnel, 151](https://asia.pokemon-card.com/tw/archive/special/card/sv2a/index.html)
- P9 — [Pokémon traditionnel, SET B](https://asia.pokemon-card.com/tw/archive/card/sun_moon_series/booster_pack_b.html)
- P10 — [Pokémon simplifié, 收集啦151 惊 et 聚](https://www.pokemon.cn/tcg/product/15486.html)
- M1 — [Wizards, bloc Khans of Tarkir](https://magic.wizards.com/en/news/making-magic/whims-fate-reforged-part-2-2015-01-05)
- M2 — [Wizards, Foundations](https://magic.wizards.com/en/news/announcements/announcing-magic-the-gathering-foundations)
- O1 — [Bandai, ROMANCE DAWN japonais](https://www.onepiece-cardgame.com/products/boosters/op01.php)
- O2 — [Bandai, ROMANCE DAWN international](https://en.onepiece-cardgame.com/products/boosters/op01.php)
- Y1 — [Konami, DARKWING BLAST OCG](https://www.yugioh-card.com/japan/products/dabl/)
- Y2 — [Konami, Explosion de l’Aile des Ténèbres FR](https://www.yugioh-card.com/eu/fr/product/explosion-de-laile-des-tenebres/)
- Y3 — [Konami, Darkwing Blast États-Unis](https://www.yugioh-card.com/en/products/dabl/)
- L1 — [Ravensburger, archives FR](https://disneylorcana.com/fr/product-archive/)
- L2 — [Ravensburger, archives EN](https://disneylorcana.com/en/product-archive/)
- [W3C, tags de langue et écritures](https://www.w3.org/International/articles/language-tags/)

## Questions de validation

Une relecture indépendante de constructibilité a été effectuée par un autre agent.
Ses trois questions ont été résolues dans cette proposition : rattachements fixes
en V1, recréation des exemples entièrement supprimés à la relance explicite, et
rapprochements autorisés également au sein d’un même catalogue. Ce n’est pas une
revue de code ni une validation utilisateur.

L’utilisateur doit valider ce document avant l’écriture du code. Les principaux
choix proposés sont les cinq jeux de l’échantillon, le seeder explicite et non
destructif, les images officielles limitées à quelques exemples, les sorties par
canal et l’absence de nouvelles pages publiques dans cette livraison.

Après validation, préparer le plan d’implémentation avec le skill
`superpowers:writing-plans`. Ce document ne constitue ni un plan exécuté ni une
autorisation de considérer les exigences testées.
