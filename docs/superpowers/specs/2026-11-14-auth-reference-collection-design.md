# Auth CardCodex et positionnement collection

L’utilisateur demande de rapprocher les pages d’authentification de
https://cardcodex.com/auth/login/ et de réécrire tous les textes de la landing
autour de la gestion de collections de cartes. Cette demande remplace la
composition auth précédente ; la palette, le logo et shadcn/ui sont conservés.

## Référence et approche

Page de référence inspectée dans le navigateur : panneau de formulaire à
gauche, logo et titre centrés dans ce panneau, grand visuel de carte à droite,
surfaces ardoise arrondies et bouton principal bleu.

Reprendre cette composition dans le layout auth commun. Le formulaire garde
une largeur de lecture contenue, ses labels accessibles, passkeys, erreurs et
contrats Fortify. Le visuel est masqué sur mobile ; les formulaires longs
défilent avec le document pour rester accessibles.

Choix de l’agent : illustration originale de trois cartes fantasy pour porter
l’identité multi-TCG, créée avec imagegen intégré et livrée comme asset WebP.
Le logo renvoie à l’accueil ; un lien de retour reste disponible sous le formulaire.
Aucun fournisseur de connexion sociale supplémentaire n’est ajouté.

## Landing

Réécrire titre et description SEO, accroche, texte de présentation, CTA,
section des jeux, liens des tuiles, état vide, section à propos et signature du
pied de page. Parler de cartes, collections, doubles, séries et pièces manquantes.
Les CTA gardent des destinations réelles : choix d’un jeu et présentation du projet.
Préciser que les outils de gestion de collection sont en préparation ; la demande
de textes ne constitue pas une implémentation de ces fonctions.

## Vérification

Desktop, 320 px, clair/sombre, formulaire long, OTP, chargement du WebP,
navigation existante, lisibilité de la landing. Contrôles projet existants,
compilation et revue indépendante. Aucun nouveau test qui recopie la prose/CSS.
