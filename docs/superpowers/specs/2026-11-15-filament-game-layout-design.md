# Mise en page des jeux dans Filament

## Intention et choix retenu

Rendre l’administration des jeux plus lisible et esthétique en restant sur les
composants natifs de Filament 5. L’utilisateur a choisi des sections avec une
colonne latérale pour l’image et la visibilité, plutôt qu’une colonne unique ou
des onglets. Continuer sur `codex/game-details`.

## Présentation

- Formulaires : grille adaptative, deux tiers pour Présentation et Historique,
  un tiers pour Visuel et Publication. Empiler les groupes sur petit écran.
- Titres, descriptions courtes et icônes natives ; description large, années
  côte à côte quand l’espace le permet, aperçu d’image dans la colonne latérale.
- Liste : nom mis en valeur, slug secondaire, éditeur sur grand écran, badges
  « Visible » et « Masqué » avec icône et couleur. Colonnes secondaires disponibles
  dans le sélecteur de colonnes. Sur mobile, masquer les visuels et l’éditeur,
  conserver les noms sur une ligne et le défilement horizontal natif du tableau.
- Conserver les contrôles, autorisations, champs facultatifs et sauvegardes actuels,
  ainsi que les confirmations de suppression, la recherche et les tris existants.

## Contraintes et validation

Utiliser Section, Group, les grilles adaptatives et les API de colonnes Filament.
Aucun thème personnalisé, CSS de contournement ou nouvelle dépendance.
Ne pas modifier les données des jeux pendant la vérification visuelle.

Réutiliser les tests Livewire existants et vérifier la recherche par slug.
Inspecter création, modification et liste dans le navigateur, y compris en largeur
mobile, puis exécuter Pint, PHPStan et la suite complète.

Références officielles :
- https://filamentphp.com/docs/5.x/schemas/layouts
- https://filamentphp.com/docs/5.x/schemas/sections
- https://filamentphp.com/docs/5.x/tables/columns/text
- https://filamentphp.com/docs/5.x/forms/file-upload
