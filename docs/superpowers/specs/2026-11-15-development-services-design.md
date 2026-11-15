# Services de développement dans Filament

## Intention et approche retenue

L’utilisateur souhaite connaître la disponibilité des services et leur état Docker,
avec des liens vers leurs interfaces dans le menu admin, uniquement en développement.
Il a choisi le petit collecteur Docker et demandé une nouvelle branche ainsi qu’une
présentation soignée avec les composants natifs Filament.

Deux approches ont été examinées : collecteur publiant un rapport local, ou proxy
Docker interrogé par Laravel. Le collecteur évite d’exposer l’API Docker à Laravel
et ne demande qu’un conteneur supplémentaire. Branche :
`codex/admin-development-services`.

## Comportement et présentation

- Groupe « Développement » : page « État des services », Mailpit et Garage Web UI.
  Les deux interfaces s’ouvrent dans un nouvel onglet avec leurs ports configurés.
- Page réservée aux administrateurs vérifiés et à `APP_ENV=local`, y compris
  les accès directs et les actualisations Livewire. `APP_DEBUG` ne donne pas accès.
- Trois cartes natives Filament : PostgreSQL (requête de lecture), Mailpit
  (disponibilité HTTP) et Garage (santé du service), avec badge de réponse,
  durée du contrôle et indication Docker distincte.
- Une section liste aussi les autres conteneurs : application, worker, Vite et
  Garage Web UI. Afficher les états arrêtés ou absents sans les confondre avec
  un service joignable. Aucun compteur ou résultat inventé.
- Actualisation toutes les 15 secondes lorsque la page est visible, bouton manuel,
  date du contrôle, états de chargement, mode sombre et grille adaptative.
- Composants Section, Grid, TextEntry, badges, icônes et Actions de Filament 5 ;
  pas de thème ou dépendance frontend supplémentaire.

## Collecte et limites

- Collecteur isolé, sans réseau et sans port publié. Seul ce conteneur accède
  au socket Docker ; Laravel lit un volume de rapport monté en lecture seule.
- Collecte toutes les 5 secondes, limitée au projet Compose courant et aux
  services connus. Exclure les conteneurs ponctuels de commandes Make.
- Ne publier que les noms de service, états et santé Docker ; jamais les
  variables d’environnement, commandes, volumes, identifiants ou logs.
- Écriture atomique. Un rapport absent, corrompu ou vieux de plus de 30 secondes
  produit « Indisponible » ; une panne du collecteur n’invalide pas les sondes.
- Le collecteur ne consulte pas Docker hors `APP_ENV=local`. Aucune action de
  démarrage, arrêt, redémarrage ou suppression depuis l’admin.
- Les sondes sont bornées et sans écriture. Les erreurs visibles restent génériques.
  Une panne PostgreSQL peut empêcher l’accès à l’admin, car comptes et sessions
  dépendent déjà de cette base ; cette page ne remplace pas un moniteur externe.
- Pas d’ajout d’interface PostgreSQL : aucun service de ce type n’existe actuellement.

## Validation

Tests d’accès et navigation local/production, Livewire, pannes de sondes, fraîcheur
et filtrage du rapport. Vérification réelle du collecteur Docker et du navigateur
sur ordinateur et mobile. `make check`, `make assets`, `make up`,
`make test-storage`, revue indépendante, CI et fusion suivant `AGENTS.md`.

Références : [pages Filament](https://filamentphp.com/docs/5.x/navigation/custom-pages),
[sections](https://filamentphp.com/docs/5.x/schemas/sections),
[API Docker](https://docs.docker.com/reference/api/engine/version/v1.56/).
