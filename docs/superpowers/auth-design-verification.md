# Authentification Immersive — vérification du 14 novembre 2026

## Livraison

Évolution de `codex/immersive-landing`, depuis `796f45c`.
La demande applique le design Immersive précédemment approuvé aux sept pages
d’authentification. Le layout commun réutilise les tokens publics, le logo et
l’éventail de cartes ; le panneau décoratif est masqué sur mobile.

Pages : connexion, inscription, mot de passe oublié, réinitialisation,
confirmation du mot de passe, vérification e-mail et double authentification
(code OTP ou récupération).

Les champs gardent leurs noms, actions, autocomplete, gestion des erreurs,
réinitialisation après soumission et règles de sécurité. Le seul contrat
serveur ajouté est `canRegister` sur la connexion, pour masquer le lien
d’inscription lorsque Fortify désactive cette fonction.

## Contrôles automatisés

```sh
docker compose exec -T app composer ci:check
docker compose exec -T app npm run build
git diff --check
```

Résultats : **111 tests réussis, 581 assertions** ; Pint, PHPStan,
formatage/lint frontend, TypeScript et compilation de production réussis.

Le nouveau test `test_login_reports_whether_registration_is_available` a
d’abord échoué sur l’absence de `canRegister`, puis passé avec les états
activé et désactivé. La suite existante couvre connexion, inscription,
réinitialisation, confirmation, vérification e-mail et défi 2FA.

## Contrôles navigateur

- Connexion réelle : nouveau logo, titres condensés, palette publique et
  formulaire shadcn/ui ; aucun message d’erreur JavaScript observé.
- Sept écrans rendus à 320 px : largeur défilante égale à la largeur disponible,
  champs et actions accessibles, y compris l’inscription et le formulaire OTP.
- Desktop : panneau immersif et formulaire visibles ensemble.
- Sombre : connexion et 2FA observées. Clair : inscription et erreurs de
  connexion observées ; couleurs de carte et de texte vérifiées.
- Connexion → mot de passe oublié → connexion : navigation réelle vérifiée.
- Identifiants de test inexistants : message serveur affiché, `role="alert"`
  et champ e-mail marqué `aria-invalid=true`.
- Afficher/masquer le mot de passe : boutons français utilisables au clavier.
- Inscription désactivée : lien « Créer un compte » absent.
- Message de statut long : contenu lisible et aucun débordement à 320 px.
- Code OTP saisi, bascule récupération puis retour : noms accessibles et titre
  adaptés, ancien code OTP effacé.

Les pages protégées ont été prévisualisées via des routes de fixtures dans
un serveur local distinct sur `localhost:8001`, uniquement avec
`APP_ENV=testing` et `DB_DATABASE=gamestackr_testing`. Aucun compte, mot de
passe ou passkey métier n’a été créé ou modifié. Le serveur a été arrêté et
ses fichiers temporaires sont retirés après la relecture.

L’inscription, la réinitialisation et l’utilisation effective d’une passkey
matérielle n’ont pas été soumises dans le navigateur. Les parcours serveur
sont couverts par la suite HTTP existante ; les appels passkeys restent ceux
du composant existant et le formulaire classique reste présent lorsque le
navigateur ne prend pas en charge les passkeys.

## Décisions

- Continuer directement la direction déjà validée dans le checkout et la
  branche de design existants ; aucune nouvelle variante graphique n’a été créée.
- Vérifier la présentation réversible par navigateur, types et build, sans
  ajouter un framework frontend ni des tests qui recopient le CSS. Les futures
  régressions visuelles nécessitent toujours un contrôle navigateur.
- Traduire les libellés de cette interface et conserver les messages serveur
  et navigateur d’origine. Une traduction globale reste un chantier distinct.
- Les boutons de visibilité du mot de passe deviennent accessibles par Tab,
  aussi sur les pages de compte qui partagent ce composant ; leurs labels
  anglais par défaut sont préservés hors du layout auth.

## Relecture

Relecture indépendante de `796f45c..8493b6a` terminée : aucun défaut critique,
important ou mineur identifié. Le reviewer a relu les sept formulaires,
le layout, les composants partagés et leurs contrats ; `git diff --check`
a également passé dans sa revue.

Les éléments exclus de la revue ont été confirmés : traduction des messages
serveur/navigateur hors périmètre ; validation réelle d’une passkey matérielle
et opérations sur des comptes métier non effectuées ; README et présent
rapport terminés après cette revue. Aucun point de code n’est différé.
