# Vérification du catalogue public — 15 novembre 2026

La version A validée utilise les données administrées sur toutes les pages de
jeu. La topbar existante, les composants partagés et les primitives shadcn sont
réutilisés. L’interface publique FR/EN reste indépendante des éditions. Le
prototype a été retiré ; les futures cartes ont un état d’attente explicite.

## Contrôles exécutés

| Contrôle            | Résultat final                                                                                                                             |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| `make check`        | Succès : Pint, PHPStan, 282 tests PHP / 2 299 assertions, formatage et lint frontend, 4 tests frontend, génération Wayfinder et TypeScript |
| `make assets`       | Compilation de production réussie                                                                                                          |
| `make test-storage` | 3 tests / 27 assertions réussis sur Garage réel                                                                                            |
| `git diff --check`  | Aucun problème d’espacement                                                                                                                |
| Index Git           | Vide ; aucun fichier ajouté à l’index                                                                                                      |

Les commandes PHP et Node ont été exécutées dans Docker. Les tests PHP
utilisent exclusivement `gamestackr_testing`. Les tests Garage nettoient leurs
propres objets. Aucune donnée applicative ni visibilité de publication n’a été
modifiée par l’agent.

Les tests ciblés couvrent notamment la visibilité héritée, les clés identiques
dans plusieurs catalogues, les traductions et images absentes, les sorties et
leur tri, les recherches accentuées, les paramètres malformés et la pagination.
Les tests frontend couvrent l’annulation des recherches différées lors des
changements de langue/navigation et la conservation des filtres successifs.

## Parcours navigateur

Vérifications sur ordinateur et à 390 × 844 : liste et fiche avec données
réelles, recherche, langue d’édition, recherche sans résultat et réinitialisation,
passage FR/EN conservant l’édition choisie, changement d’édition conservant la
langue du site, accès mobile à la connexion. Aucun débordement horizontal
constaté sur les vues mobiles vérifiées. Le réglage temporaire de taille du
navigateur a été réinitialisé.

La liste et la fiche présentent les logos effectivement renseignés dans l’admin.
Une extension sans édition publique reste informative. La langue vide dans
l’URL affiche correctement « Toutes les langues ».

## Revue indépendante

L’agent `review_public_catalog` a relu l’ensemble des fichiers modifiés et non
suivis par rapport à `69df2149e487fb271bdc49b5782281d21e7adad0`, puis les
corrections. Conclusion finale : aucun défaut critique, important ou mineur
restant. Le reviewer n’a exécuté ni tests ni modifications ; cette revue ne
remplace pas les contrôles ci-dessus.

Corrections issues de la revue : annulation des requêtes de filtre obsolètes,
codes de langue conformes au domaine, message lorsque l’édition demandée est
indisponible sans édition de repli, sentinelles de filtre sans collision avec
les clés administrables. Une suspicion sur les langues vides a été écartée par
un test HTTP confirmant le comportement des middlewares Laravel existants.

## Portée et conservation

La traduction couvre le parcours public. Les pages compte/authentification et
l’administration gardent leur contenu existant. Les textes éditoriaux saisis
dans l’admin ne sont pas traduits automatiquement. Les cartes restent à venir.

La livraison initiale a été conservée non commitée sur
`codex/public-catalog-prototypes`, sans push, PR ni fusion. L’utilisateur a ensuite
demandé la publication et la fusion selon `AGENTS.md` ; la branche porte désormais
le nom `codex/public-multilingual-catalog`. Le SHA relu et les résultats distants
seront tracés dans la description de PR, après la revue du commit final.

Après la livraison initiale, l’alignement de la recherche avec les trois filtres
a été corrigé dans le composant partagé `FilterSelect` : espacement avec `gap`
et hauteur uniforme de 44 pixels. Les coordonnées des quatre champs ont été
vérifiées dans le navigateur ; le lint frontend et la compilation passent.
