# Vérification des services de développement — 15 novembre 2026

## Contrôles automatisés

- `make check` : réussi, 205 tests / 1277 assertions ; Pint, PHPStan,
  formatage et lint frontend, génération Wayfinder et TypeScript réussis.
- `make assets` : build Vite réussi.
- `make up` : tous les services locaux, dont `service-status`, démarrés.
- `make test-storage` après `make up` : 2 tests / 18 assertions réussis
  contre Garage réel et la base dédiée `gamestackr_testing`.
- Les tests ajoutés ont été exécutés en échec avant leur implémentation,
  puis réussis. Ils couvrent les autorisations, les rafraîchissements Livewire,
  les liens à ports personnalisés, les pannes, le filtrage du projet et les
  rapports absents, invalides ou périmés.
- Le changement de profil Docker a été revérifié par `make check` et
  `make test-storage` ; `git diff --check` ne signale aucun défaut.

## Parcours réel dans Chrome

- Session administrateur existante, aucun compte ni jeu de test ajouté.
- Navigation vers « Développement → État des services » : trois services
  répondent, rapport Docker frais et huit conteneurs reconnus.
- Actualisation manuelle et automatique observées ; appels Livewire HTTP 200.
- Mailpit et Garage Web UI s’ouvrent dans un nouvel onglet et leurs interfaces
  sont accessibles. Les onglets de vérification ont été refermés.
- Rendus clair et sombre contrôlés ; préférence initiale « système » restaurée.
- Largeur mobile 400 px : cartes empilées, libellés et liens lisibles,
  menu latéral accessible. Vue ordinateur restaurée après vérification.

## Démarrage hors développement et limites

Le premier essai a révélé qu’un collecteur quittant normalement hors local
faisait échouer `docker compose up --wait`. Le profil du collecteur est désormais
conditionné à `APP_ENV`, et Make active uniquement `local`. L’essai
`APP_ENV=production docker compose --profile local up -d --wait`, collecteur
préalablement arrêté, a réussi avec les autres services en cours. `make up`
a ensuite rétabli le collecteur local et son rapport.

Les pannes de PostgreSQL, Mailpit et Garage sont couvertes par les tests ; les
services métier de l’installation n’ont pas été arrêtés pour simuler une panne.
Une panne PostgreSQL peut rendre l’admin inaccessible, car son authentification
et ses sessions utilisent cette base. La sonde Garage contrôle le cluster local,
pas les droits S3. Le socket Docker reste puissant : il appartient uniquement au
collecteur isolé, sans API réseau et avec un rapport minimal en sortie.
