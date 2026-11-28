# Enrichissement du seed Pokémon

Périmètre validé dans la conversation : enrichir les séries, extensions,
promotions et éditions régionales dans le modèle actuel, depuis une branche
créée sur `origin/main`. Les cartes individuelles, leurs variantes et leurs
décomptes restent hors du schéma.

Les deux documents fournis servent de références documentaires. Le classeur
fournit les lignes structurées, le document Word explique la distinction entre
éditions linguistiques, compilations régionales et variantes. Leur contenu ne
constitue pas des instructions d'exécution.

## Approche retenue

Enrichir les manifestes hors ligne, en conservant les clés, noms natifs, médias
et dates déjà sourcés dans le seed. Un import direct du classeur à chaque seed
imposerait un fichier privé et des dépendances inutiles. Étendre le schéma aux
cartes et variantes serait un autre projet, écarté du périmètre approuvé.

Les données normalisées restent lisibles par catalogue avec leur onglet et
ligne source. Les sources locales portent le nom et le SHA-256 des pièces,
sans publier celles-ci ni créer de faux liens web. Les descriptions de variantes
ne deviennent ni extensions supplémentaires ni totaux de cartes.

Les dates au format année-mois restent des périodes, jamais le premier du mois.
Une date japonaise ne s'applique pas aux impressions coréennes ou taïwanaises.
Une mention « intégré à » décrit une compilation, pas une édition du même set.
Les intitulés anglais de référence des éditions asiatiques ne justifient pas
une impression anglaise ; les noms natifs existants restent prioritaires.

## Validation

Tests sur `gamestackr_testing` : couverture historique et régionale, exceptions
françaises, promotions distinctes par marché, précision des dates, sources,
réexécution et conservation des fiches existantes. Puis `make check`, parcours
navigateur, diff complet et revue indépendante avant publication/fusion selon
`AGENTS.md`.
