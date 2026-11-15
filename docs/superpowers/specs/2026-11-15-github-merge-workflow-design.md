# Parcours GitHub et fusion des agents

Demande du 15 novembre 2026 : vérifier la branche de stockage avant GitHub,
préparer sa PR et ses tests, puis fournir un workflow de fusion pour les agents.
L’utilisateur a choisi explicitement : **fusion automatique après revue et CI verte**.

## Approche retenue

Conserver le workflow Docker/Make existant, ajouter les tests Garage réels et
formaliser la procédure dans `AGENTS.md` avec un modèle de PR. Une protection
GitHub de `main` impose PR, contrôle `ci` réussi, branche à jour et discussions
résolues, y compris pour l’administrateur ; push forcé et suppression interdits.

Une simple consigne documentaire laisserait les contrôles contournables ; une
approbation GitHub obligatoire par un second compte bloquerait un agent qui
utilise le compte de l’auteur. La revue indépendante est donc réalisée par un
agent distinct et tracée dans la PR avec le SHA revu ; aucun quota d’approbation
GitHub n’est imposé. Cette séparation est explicite : GitHub vérifie la CI et
les protections, les agents respectent la procédure de revue.

L’agent attend la CI, vérifie que le SHA est toujours celui relu, puis effectue
une fusion squash avec `--match-head-commit`. Il ne programme pas une fusion
future qui pourrait accepter de nouveaux commits non relus. Toute correction
ultérieure nécessite une revue du delta et de nouveaux contrôles applicables.
Une consigne utilisateur plus restrictive reste prioritaire.

## Périmètre et vérification

- Même branche `codex/local-image-storage`, installation Docker existante.
- Conserver le nom stable `ci` du contrôle GitHub et ses permissions en lecture.
- CI : installation propre et build, `make check`, `make test-storage`.
- Ne publier ni `.env`, ni accès Garage, ni données locales.
- Ajouter un délai maximal et annuler les anciens runs d’une même PR.
- Relancer les contrôles pertinents localement, faire une revue indépendante,
  publier la branche et la PR, attendre la CI distante, appliquer et relire la
  protection GitHub avant fusion. Puis vérifier le run de `main`.
- Pas de déploiement, pas de merge par un workflow ayant accès en écriture,
  pas de changement d’authentification ni de suppression des volumes.

## Références

- [Protection des branches](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches)
- [Fusion avec le CLI](https://cli.github.com/manual/gh_pr_merge)
