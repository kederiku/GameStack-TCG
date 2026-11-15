# Administration plus lisible et guidée

L’utilisateur demande une administration plus simple à utiliser et plus lisible.
Il choisit explicitement « Améliorer directement » : navigation regroupée,
accueil utile, formulaires aérés, aides repliables et tableaux simplifiés.

## Approche retenue

Améliorer Filament en place, en conservant ses composants, l’identité ambre,
les modes clair/sombre et les données existantes. Une maquette préalable a été
proposée ; l’utilisateur préfère l’implémentation directe. Une réécriture de
l’admin ou un assistant obligatoire de saisie ajouterait une complexité inutile.
Les détails suivants sont des choix de réalisation de l’agent.

- Navigation : accueil, groupe « Catalogue » (jeux, catalogues, séries,
  extensions), groupe « Référentiels » (langues, marchés), développement séparé.
- Accueil : remplacer les widgets génériques par un aperçu des vrais effectifs,
  des raccourcis vers les listes et un parcours de saisie expliqué.
- Langue : français pour le panneau et ses interactions Livewire, sans imposer
  cette langue à l’application publique ni modifier les réglages de l’utilisateur.
- Parcours : liens du catalogue vers ses séries/extensions, de la série vers
  ses extensions et retours vers les parents. La création depuis une liste
  filtrée préremplit le catalogue et la série seulement s’ils sont cohérents.
- Formulaires : champs usuels en premier ; clé et ordre dans des options
  avancées repliées ; explication de la fiche dans une aide repliable ; aides
  de champs courtes avec les précautions essentielles toujours visibles.
- Tableaux : noms lisibles et contexte regroupé, clés masquées par défaut mais
  accessibles ; recherche et filtres conservés ; suppression moins mise en avant.
- Présentation : largeur de lecture maîtrisée, contraste des textes secondaires,
  espace entre sections, actions de formulaire accessibles et adaptation mobile.

## Contraintes

Conserver les validations, autorisations administrateur vérifié, comportements
d’images, statuts et identités. Aucun nouveau modèle métier, migration, produit,
carte ou écran public. Aucun changement de données applicatives pour les essais.
Commandes PHP/Node dans Docker via Make ; tests exclusivement gamestackr_testing.
Les aides restent consultables au clavier ; aucune information essentielle ne
doit dépendre du survol. Les widgets refusent aussi les accès Livewire directs
aux comptes non autorisés. Les titres longs et la base vide restent utilisables.

## Validation

Tests des nouveaux liens, préremplissages, accès et langue ; suite existante pour
les mutations ; contrôle navigateur clair/sombre et petit écran pour la lecture,
les aides repliées, la validation et les actions. make check, make assets, revue
indépendante, CI de PR et CI après fusion selon AGENTS.md.
