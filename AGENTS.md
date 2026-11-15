# Consignes pour les agents

## Projet et environnement

GamestackrTCG utilise Laravel, Filament, React/TypeScript, PostgreSQL et Garage.
Les commandes PHP, Composer et Node s’exécutent dans Docker via le `Makefile`.
Lire le `README.md` avant de modifier l’environnement.

- Travailler sur une branche dédiée, préfixée `codex/` par défaut. Réutiliser
  la branche de la tâche lorsqu’elle existe ; ne pas développer sur `main`.
- Examiner `git status` avant de modifier quoi que ce soit et préserver les
  changements de l’utilisateur.
- Ne jamais versionner `.env`, `storage/app/private/garage/`, les données,
  les identifiants ou les dépendances installées. Les lockfiles sont versionnés.
- Les tests utilisent exclusivement `gamestackr_testing`. Ne jamais désactiver
  les garde-fous de `tests/TestCase.php` ou utiliser la base applicative en test.
- Ne pas effacer les volumes Docker pour résoudre un problème de démarrage.

## Validation avant PR

1. Examiner le diff complet par rapport à `origin/main`, y compris les fichiers
   non suivis. Ajouter les tests utiles aux changements de comportement.
2. Exécuter `make check` pour PHP/Pint, PHPStan, les tests, le lint frontend et
   TypeScript. Exécuter `make assets` pour vérifier un changement frontend,
   de dépendances ou de configuration de build.
3. Pour un changement de stockage, d’images, de Docker ou du workflow CI,
   exécuter aussi `make test-storage`, après `make up`. Cette suite utilise
   Garage réel et nettoie uniquement ses propres objets de test.
4. Vérifier un parcours navigateur si le comportement visible change.
5. Vérifier `git diff --check` et les fichiers réellement ajoutés à l’index.
   Ne pas présenter un contrôle non exécuté comme réussi. Documenter toute
   limite ; ne pas neutraliser un test pour obtenir une CI verte.

## Revue indépendante

Avant toute fusion, un autre agent ou un reviewer humain relit le changement
complet par rapport à la base de la PR. Le reviewer reçoit les exigences,
la plage de commits et les résultats utiles, sans se substituer aux tests.

- Corriger les défauts importants avant de poursuivre.
- Tracer dans la description de PR le SHA revu, le type de reviewer et sa
  conclusion. Il s’agit d’une revue réelle, pas d’une auto-approbation GitHub.
- Si un commit est ajouté ensuite, faire relire le delta, mettre à jour le SHA
  et relancer les contrôles applicables ; la CI distante doit être verte sur
  la nouvelle version.
- Si aucun reviewer indépendant n’est disponible, laisser la PR ouverte et
  expliquer ce qui manque. Ne pas inventer une revue ni fusionner quand même.

## Publication et fusion vers main

Le propriétaire a autorisé les agents à publier la branche, créer sa PR puis
fusionner automatiquement après revue indépendante et CI verte. Une consigne
utilisateur plus restrictive (brouillon, pas de push, pas de merge) prévaut.

1. Actualiser `origin/main`. Si la branche est en retard, y intégrer `origin/main`
   sans réécrire l’historique distant, résoudre les conflits et revérifier.
2. Pousser uniquement la branche de travail et ouvrir une PR vers `main` avec
   le modèle fourni. Expliquer le problème, le résultat et les vérifications.
3. Attendre le workflow `tests`, job **`ci`**, sur la version actuelle de la PR.
   Il exécute `make init` (installation propre et build), `make check` puis
   `make test-storage`. Un résultat absent, en cours, annulé, ignoré ou en échec
   ne vaut pas un succès.
4. Vérifier que la PR n’est pas un brouillon, que le SHA de tête correspond au
   SHA revu, qu’elle est à jour avec `main`, que les discussions sont résolues
   et qu’aucune demande de changement ne reste ouverte.
5. Fusionner en **squash**, en liant l’opération au SHA vérifié :

    ```sh
    gh pr merge <numero> --squash --match-head-commit <sha-revu>
    ```

    Ne pas utiliser `--admin`, `--force`, un push direct sur `main`, ni désactiver
    les protections. Ne pas armer `--auto` avant la vérification finale : un
    nouveau commit doit être revu avant toute fusion.

6. Vérifier l’état `MERGED` et le commit résultant, puis attendre le workflow
   `tests` de ce commit sur `main`. En cas d’échec, le signaler et préparer une
   correction par PR ; ne pas réécrire `main`.
7. Synchroniser le checkout uniquement s’il est propre et fournir à
   l’utilisateur les liens vers la PR et les contrôles. Conserver une branche
   ou un worktree contenant encore du travail non sauvegardé.

## Protection GitHub attendue

`main` exige une PR, le check `ci` de l’application GitHub Actions, une branche
à jour et les conversations résolues. La protection s’applique aussi aux
administrateurs et interdit les push forcés et la suppression de la branche.

Le nombre d’approbations GitHub obligatoires est zéro : les agents utilisent
le compte de l’auteur, qui ne peut pas approuver sa propre PR. La revue
indépendante ci-dessus reste obligatoire pour les agents ; GitHub ne peut pas
vérifier la qualité d’une revue déclarée dans la description. Ne pas prétendre
que la protection technique impose un second reviewer GitHub.

Ne pas modifier la protection pour débloquer une fusion. Si elle diffère de
ces règles, diagnostiquer l’écart et respecter toute règle plus stricte décidée
par le propriétaire.
