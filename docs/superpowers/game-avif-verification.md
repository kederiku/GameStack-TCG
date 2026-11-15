# Vérification de la conversion AVIF — 15 novembre 2026

- Branche : `codex/game-details`, changements AVIF depuis `b5c3e8c`.
- RED initial : l’upload produisait `.webp` au lieu de `.avif` ; la commande de conversion n’existait pas.
- Image Docker reconstruite avec GD AVIF, librsvg et util-linux ; services app, vite et queue recréés, PostgreSQL conservé.
- `docker compose exec -T app composer test` : Pint 91 fichiers, PHPStan 64 fichiers, **169 tests / 1050 assertions réussis**.
- Uploads PNG/JPEG/WebP/SVG/AVIF, transparence, orientation EXIF, aperçu AVIF Livewire, renommage, suppression, rollback et protection des chemins vérifiés.
- Commande : simulation, sauvegarde privée exacte, préservation des métadonnées, relance sans effet, erreurs de sauvegarde/base/fichier et poursuite des autres jeux vérifiées.
- Revue indépendante : un point important sur la mémoire des images intégrées dans les SVG. Deux régressions reproduites en échec avant correction, puis réussies ; petit PNG intégré toujours rendu. Contrôle des dimensions et du budget de pixels avant rendu, plafond natif de 512 Mio. Aucun autre point ni réserve de revue.
- Simulation locale : 12 WebP à convertir, aucune erreur.
- Conversion locale : **12 images converties**, aucune erreur. Les fichiers AVIF ont été décodés, leurs dimensions vérifiées et les autres métadonnées comparées au relevé préalable.
- Les 12 originaux ont une empreinte SHA-256 identique à leur sauvegarde privée dans `storage/app/private/game-images-before-avif/`. Le relevé local `manifest-before-2026-11-15.json` conserve leurs identifiants et anciens chemins.
- Les 12 URL publiques répondent HTTP 200 avec `Content-Type: image/avif`.
- Seconde exécution locale : zéro conversion, zéro erreur.

Les icônes de l’application restent indépendantes des images des jeux. Les sauvegardes locales et la base ne sont pas versionnées ; pour un autre environnement, suivre les commandes du README.
