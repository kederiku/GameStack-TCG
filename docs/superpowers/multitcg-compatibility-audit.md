# Compatibilité avec les jeux enregistrés

Audit du 15 novembre 2026. Consultation en lecture seule des fiches `Game` via
Laravel dans Docker : 13 jeux, dont Naruto Card Game masqué. Aucune fiche, image,
visibilité ou URL existante n’a été modifiée. Ce rapport décrit une compatibilité
de conception, pas une implémentation testée ni une couverture exhaustive de tous
les produits et tirages historiques.

## Conclusion

La séparation jeu / catalogue / série facultative / ensemble éditorial / version
linguistique convient aux treize jeux, avec les précisions ci-dessous. Pour le
nouveau Naruto de Bandai, la compatibilité reste provisoire : les annonces ne
fournissent pas encore toutes les langues et listes définitives.

Les différences les plus importantes concernent le futur domaine des cartes :
identités mécaniques distinctes, tirages, faces, objets sérialisés et Uniques.
Elles ne nécessitent pas d’implémenter maintenant les cartes et produits, mais
doivent figurer dans leur contrat de compatibilité.

## Vérification par jeu

| Jeu présent          | Verdict et cas concret                                                                                                                                                                                                                                                                                                                                                                                                 | Conséquence                                                                                                                                                                                         |
| -------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Pokemon              | Adapté. Les catalogues régionaux restent distincts ; la Galerie de Galar est un sous-ensemble de Crown Zenith. [Pokémon](https://press.pokemon.com/en/releases/MEDIA-ALERT-Pokemon-Trading-Card-Game-Crown-Zenith-Expansion-Released-)                                                                                                                                                                                 | Les futurs sous-ensembles de cartes ne sont pas des blocs ni des produits. Les langues ne se déduisent pas des équivalences entre extensions.                                                       |
| One Piece            | Adapté avec une définition plus large de l’ensemble éditorial. Les starters ont leurs listes propres ; ROMANCE DAWN contient plusieurs visuels sous le même code. [ST-11](https://en.onepiece-cardgame.com/products/decks/st11.php), [OP01](https://en.onepiece-cardgame.com/cardlist/?series=569101)                                                                                                                  | Accepter ensembles de deck et réimpressions, distincts de leur emballage. Ne pas rendre un code imprimé unique par défaut.                                                                          |
| Cyberpunk            | Adapté avec tirages distincts. WeirdCo documente Beta et Retail de Welcome to Night City, des starters et promos. [Guide WeirdCo](https://cyberpunktcg.com/blog/sealed-primer), [Floor It](https://cyberpunktcg.com/cards/floor-it)                                                                                                                                                                                    | Une langue peut couvrir plusieurs tirages avec numéros, contenus et images différents.                                                                                                              |
| Magic: The Gathering | Adapté. Foundations mélange des ensembles dans ses boosters ; Innistrad Remastered comporte des cartes recto-verso et des exemplaires sérialisés. [Foundations](https://magic.wizards.com/en/news/feature/collecting-foundations), [Innistrad Remastered](https://magic.wizards.com/en/news/feature/collecting-innistrad-remastered)                                                                                   | Produit plusieurs ensembles ; images par face ; langue réelle du contenu distincte de celle du produit ; numéro d’exemplaire distinct du numéro de collection.                                      |
| Flesh and Blood      | Adapté, mais le pitch change l’identité de jeu. Alpha et Unlimited peuvent partager langue et numéro ; History Pack 1 contient plusieurs cartes distinctes sous 1HP000. [Règles §1.3.4](https://rules.fabtcg.com/en/cr/01-game-concepts/#134), [WTR](https://legacy.fabtcg.com/en/collectors-centre/welcome-rathe/), [History Pack 1](https://legacy.fabtcg.com/en/collectors-centre/history-pack-1/)                  | Ne pas classer rouge/jaune/bleu comme de simples finitions. Pas d’unicité imposée du numéro même dans une version linguistique. Prévoir les tirages et versos différents.                           |
| Riftbound            | Adapté. Origins coréen comprend des variantes et promos propres ; Premiers pas possède son ensemble OGS. [Corée](https://playriftbound.com/en-us/news/announcements/koreas-rift-opens-on-september-18/), [OGS](https://playriftbound.com/fr-fr/news/announcements/what-to-expect-during-preview-season/)                                                                                                               | Ensemble éditorial spécial distinct du coffret ; pas de copie automatique des variantes entre langues.                                                                                              |
| Yu-Gi-Oh!            | Adapté avec ensembles de decks et réimpressions. OCG/TCG sont distincts ; Fire Kings est un Structure Deck avec sa propre liste de cartes. [OCG](https://www.yugioh-card.com/japan/products/dabl/), [Fire Kings](https://www.yugioh-card.com/en/products/sdfk/)                                                                                                                                                        | L’entité de catalogage ne doit pas être limitée aux boosters. Le deck produit et la liste éditoriale de ses cartes restent deux notions.                                                            |
| Lorcana              | Adapté. Les réimpressions Fabled et le coffret Elsa, avec plusieurs extensions et une promo propre, confirment les séparations proposées. [Fabled](https://disneylorcana.com/en/news/2025/08/2025-8-familiar-faces/), [coffret Elsa](https://disneylorcana.com/en/product/elsa-gift-box/)                                                                                                                              | Chapitre = extension dans les exemples, pas bloc forcé ; produits et disponibilités linguistiques indépendants.                                                                                     |
| Naturo Mythos        | La fiche semble désigner NARUTO Mythos de Cicaboom ; nom laissé intact. Konoha Shidō possède deux éditions de tirage avec tampon, emballages et certaines cartes différents. [Cicaboom](https://www.narutotcgmythos.com/updates-about-1st-set-2nd-edition), [guide](https://www.narutotcgmythos.com/collection-guide)                                                                                                  | Ne pas confondre tirage 1/2 avec langue 1/2, ni transformer chaque tirage en un nouveau jeu. Ne pas fusionner ce jeu avec celui de Bandai.                                                          |
| Naruto Card Game     | Compatibilité provisoire avec le nouveau jeu Bandai annoncé pour juin 2027. Des starters et un booster sont annoncés, des promos de démonstration existent avant le lancement. [Bandai](https://www.naruto-cardgame.com/en/news/nycc-report.php), [Paris Games Week](https://www.naruto-cardgame.com/en/news/parisgamesweek-2026.php)                                                                                  | Conserver la visibilité actuelle. Distinguer annonce et publication, période annoncée et jour exact. Ne pas inventer les langues d’impression à partir des langues du site.                         |
| Final Fantasy TCG    | Adapté. Chapter japonais et Opus justifient des catalogues distincts ; les cartes Legacy peuvent conserver un ancien code dans un nouvel ensemble. [Chapter](https://fftcg.square-enix-games.com/en/news/from-the-producer-special-vol-7-the-chapter-series), [Dreamlike Oceans](https://fftcg.square-enix-games.com/na/release/dreamlike-oceans)                                                                      | Ne déduire l’extension ni du numéro ni de l’illustration. Rattacher explicitement les impressions et documenter les réimpressions.                                                                  |
| Star Wars: Unlimited | Adapté. Leaders à deux faces ; cartes exclusives au starter au sein de la même extension ; cadre Hyperspace combinable avec foil. [FFG, lancement](https://starwarsunlimited.com/fr/articles/launch-sequence-fr), [variantes](https://starwarsunlimited.com/articles/boosting-ahead-of-release)                                                                                                                        | Images par face et par variante. L’absence d’une carte dans les boosters ne crée pas automatiquement une autre extension.                                                                           |
| Altered              | Adapté pour les publications physiques attestées, avec un futur modèle de cartes à préciser. Les Uniques ont des statistiques/capacités générées. Le Legacy POD est indépendant de l’ancien système de propriété. [Uniques](https://www.altered.gg/en-us/news/unique-cards-explained/), [Legacy](https://www.altered.gg/en-us/news/altered-legacy/), [POD](https://www.altered.gg/en-us/news/legacy-POD-is-available/) | Distinguer famille, identité mécanique générée, impression, finition et exemplaire. Une sortie numérique ne prouve pas une sortie papier. Aucun lien de dépendance à l’ancien service de propriété. |

## Ajustements du document de conception

### Socle V1

1. Étendre le sens d’`Expansion` à un **ensemble éditorial de cartes** : extension
   principale, spéciale, ensemble promotionnel, ensemble propre à un deck,
   collection de réimpressions. Dans Filament, le libellé proposé est « Extensions
   et ensembles ». Aucun produit ne crée automatiquement un ensemble : SWU montre
   des cartes starter exclusives appartenant à l’extension existante, tandis
   qu’OGS ou ST-11 fournissent des références éditoriales distinctes.
2. Les canaux de lancement doivent représenter une première distribution par
   financement participatif ou événement, au-delà des trois valeurs prévues.
   Le procédé d’impression POD reste une information de production future et ne
   doit pas être confondu avec le support numérique ni avec une langue.
3. Pouvoir enregistrer une édition annoncée sans prétendre qu’elle est déjà publiée.
   Une période « juin 2027 » ne devient pas artificiellement le 1er juin. Aucun
   jour précis n’est enregistré sans preuve. Un ensemble annoncé peut ne pas
   encore avoir d’édition linguistique si les langues ne sont pas confirmées.
   Dans ce cas, l’annonce générale et sa période restent une note sourcée dans
   la documentation/le manifeste ; la V1 ne propose pas de calendrier structuré
   d’annonces indépendantes des langues.
4. Les imports/seeds utilisent les jeux existants : le slug actuel de Yu-Gi-Oh!
   est `yu-gi-oh`, pas `yugioh`. `naturo-mythos` est conservé malgré sa coquille
   probable. Le lancement du seeder ne corrige ni les URL, ni les noms, ni les
   visibilités des jeux de l’utilisateur.

### Domaines futurs

- Introduire la notion de **tirage/édition physique** au-dessous de la version
  linguistique : Alpha/Unlimited, Beta/Retail, First/Second Edition, etc. Le
  tirage peut changer texte imprimé, assortiment, visuel ou code. Ce n’est pas
  nécessairement un simple booléen de finition sur une carte.
- Séparer **identité de jeu** et **apparence**. Une différence de pitch FaB ou
  de caractéristiques d’un Unique Altered ne se réduit pas à un traitement foil.
  Un regroupement par famille reste possible sans fusion des règles de jeu.
- Accepter plusieurs **faces**, avec leurs caractéristiques et images propres
  par impression/variante/langue. Une image principale n’est pas un plafond d’une
  image pour la carte entière.
- Conserver les codes de collection comme des données textuelles, pas des clés
  globales, ni automatiquement uniques dans un ensemble/langue. Le numéro de
  série d’un exemplaire et son scan spécifique appartiennent à l’exemplaire.
- Prévoir des sous-ensembles éditoriaux quand ils sont documentés (Galerie de Galar,
  par exemple), sans transformer un sous-ensemble en bloc. Leur modélisation fine
  appartient à la conception des cartes. Le cas SPG est différent : il s’agit d’un
  ensemble distinct distribué dans des boosters d’autres extensions.
- Une carte peut être redistribuée dans un autre produit sans changer son ensemble
  d’origine ; une véritable nouvelle impression conserve une référence explicite
  à l’ensemble qui la publie, même si elle reprend un ancien code.

## Conséquence pour les exemples

Les 19 extensions prévues pour cinq jeux restent un noyau utile, mais ne suffisent
pas à démontrer la couverture des treize jeux. Étendre le manifeste d’exemples lors
de la préparation de l’implémentation, en vérifiant chaque champ réellement chargé :

- Cyberpunk : Welcome to Night City, documentation Beta/Retail pour la suite ;
- Flesh and Blood : Welcome to Rathe et History Pack 1 ;
- Riftbound : Origins et ensemble spécial OGS ;
- Naruto Mythos : Konoha Shidō, documentation des deux tirages pour la suite ;
- Naruto Bandai : jeu existant conservé masqué ; annonce générale documentée dans
  le manifeste tant que les langues sont inconnues ; aucune édition publiée fictive ;
- FFTCG : au moins un exemple Chapter et un Opus, identités distinctes ;
- SWU : Étincelle de Rébellion, avec disponibilité exclusive au starter documentée ;
- Altered : un ensemble physiquement publié et une note sur les Uniques/POD.

Le seeder V1 ne chargera pas encore les produits, cartes, tirages ou exemplaires.
Les preuves de compatibilité ne se transforment pas en valeurs non vérifiées dans
les fixtures. Un jeu masqué n’est pas rendu visible par sa présence dans l’échantillon.

## Méthode et limites

Trois agents ont examiné indépendamment des groupes de jeux à partir du document
de conception et de sources primaires. La synthèse distingue faits publiés et
choix de modélisation. Les archives LSS de WTR/History Pack ont été retrouvées dans
l’index officiel détaillé, avec ouverture directe parfois indisponible ; certains
liens Bandai sont également instables dans l’outil de consultation.

Le domaine `narutomythos.com` indique être un compagnon non officiel. Les faits de
ce rapport renvoient donc au site source de Cicaboom, `narutotcgmythos.com`, et non
à cette copie. Le caractère officiel de `cyberpunktcg.com` est confirmé par le
[lien explicite de CD PROJEKT RED](https://press.cdprojektred.com/en/news/1816/cyberpunk-trading-card-game-launches-kickstarter-campaign).

Pas de tests applicatifs exécutés pour cet audit documentaire. Les seules opérations
sur l’application sont des lectures ciblées des fiches de jeux ; aucun dump de base
ni information de compte n’est enregistré dans le dépôt.
