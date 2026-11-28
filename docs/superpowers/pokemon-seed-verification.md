# Vérification du seed Pokémon — 28 novembre 2026

## Résultat local

Le manifeste contient 826 fiches Pokémon dans sept catalogues, dont 139 éditions
françaises. Les autres jeux gardent leur couverture antérieure. Les deux pièces
fournies sont référencées par leur titre, leur SHA-256 et leur emplacement ; elles
ne sont pas nécessaires à l’exécution et ne sont pas publiées.

- Nouveaux tests de couverture exécutés d’abord en échec sur les références absentes.
- Tests de régression exécutés en échec sur les marchés McDonald’s, l’utilisation
  de traductions anglaises comme noms officiels locaux et l’écrasement d’un nom
  local renseigné après import, puis corrigés.
- `make check` réussi : 407 tests PHP, 19 tests frontend,
  localisation, Pint, PHPStan, formatage, lint et TypeScript.
- Tests du seeder : réexécution, stabilité des identifiants, conservation des
  médias existants, validation du manifeste et rollback transactionnel inclus.
- `make seed` réussi sur la base locale après les corrections. Les tests restent
  exclusivement exécutés sur `gamestackr_testing`.
- `git diff --check` réussi.

`make assets` et `make test-storage` ne sont pas requis localement : aucun fichier
frontend, dépendance, build, stockage, image, Docker ou CI n’est modifié.
Le workflow distant `tests / ci` reste requis avant fusion et exécute également
l’installation propre, le build et les tests Garage.

## Parcours public vérifié

Vérification dans le navigateur après chargement du seed :

- Recherche de Base Set 2 : résultat présent, édition anglaise seule.
- McDonald’s 2018 international : sortie québécoise distincte de la France.
- McDonald’s 2024 anglais : États-Unis le 21 janvier 2025, Royaume-Uni en mai 2025.
- Pokémon Jungle japonais : nom officiel local inconnu (`—`), période mars 1997
  sans jour inventé.
- Aura Storm thaïlandais : statut annoncé, code MA7T et période décembre 2026.

## Revue et limites

Les revues indépendantes ont fait corriger l’attribution des dates McDonald’s,
les noms locaux inconnus, leur préservation au reseed et les compléments régionaux
explicites du document Word.
La relecture du commit final et les résultats CI sont consignés dans la PR.

Les règles détaillées figurent dans
[le registre Pokémon](../../database/seeders/data/tcg/pokemon/README.md).
Les contradictions documentaires ne sont pas résolues par extrapolation. Les noms
locaux manquants restent inconnus ; les cartes individuelles, variantes et données
personnelles de collection sont hors périmètre. Cet import n’est pas une validation
externe exhaustive des affirmations des documents.
