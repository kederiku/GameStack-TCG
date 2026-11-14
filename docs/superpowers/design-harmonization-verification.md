# Harmonisation du design — 14 novembre 2026

## Réalisation

Branche `codex/design-harmonization`, base `e719b01` (`main`).
[Design approuvé](specs/2026-11-14-design-harmonization-design.md) et
[plan](plans/2026-11-14-design-harmonization.md).

- Profil, sécurité et apparence utilisent `SettingsSection` et une navigation
  interne compacte. Titres, formulaires, fenêtres, états vides et libellés
  accessibles sont en français.
- Boutons, inputs, cartes, OTP, badges, alertes, menus, dialogues et tiroirs
  partagent les surfaces, dimensions, arrondis et focus GameStackr.
- Aperçus de thème avec la même source d’état que la topbar ; grille adaptative
  selon l’espace réellement disponible.
- Fenêtres limitées à la hauteur du viewport, QR sur fond blanc, clés longues
  contenues, codes sélectionnables et mise en pause des animations concernées
  selon la préférence de réduction des mouvements.
- Neuf composants/layouts du starter supprimés après vérification de leurs
  imports : trois layouts auth alternatifs, layout header, header, deux alias
  de logo, ancien Heading et footer de navigation.
- Aucun contrôleur, schéma, dépendance ou code Filament modifié.

## Vérifications automatisées

Commandes exécutées dans Docker :

```sh
docker compose exec -T app composer ci:check
docker compose exec -T app npm run build
git diff --check
```

Résultat : **119 tests réussis, 757 assertions** ; formatage/lint frontend,
TypeScript, Pint et PHPStan passent ; compilation de production réussie.
La baseline Settings/Auth comptait 47 tests réussis et 313 assertions.
Aucun test qui recopie les textes ou les classes CSS n’a été ajouté.

Le build conserve le message informatif préexistant du plugin de polices :
la dépendance optionnelle `fontaine` n’est pas installée. Un premier essai
`npm run check` sur macOS a confirmé l’absence du binaire natif Darwin dans
les dépendances installées pour Linux ; les contrôles ont ensuite utilisé
Docker, comme prévu par le projet, sans changer les dépendances.

## Contrôles navigateur

Prévisualisation locale temporaire sur `localhost:8001`, avec des props
fictives et le frontend compilé. Aucun contrôleur de mutation n’y est exécuté,
aucune opération en base n’y est effectuée et les soumissions simulent des
redirections. Les comptes réels et la session Chrome ne sont pas modifiés.

- Les 13 écrans React ont été chargés à 320 px : trois réglages, dashboard,
  accueil, page de jeu et sept pages auth. Aucun débordement horizontal.
- Profil et apparence inspectés en clair/sombre à 1440 px, sidebar ouverte
  et repliée. Noms et adresses longs testés à 320 px.
- Profil : erreurs de formulaire simulées après soumission, adresse non
  vérifiée, fenêtre de suppression sans confirmation, labels persistants.
- Sécurité : 2FA activée/inactive, configuration, OTP, codes de récupération,
  passkeys vides et noms de clés très longs. Aucun secret réel utilisé.
- Fenêtre 2FA à 320 × 500 : défilement interne et boutons accessibles.
  Le bouton OTP trop étroit a été observé puis corrigé en donnant au formulaire
  toute la largeur disponible ; le libellé est désormais entier.
- Fermeture 2FA par Échap : retour du focus initialement perdu vers le body,
  reproduit puis corrigé vers « Poursuivre la configuration ».
- Tiroir mobile : navigation vers une autre page et fermeture ; aperçu tablette
  à 768 px, grille d’apparence élargie après constat de choix trop serrés.

La validation d’une authentification WebAuthn par un appareil physique et
les changements de mot de passe du compte réel ne font pas partie des essais
visuels. Les contrats serveur restent couverts par les tests existants.
Les messages et dates relatives fournis par Laravel ou le navigateur conservent
leur langue actuelle, conformément au périmètre du design.

## Relecture

Revue indépendante de la branche à consigner après réception.
