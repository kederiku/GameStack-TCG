# Visibilité des jeux sur le site

## Demande et choix de réalisation

Compléter la branche `codex/game-details` avec un champ permettant d’afficher
ou de masquer chaque jeu sur le site, administrable dans Filament.

Choix de l’agent : booléen `is_visible`, non nullable et vrai par défaut pour
conserver le comportement actuel des jeux existants et des nouvelles créations.
Un interrupteur « Afficher sur le site » permet de modifier cette valeur avant
enregistrement ; une colonne indique l’état dans la liste Filament.

## Approche et comportement

Étendre la table et `SaveGame`, puis filtrer le catalogue central `PublicGames`
et refuser l’accès direct à un jeu masqué dans `PublicGameController` (404).
Un filtre global Eloquent masquerait aussi les jeux à l’administration ; un
filtre React transmettrait encore les jeux cachés et laisserait les URL ouvertes.
Le filtrage reste donc côté serveur, uniquement aux points d’exposition publique.

Les listes de l’accueil, des pages de jeux, du dashboard et de l’authentification
utilisent déjà le catalogue central. La réactivation rend immédiatement le jeu
accessible. Filament conserve tous les jeux. Masquer un jeu préserve ses données
et son image. `SaveGame` conserve la visibilité quand la clé est omise et refuse
les valeurs non booléennes avant le traitement d’une image.

La visibilité est indépendante de l’année de fin. Pas de suppression, de
prévisualisation spéciale, ni de changement d’accès aux fichiers d’images.

## Vérification

Tests PostgreSQL/Livewire couvrant création, masquage, réactivation, modifications
partielles, validation, catalogue public, URL directes et accès administrateur.
Pint, PHPStan et suite complète, revue indépendante, puis migration locale.
