# Informations complémentaires des jeux

## Demande

Sur une nouvelle branche, ajouter au modèle `Game` quatre informations
facultatives, éditables à la création et à la modification dans Filament :
année de création, année de fin, nom de l’éditeur et petite description.

## Réalisation

- Branche `codex/game-details`, dans le checkout existant.
- Nouvelle migration additive : `start_year` et `end_year` en entiers courts
  nullables, `publisher` en chaîne nullable, `description` en texte nullable.
  Les jeux existants gardent leurs données et reçoivent des valeurs nulles.
- Années sur quatre chiffres (1000 à 9999), converties en entiers par le modèle.
  Les deux années sont indépendamment facultatives ; lorsqu’elles sont connues,
  la fin est supérieure ou égale à la création. Les années futures sont permises.
  Ces années décrivent le TCG, indépendamment des timestamps de la fiche.
- Éditeur limité à 255 caractères ; description en texte simple multiligne.
- `SaveGame` valide les informations avant tout traitement d’image. Omettre
  une clé conserve sa valeur ; transmettre `null` ou du texte vide l’efface.
- Formulaire Filament en français avec deux saisies numériques, une saisie
  d’éditeur et une zone de description. Chaque champ peut rester vide.

## Approche retenue

Choix de réalisation de l’agent : étendre les colonnes du modèle et le service
existant. Une table d’éditeurs serait disproportionnée pour un simple nom ;
des dates complètes imposeraient des jours et mois absents de la demande.
La demande est suffisamment précise pour une réalisation directe.

## Périmètre et vérification

Pas de données historiques préremplies ni de modification des pages publiques.
Tests de persistance PostgreSQL et parcours Livewire : création, modification,
effacement, champs omis, années isolées, chronologie et saisies incorrectes.
Vérification finale avec Pint, PHPStan et la suite complète.
