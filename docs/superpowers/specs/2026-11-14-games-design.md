# Jeux dans Filament

## Demande

Créer un modèle Game avec nom, slug et image optionnelle, tous éditables dans
Filament. Conserver les images localement, au format WebP (interprétation de
« format web »), avec un nom dérivé du jeu. Exception demandée ensuite : les
images SVG conservent le format SVG. Remplacer et supprimer les fichiers avec
leur jeu.

## Choix de réalisation

- Table `games` : id, name, slug unique, image nullable, timestamps.
- Nom obligatoire, 255 caractères ; slug ASCII en minuscules, chiffres et tirets,
  255 caractères, proposé depuis le nom à la création et ensuite modifiable.
  Un changement de nom ne modifie pas automatiquement un slug existant.
- Ressource Filament en français : liste avec miniature, recherche, création,
  modification, suppression individuelle et groupée ; accès administrateur vérifié.
- JPEG, PNG et WebP, maximum 5 Mio et 4096 × 4096 pixels ; conversion réelle en
  WebP qualité 85, transparence conservée et orientation JPEG corrigée.
- SVG jusqu’à 5 Mio, conservé en SVG après nettoyage des contenus actifs et
  références distantes ; pas de limite de dimensions en pixels pour ce format
  vectoriel. Refus des SVG malformés.
- `storage/app/public/games/{uuid}/{nom-normalise}.webp` ; UUID renouvelé à chaque
  image pour éviter collisions et cache périmé, même si deux jeux ont le même nom.
  Extension `.svg` pour les SVG. Nom de fichier limité à 180 caractères, repli
  sur `jeu` si non translittérable.
- Renommer un jeu renomme aussi son image ; retirer l'image la supprime à la
  sauvegarde. Quitter le formulaire sans enregistrer conserve l'image actuelle.
- Préparer le nouveau fichier dans une transaction, supprimer le nouveau sur
  rollback et l'ancien après commit ; ne jamais écraser l'ancien avant succès.
- Refuser les chemins d'images étrangers envoyés par un formulaire modifié.
  Sérialiser les modifications du même jeu par verrou de ligne.

## Approches

Choix de réalisation de l'agent : service de sauvegarde, stockage GD et observer
Eloquent pour le nettoyage, intégrés au FileUpload standard de Filament.
Une médiathèque supplémentaire est disproportionnée pour une image par jeu.
Une conversion uniquement dans le navigateur ne garantit pas le format stocké.
GD et EXIF sont ajoutés à Docker ; `enshrined/svg-sanitize` nettoie les SVG.

## Limites et validation

Pas de pages publiques ni de relation avec des cartes dans cette demande.
Les modifications passent par `SaveGame` ; les suppressions utilisent Eloquent
instance par instance (les suppressions SQL de masse contournent ses événements).
Un arrêt brutal du processus ou une panne disque reste une limite à l'atomicité
entre PostgreSQL et fichiers ; les erreurs de nettoyage doivent être signalées.
Tests PostgreSQL et vrais fichiers WebP, parcours Livewire Filament, Pint, PHPStan
et suite complète du projet.
