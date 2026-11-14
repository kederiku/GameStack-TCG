# Harmonisation du site et de l’espace membre

## Intention et décisions

Le 14 novembre 2026, l’utilisateur demande de porter le nouveau design sur
toutes les pages et tous les composants qui gardent la présentation du starter,
en s’inspirant de CardCodex, sur une nouvelle branche.

Branche créée depuis `main` (`e719b01`) : `codex/design-harmonization`.
L’utilisateur précise que le périmètre est **le site et l’espace membre**,
sans l’administration Filament. Il retient l’approche **composants partagés et
réglages en panneaux**. Ce document est validé par l’utilisateur le 14 novembre
2026, avec accord pour lancer l’implémentation.

## État actuel et référence observée

L’accueil, les pages de jeu, les sept écrans d’authentification, le dashboard
et la navigation du compte utilisent déjà l’identité GameStackr. Les tokens
globaux définissent les couleurs claires/sombres ; Big Shoulders Display sert
aux titres et Instrument Sans aux textes.

Les trois pages de réglages restent composées comme le starter : textes anglais,
formulaires étroits posés sur le fond de page, double navigation latérale,
petits titres ordinaires. Leurs composants 2FA, passkeys et suppression de compte
gardent aussi ces conventions. Plusieurs primitives shadcn ont encore les
dimensions et arrondis initiaux ; l’authentification les corrige par CSS local.

La collection authentifiée de CardCodex a été inspectée dans Chrome :
<https://cardcodex.com/user/cedricdelagree/collection>.
La référence apporte une hiérarchie lisible : fond bleu nuit, panneaux ardoise,
bordures fines, titres condensés en capitales, champs généreux et arrondis,
actions bleues, navigation active teintée, commandes regroupées.
La page profil locale a également été observée pour confirmer les écarts.

## Approches

1. **Composants partagés et réglages en panneaux — choisie.** Harmoniser les
   primitives et les sections de compte, puis appliquer leur composition aux
   trois réglages. Cela couvre aussi les fenêtres et les états secondaires ;
   les changements communs demandent une vérification des pages déjà abouties.
2. **Retouches par écran, structure actuelle conservée.** Modifier les classes
   et textes localement. Intervention plus limitée mais répétition des styles,
   maintien de la double navigation et davantage de dérives futures.

La pile React/Inertia, Tailwind et shadcn/ui existante reste le support.

## Présentation proposée

### Réglages

- Largeur de contenu et rythme vertical cohérents avec « Mon espace ».
- Titre principal visible et condensé, description courte en français.
- Navigation secondaire compacte Profil / Sécurité / Apparence avec icônes,
  état actif explicite et `aria-current`, qui se replie ou revient à la ligne
  à 320 px. Elle remplace la seconde sidebar interne du starter.
- Sections dans des panneaux arrondis : titre, explication, contenu puis actions.
  Une section partagée matérialise cette structure sans créer un framework UI.
- Champs regroupés avec labels persistants, aides et erreurs voisines ; boutons
  d’enregistrement et états d’attente cohérents avec l’authentification.

**Profil :** présentation des informations existantes du compte, formulaire du
nom et de l’adresse e-mail, état de vérification et renvoi du lien lorsqu’il est
disponible. Panneau distinct pour supprimer le compte ; la fenêtre conserve la
saisie du mot de passe, la confirmation explicite et les erreurs existantes.
Aucun ajout de photo de profil ni de nouveau champ métier.

**Sécurité :** panneaux mot de passe, double authentification et clés d’accès.
Les états activé/désactivé et les clés enregistrées proviennent des props
existantes. Harmoniser les instructions de configuration, QR code, saisie OTP,
copie de la clé, codes de récupération, liste des passkeys, ajout et suppression.
Conserver les états indisponibles ou masqués selon les capacités existantes.

**Apparence :** trois choix Clair / Sombre / Système avec aperçu visuel simple
de la palette, sélection accessible et application immédiate via le hook
existant. Le sélecteur compact de la topbar partage la même source de vérité.

### Composants communs et autres pages

Harmoniser les primitives effectivement utilisées : boutons, champs, OTP,
cartes, badges, alertes, menus déroulants, dialogues et tiroirs. Les sélecteurs,
infobulles, cases à cocher et squelettes suivent les mêmes tokens lorsqu’un
ajustement est nécessaire. Les notifications gardent le même circuit de flash.

Choix proposés par l’agent : panneaux de rayon 16 px, champs et actions
principales de rayon 12 px et de hauteur 44–48 px, commandes compactes adaptées
à leur contexte. Le bleu marque les actions et sélections ; le rouge reste
réservé aux erreurs et actions destructives. Éviter de redimensionner une icône
ou une commande de sidebar comme un bouton de formulaire.

Les dialogues ont des titres cohérents, une fermeture nommée en français,
une hauteur bornée et un défilement utilisable sur petit écran. Préserver le
contraste du QR code et la sélection/copie des codes. Les menus portalisés
utilisent les tokens globaux en clair comme en sombre.

Auditer accueil, jeu, dashboard et les sept écrans auth après les modifications
communes et corriger leurs éventuels écarts de composants. Leurs compositions
déjà établies servent de référence. Les styles locaux devenus redondants sont
consolidés. Les layouts alternatifs du starter ne sont actuellement associés
à aucune route : vérifier leurs références et supprimer les doublons réellement
inutilisés plutôt que conserver une seconde identité prête à réapparaître.
Ne pas supprimer de primitive shadcn au seul motif qu’elle est peu utilisée.

Tous les textes d’interface concernés, titres de document, fils d’Ariane,
états vides, libellés accessibles et messages frontend internes passent en
français. Les messages fournis par Laravel, WebAuthn ou le navigateur gardent
leur comportement et leur langue actuelle.

## Contraintes et limites

- Réutiliser le logo, les polices, les tokens et les composants du projet.
- Pas de nouvelle dépendance, nouveau modèle de données ou nouvelle route
  métier requis. Pas de copie d’assets CardCodex.
- Préserver formulaires Inertia, noms de champs, actions, reset, validation,
  focus après erreur, autorisations, confirmation du mot de passe, Fortify et
  WebAuthn. Aucun changement de politique de sécurité.
- Filament n’est pas modifié. Les catalogues, collections, prix, scanner et
  autres fonctions visibles sur CardCodex restent hors de cette refonte.
- Aucun chiffre, indicateur de sécurité ou contenu utilisateur fictif.
- Respecter les modes clair/sombre/système, le clavier, les préférences de
  réduction des animations et les contenus longs.

## Validation attendue

- TypeScript, vérifications frontend, suite existante et build dans
  l’environnement Docker du projet ; `git diff --check`.
- Les tests existants de profil, sécurité, authentification et pages publiques
  contrôlent les contrats préservés. Ne pas écrire de tests qui recopient les
  textes ou les classes CSS ; ajouter un test seulement si une modification
  de comportement le justifie.
- Contrôle navigateur à 320, 768 et 1440 px : profil, sécurité, apparence,
  menus et fenêtres ; aucun débordement ni action inaccessible.
- Contrôle des trois thèmes, focus clavier, Échap/retour du focus, noms longs,
  erreurs, enregistrement en cours et états vides.
- Régression visuelle accueil, jeu, dashboard et authentification, notamment
  les champs de mot de passe, OTP et passkeys.
- Les scénarios sensibles (2FA, clés d’accès, suppression) sont vérifiés avec
  fixtures et tests isolés ; les comptes réels du navigateur ne sont pas
  modifiés pour la QA. Consigner les limites de vérification matérielle WebAuthn.

## Détails laissés à la réalisation

Nom exact de la section partagée, composition des aperçus d’apparence,
espacements fins et icônes Lucide. Les choix doivent rester dans l’identité
actuelle et respecter les contraintes ci-dessus. Livrer le travail local sur
la nouvelle branche et un rapport de vérification ; fusion et publication ne
font pas partie de cette demande.
