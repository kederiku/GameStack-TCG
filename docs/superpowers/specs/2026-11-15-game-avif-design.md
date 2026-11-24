# Images des jeux en AVIF

> Archive historique. La commande de conversion des images existantes a été retirée.
> Voir [les formats d’envoi actuels](../../../README.md#jeux).

Tous les visuels des jeux enregistrés via Filament seront stockés en AVIF, y compris les imports AVIF, JPEG, PNG, WebP et SVG. Les dimensions des images matricielles, la transparence et l’orientation EXIF des JPEG sont conservées. Les limites actuelles restent 5 Mio et 4096 × 4096 pixels. Les SVG sont nettoyés puis rendus proportionnellement dans une boîte de 2048 × 2048 pixels, sans fond ajouté.

GD assure l’encodage AVIF (qualité 85) ; librsvg assure le rendu SVG via l’entrée standard, sans chemin de base, avec un délai maximal de 10 secondes. Docker fournit ces dépendances. Livewire doit permettre l’aperçu AVIF avant l’enregistrement. Les erreurs restent affichées sur le champ image et préservent le jeu et son ancien fichier.

Les images matricielles intégrées dans un SVG sont contrôlées avant rendu : dimensions maximales de 4096 × 4096 et budget total de 16 777 216 pixels. Le processus natif est limité à 512 Mio d’espace d’adressage avec `prlimit` (util-linux). Ces contrôles empêchent un petit SVG compressé de déclencher un décodage disproportionné.

Les images existantes disposent d’une commande de conversion avec simulation. Chaque original est sauvegardé sur le disque privé, sous `game-images-before-avif/{chemin original}`, avant tout remplacement. Une transaction verrouille chaque jeu et ne modifie que son image. La commande continue après une erreur, renvoie un échec si nécessaire et ignore les images déjà AVIF ; une nouvelle exécution est sans effet. Aucun changement de schéma SQL.

Périmètre : images des jeux administrées par Filament et leurs fichiers existants. Les icônes de l’application et les ressources graphiques non utilisées ne font pas partie de ce traitement d’upload.

Conserver les garanties existantes : noms normalisés, URL unique par révision, suppression après commit, nettoyage après rollback, protection contre les chemins arbitraires.
