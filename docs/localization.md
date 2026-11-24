# Localisation

Les URL canoniques commencent par `/fr` ou `/en`. Le préfixe décide de la langue,
y compris pour un utilisateur connecté ayant une autre préférence. Le sélecteur
enregistre le choix de l’utilisateur, conserve la ressource et les critères de
navigation applicables. Les identifiants de jeu, de catalogue et d’extension sont
stables. Les langues de contenu et d’édition viennent de la table `languages`.
L’activation de l’interface dépend de `config/localization.php`.

## Installation neuve

`make init` vérifie les traductions, applique les migrations Laravel et
synchronise dans `languages` les entrées du registre actif, `fr` et `en` par
défaut. L’installation laisse le catalogue et les comptes vides sur une base
neuve. `make seed` charge le catalogue de référence à la demande, avec les
langues de contenu utilisées par les éditions. Sur une base utilisant l’ancien
historique, suivre les [instructions de réinitialisation](../README.md#installation-utilisant-lancien-historique-de-migrations)
avant `make init`.

Les commandes Artisan de localisation sont les suivantes.

| Commande                      | Effet                                                                                                                   |
| ----------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `localization:check`          | Vérifier les dictionnaires, paramètres et pluriels des locales actives.                                                 |
| `localization:export`         | Générer les dictionnaires, types et adaptateurs de routes pour React. `make translations` lance la génération complète. |
| `localization:sync-languages` | Ajouter au référentiel de contenu les langues d’interface actives qui manquent.                                         |

## Ajouter une langue

1. Préparer en dehors du registre actif son code de préfixe, sa locale Laravel,
   son tag HTML, son libellé et sa direction (`ltr` ou `rtl`). Réunir les
   neuf domaines PHP `common`, `auth`, `account`, `catalog`, `admin`,
   `validation`, `passwords`, `notifications`, `routes` dans
   `lang/{localeLaravel}/`, ainsi que
   `lang/vendor/{fortify,passkeys,laravel}/{localeLaravel}/messages.php`.
   Préserver les placeholders, les paramètres des chemins et les pluriels.
   Traduire les segments de navigation et vérifier les textes natifs de Filament.
2. Sur une installation isolée, ajouter atomiquement les dictionnaires et
   l’entrée du registre à la version à tester. Exécuter `make artisan
   ARGS='localization:check'` et `make artisan ARGS='route:cache'` pour vérifier
   les dictionnaires et la construction des routes. Vérifier que le nouveau
   préfixe et les segments réservés ne masquent aucun slug existant. Contrôler
   le public, l’authentification, Filament, le sélecteur et le repli. Prévoir
   un contrôle visuel supplémentaire pour une langue RTL.
3. Déployer les fichiers et le registre ensemble sous maintenance. Régénérer la
   configuration (`make artisan ARGS='config:cache'`), les routes
   (`make artisan ARGS='route:cache'`), les assets (`make assets`) et synchroniser
   le référentiel de contenu avec `make artisan ARGS='localization:sync-languages'`.
   Redémarrer les processus persistants avant réouverture. `make translations`
   génère à la demande les routes Wayfinder, l’adaptateur typé et les dictionnaires
   React. Les fichiers générés sont ignorés par Git.
4. Les traductions de contenu peuvent ensuite être complétées dans Filament.
   Les éditions imprimées se gèrent séparément des traductions de texte. Le nom
   d’affichage utilise la langue demandée, puis l’anglais, puis la langue par défaut
   du catalogue pour les séries et extensions ou celle de l’application (le français)
   pour les autres entités. Le nom de référence intervient en dernier. Les champs
   descriptifs et les périodes annoncées passent directement de la langue demandée
   au repli du modèle, puis au texte de référence. Une traduction explicitement
   vide interrompt le repli du champ.

## Référencement

L’accueil, les jeux et les fiches d’extension ont une URL canonique dans la
langue visitée. Chaque page indexable publie les autres langues pour la même
ressource et `x-default` en français. Une page de liste conserve `page=2` et
les pages suivantes. Les zéros initiaux sont normalisés. Une page hors plage prend
l’identité de la dernière page affichée et une valeur invalide celle de la première.
Les filtres, la recherche et le tri actifs, ainsi qu’une
édition explicitement sélectionnée, donnent `noindex,follow` et une canonique
pour cette sélection normalisée. Les valeurs par défaut et paramètres de suivi
disparaissent. Les écrans auth/compte, les liens signés et Filament ne publient
aucune variante SEO. Le document définit sa langue et sa direction dès la
réponse HTML puis les actualise lors des navigations Inertia.

## Compatibilité des anciens liens

| Entrées conservées                                                                                                                                                                                                                                                                        | Usage                                                                                                                                                                                                                                                                                            |
| ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `/`, `/{jeu}`, `/{jeu}/{catalogue}/{extension}`, `/dashboard`, `/settings`, `/settings/{profile,security,appearance}`                                                                                                                                                                     | GET/HEAD redirige vers la page localisée. Le choix historique suit `lang`, le profil, le cookie, puis FR. Les liens du catalogue vérifient la visibilité.                                                                                                                                        |
| `/admin`, `/admin/login`, `/admin/development-services`, `/admin/{ressource}` et ses pages `create` et `{record}/edit` disponibles                                                                                                                                                        | Redirection des favoris vers le panneau de même identité. Les sept ressources sont jeux, catalogues, séries, extensions, éditions, langues et marchés.                                                                                                                                           |
| Vues Fortify `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`, `/email/verify`, `/user/confirm-password`, `/two-factor-challenge`                                                                                                                                     | Les GET/HEAD de présentation redirigent. Les noms historiques continuent de satisfaire les appels du framework.                                                                                                                                                                                  |
| Actions Fortify d’inscription, connexion et déconnexion, mot de passe oublié/réinitialisé/confirmé, vérification d’e-mail et renvoi, mise à jour profil, challenge/activation/confirmation/désactivation 2FA, QR code, clé secrète et codes de récupération, options et mutations passkey | Les alias non préfixés gardent verbes, contrôleurs et middlewares pour les anciens formulaires, clients JSON et liens signés. L’inventaire exact par nom et URI figure dans `AuthRouteDefinitions::legacyPaths()`. Une ancienne URL de vérification signée est exécutée à son adresse d’origine. |
| PATCH/DELETE `/settings/profile`, PUT `/settings/password`                                                                                                                                                                                                                                | Compatibilité des anciennes mutations de compte avec les protections d’origine.                                                                                                                                                                                                                  |
| `/.well-known/passkey-endpoints`                                                                                                                                                                                                                                                          | Point d’entrée standard pour la découverte des passkeys. Les destinations générées sont localisées.                                                                                                                                                                                              |

Les trois fichiers de messages vendor adaptent les clés textuelles attendues par
Fortify, Passkeys et Laravel à la source éditable PHP.
