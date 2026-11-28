# Références Pokémon

Les données proviennent des deux documents fournis par le propriétaire le
28 novembre 2026. `sources.php` conserve leurs titres, empreintes SHA-256 et
onglets/sections. Chaque ligne normalisée conserve `source` et `row` ; le
manifeste résultant expose `sources` et `source_row`. Les documents originaux
et les données personnelles de suivi de collection ne sont pas versionnés.

Les tableaux PHP sont un instantané documentaire, pas une recherche réseau
effectuée à chaque seed. Leur reprise ne constitue pas une vérification
indépendante exhaustive de chaque affirmation des documents.

| Catalogue            | Séries | Fiches |
| -------------------- | -----: | -----: |
| International        |     12 |    184 |
| Japonais             |     18 |    210 |
| Coréen               |     11 |    158 |
| Chinois traditionnel |      7 |     77 |
| Chinois simplifié    |      6 |     63 |
| Thaïlandais          |      5 |     67 |
| Indonésien           |      5 |     67 |

## Composition et identifiants

`PokemonCatalogData` transforme ces références en manifeste compatible avec
`TcgCatalogSeeder`. `curated.php` conserve les 15 fiches antérieures et leurs
sources web. La fusion utilise les clés des catalogues/séries/extensions,
la langue des éditions et le couple marché/canal des sorties. Les références
antérieures ont priorité sur les titres et dates documentaires. Le classement
historique des nouvelles références est conservé.

Les intitulés anglais des nouvelles références asiatiques sont des noms
d’affichage, souvent des traductions éditoriales. Ils ne créent pas une édition
anglaise et ne remplissent pas son nom commercial local. Une valeur `null` dans
la référence devient `—` dans le champ `name` obligatoire de l’édition : cela
signale un nom inconnu sans lui attribuer une langue erronée. Les intitulés
natifs fournis explicitement sont utilisés, notamment Hantaman Pertama et
Kehadiran Juara. Les anciennes fiches conservent leurs noms natifs vérifiés.

Les compléments explicites de l’encyclopédie sont repris : EX Legend Maker à
Taïwan, familles promotionnelles régionales et dates propres à chaque marché
(Start Deck 100, compilations taïwanaises, First Impact indonésien, série MA,
30th Celebration). Leurs sorties citent le document Word ; `source_row` désigne
un numéro de paragraphe extrait lorsqu’une fiche entière vient de ce document.

## Précision et limites

- Les formats Excel `yyyy-mm` et `yyyy-m` indiquent un mois, même si la valeur
  interne tombe le premier. Les sorties correspondantes ont `released_on = null`
  et une période `YYYY-MM`. Les jours explicites sont conservés, en tenant compte
  du Word : CSVH5 reste en juillet 2026 malgré le 1er juillet stocké dans Excel.
- La colonne « Sortie (EN) » ne précise pas de marché. Sa période devient une
  description de l’extension. Les éditions françaises ne reçoivent pas cette date.
- Les colonnes de codes coréens et taïwanais ne donnent pas leurs dates : aucune
  sortie n’est déduite de la date japonaise. Les lignes exclusivement KO/TW ne
  deviennent pas des fiches japonaises. Les compilations taïwanaises A/B sont
  séparées et les mentions « Intégré » ne créent pas d’éditions supplémentaires.
- Les codes de classement internationaux (`BS`, `SV1`, `ME1`, etc.) ne sont pas
  chargés dans `official_code`. Les codes historiques japonais ambigus ou
  reconstruits (`ADV1`, `PCG1`, codes avec parenthèses) sont aussi omis.
- Les saisons des collections chinoises 151 contredisent les dates précises
  déjà sourcées : les fiches `151-jing` et `151-ju` conservent les sources antérieures.
- Les éditions KO/TW de M7 sont omises en l’absence de confirmation locale de
  leur sortie au 28 novembre. Aura Storm (TH/ID) reste `announced` avec la période
  décembre 2026. La date thaïlandaise de First Impact diverge entre les pièces
  (janvier/février 2019) et n’est pas fixée arbitrairement.
- Les éditions internationales ajoutées sont EN/FR, avec respect des huit
  absences françaises du tableau principal. Les éditions DE/ES/IT déjà
  vérifiées sont conservées. Les langues supplémentaires du référentiel ne
  suffisent pas à inventer des titres imprimés pour toutes les extensions.
- Les collections McDonald’s 2018/2019 France et internationales restent
  distinctes. Le marché Québec ne devient pas France. L’édition 2024 peut
  avoir une sortie anglaise en 2025. Les cellules indiquant plusieurs marchés
  donnent des sorties distinctes, avec conservation des mentions au mois seul.
- Les séries Black Star, les 9 POP Series, les 9 Prize Packs, les trois Trick
  or Trade 2022–2024 et les promotions japonaises sont des fiches séparées.
  Les intitulés de regroupement promotionnels sont éditoriaux. Le document ne
  détaille pas les impressions françaises de chaque série promo ; aucune
  édition française n’est extrapolée, hors McDonald’s explicitement ventilé.
- Les groupes « Trainer Kits » et « World Championships Decks », non ventilés
  par produit dans les documents, ne deviennent pas des extensions artificielles.
  Les lignes de totaux, les décomptes de cartes, la collection personnelle et
  les variantes (Reverse Holo, Shadowless, tampons, etc.) ne sont pas importés.

Une source locale possède `url = null` et des métadonnées documentaires. Les
sorties associées n’affichent donc pas de faux lien public. Le moteur continue
de transmettre les URL réelles des sources web existantes.
