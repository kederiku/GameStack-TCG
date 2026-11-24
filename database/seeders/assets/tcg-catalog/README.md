# Visuels officiels du catalogue

Consultation et préparation : 2026-11-15. Logos du JCC Pokémon, extension de base
Écarlate et Violet / Scarlet & Violet. Deux visuels distincts, vérifiés visuellement.

- Français : [médiathèque officielle Pokémon](https://pokemon.gamespress.com/fr/Pokemon-TCG-Scarlet-Violet), onglet Logos, collection « Scarlet & Violet Logo » (8128), fichier original annoncé `SV01_Logo_FR.png`. La fixture utilise l’aperçu JPEG public de 240 × 65 pixels, fond blanc conservé. L’accès à la médiathèque le 24 novembre 2026 a renvoyé HTTP 401. L’empreinte du fichier versionné a été vérifiée à cette date.
- Anglais : [annonce officielle](https://press.pokemon.com/en/releases/First-Expansion-of-the-Pokemon-Trading-Game-Scarlet-Violet-Series-Comi), logo `Pokemon_TCG_Scarlet_Violet_Logo.png`, aperçu public transparent de 360 × 103 pixels.

Conversion locale dans Docker par GD, AVIF qualité 85, conservation des proportions
et de la transparence disponible. Le seeder utilise les fichiers locaux et
réencode chaque visuel à comparer avec le fichier stocké. L’action de sauvegarde
remplace les visuels différents et recrée les fichiers manquants. Les noms et
images appartiennent à leurs ayants droit.

Le catalogue réutilise aussi ces deux logos pour présenter la série. Chaque
série ou édition illustrée garde sa propre association et son propre fichier
stocké.

## Fichiers sources publics

- Français : https://imgeucdn.gamespress.com/cdn/files/Pokemon/2022/12/091341-55c08c49/SV01_Logo_FR_png_jpgcopy.jpg?w=240&mode=max&otf=y&quality=90&format=jpg&bgcolor=white&sky=9e06b3caa2b7ac62d6f199da12603306bdb2164ce4add704daf8d958ebfc2177
- Anglais : https://imguscdn.gamespress.com/cdn/files/PokemonAmerica/2022/12/081804-015b3c8d/Pokemon_TCG_Scarlet_Violet_Logo.png?w=360&mode=max&otf=y&quality=90&format=png&bgcolor=transparent&sky=1892f9c9af468d87ecc9ebf922d0010b2dad77dd885ba2daa539ec0716abf688

## Empreintes des fixtures

- `scarlet-violet-en.avif` : SHA-256 `5b39e1ea907f6aae02601ebe815bfa2dee89b16bd27d3445883dc303ecca5280` (16026 octets).
- `scarlet-violet-fr.avif` : SHA-256 `aad2502775d4195f3e23c24380940703f3cc459ab3460a9e9ecc265537029983` (7118 octets).
