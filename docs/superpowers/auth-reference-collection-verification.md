# Auth de référence et textes collection — vérifications

Évolution du 14 novembre 2026 sur `codex/immersive-landing`, depuis `635de9b`.

## Résultat

Le layout commun aux sept écrans auth reprend la composition observée sur
[CardCodex](https://cardcodex.com/auth/login/) : panneau de formulaire à gauche
avec logo et titre centrés, grand visuel à droite. Le document reste défilable
pour que les formulaires longs gardent toutes leurs actions accessibles.
Les composants shadcn/ui, les routes et les mécanismes d’authentification
restent ceux de la réalisation précédente.

Le visuel original est livré dans `resources/images/auth-collection-v1.webp`.
Il a été créé avec imagegen intégré, puis encodé en WebP qualité 85 sans
redimensionnement : 1086 × 1448, 255 422 octets. Le [prompt et la provenance](auth-collection-artwork.md)
sont versionnés. Aucune image de CardCodex n’est utilisée par le site.

La landing a été réécrite : titre et description SEO, accroche, présentation,
CTA, section des jeux, libellés des tuiles, état vide, à propos et pied de page.
L’accroche est « Vos cartes. Toutes vos collections. ». Les cartes possédées,
doubles, séries et pièces manquantes décrivent le projet. Le texte précise
que les outils de gestion de collection sont en préparation.

## Contrôles automatisés

```sh
docker compose exec -T app composer ci:check
docker compose exec -T app npm run build
git diff --check
```

**111 tests réussis, 581 assertions.** Formatage, lint, TypeScript, Pint et
PHPStan réussis. Compilation de production réussie (1,73 s) ; le WebP apparaît
dans les assets compilés avec un nom versionné par Vite.

Aucun nouveau contrat serveur n’est ajouté. Pas de tests recopiant les textes
ou le CSS ; la suite existante et le contrôle navigateur couvrent ce changement.

## Contrôles navigateur

- Référence et version initiale inspectées, puis nouvelle composition observée.
- Connexion desktop sombre : logo, formulaire à gauche, véritable WebP à droite.
- À 320 px : connexion, inscription, défi 2FA et landing sans débordement
  horizontal ; les boutons et les liens du bas d’inscription sont accessibles au clavier.
- Illustration mobile : `display: none` et `background-image: none`.
  L’URL de l’image n’est déclarée que dans la media query desktop.
- Thème clair : OTP, landing et contraste du bouton passkey vérifiés.
- Landing avec les jeux de développement réels et landing sans jeu en test.
- Liens « Choisir mon TCG », « Découvrir le projet », tuile Pokémon et retour
  à l’accueil : navigation vérifiée, y compris retour navigateur vers la connexion.
- Aucune erreur JavaScript observée sur la connexion finale.

Un problème de chargement du premier asset a été reproduit : une URL CSS
absolue vers `public/images` pointait vers le serveur Vite, dont le dossier
public est désactivé par le plugin Laravel. L’image a été intégrée dans
`resources/images` avec une URL CSS relative. Le rendu réel et le bundle de
production ont ensuite été vérifiés.

Les variantes mobiles et la page protégée 2FA ont été prévisualisées sur un
serveur local de test distinct, avec `gamestackr_testing`, sans création de
compte ni modification de données métier. Le serveur est arrêté et les fichiers
temporaires ont été retirés à la fin de la vérification.

## Décisions et limites

- Appliquer directement la référence explicitement demandée, sur la branche
  de design existante ; aucune nouvelle sélection de maquettes.
- Utiliser une illustration originale multi-TCG. Ces cartes sont décoratives
  et ne correspondent pas à des enregistrements du catalogue.
- Présenter honnêtement les fonctions collection comme en préparation ;
  cette livraison ne crée pas de gestion de collection.
- Contrôler les changements visuels au navigateur sans nouveau framework de
  tests frontend ; les évolutions visuelles nécessitent toujours ce contrôle.
- Aucun fournisseur Google/Discord ni changement de sécurité n’est ajouté.

## Relecture

Relecture indépendante de `635de9b..f426f8d` terminée : aucun défaut critique,
important ou mineur identifié. Le reviewer a inspecté la composition, les
composants partagés, le chargement de l’asset et la cohérence des textes.

Les exclusions de la revue sont confirmées : l’implémentation des collections
et une refonte du backend auth ne font pas partie de cette demande ; README
et présent rapport ont été finalisés après la revue. Aucun point de code différé.
