# Authentification — continuité du design Immersive

L’utilisateur demande d’appliquer le nouveau design aux pages d’authentification,
dans la continuité de la direction Immersive déjà choisie. Réutiliser la palette
publique, le logo GameStackr/TCG, Big Shoulders Display, les cartes décoratives
et les composants shadcn/ui existants.

## Approche

Un layout d’authentification commun permet de mettre à jour les sept écrans
ensemble. Refaire chaque page séparément dupliquerait la structure ; utiliser
le layout public complet demanderait des données de jeux inutiles aux formulaires.
L’agent retient donc un layout dédié partageant les éléments de marque existants.

Sur ordinateur : logo et retour à l’accueil en haut, panneau d’ambiance à gauche,
formulaire dans une surface arrondie à droite. Sur mobile : logo, lien de retour
et formulaire, sans panneau décoratif encombrant. Choix de détail de l’agent,
en application de la charte déjà approuvée.

## Périmètre

Connexion, inscription, demande de réinitialisation, nouveau mot de passe,
confirmation du mot de passe, vérification e-mail, défi 2FA et mode récupération.
Libellés français et `lang="fr"` dans ce layout, thèmes clair/sombre/système,
champs et boutons tactiles, erreurs et statuts lisibles, noms accessibles.

Conserver les actions Fortify/Inertia, noms de champs, tokens, validation,
autocomplete, soumission, passkeys, liens de récupération et bascule 2FA.
Le lien d’inscription sur la connexion suit le drapeau Fortify, comme la topbar.
Les traductions des messages serveur et navigateur restent celles de leur source.
Aucun changement de règles de sécurité, des comptes, de Filament ou des réglages.

## Vérification

Contrôle navigateur avant/après, desktop et 320 px, clair/sombre, clavier,
messages d’erreur et états de formulaire ; routes protégées rendues avec des
fixtures de prévisualisation temporaires uniquement. Tests HTTP existants,
test du drapeau d’inscription, lint, types, PHPStan et build. Relecture indépendante.
