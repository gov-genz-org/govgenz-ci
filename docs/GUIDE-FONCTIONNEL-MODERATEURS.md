# Guide fonctionnel de modération — Pages, Positions et Projets

Ce document explique comment modérer les contenus éditoriaux dans le back-office GovGenZ. Il s’adresse aux personnes qui créent, relisent, traduisent et publient les **Pages**, **Positions** et **Projets**.

Il ne couvre pas les candidatures volontaires, les propositions de financement, les comptes équipe ni le déploiement technique.

## Accès

URL du back-office : `/admin/login`

Modules concernés :

- **Contenu → Pages**
- **Positions → Positions**
- **Projets → Projets**

## Principes communs

Les trois modules suivent le même cycle de modération :

1. créer ou modifier une fiche ;
2. garder la fiche en **Brouillon** pendant la rédaction ;
3. relire le contenu ;
4. vérifier l’aperçu ;
5. compléter la traduction si nécessaire ;
6. passer la fiche en **Publié** quand elle est validée ;
7. ouvrir la fiche publique pour contrôler le rendu final.

## Statuts de publication

### Brouillon

Le contenu n’est pas visible sur le site public.

À utiliser pour :

- une création en cours ;
- une traduction incomplète ;
- une fiche à relire ;
- une correction non validée ;
- une fiche publiée qu’il faut retirer temporairement.

### Publié

Le contenu est visible sur le site public.

À utiliser uniquement quand :

- le contenu est relu ;
- les liens fonctionnent ;
- les images sont correctes ;
- la traduction attendue est prête ;
- l’aperçu est validé.

## Langues et traductions

Les contenus peuvent exister en français et en anglais. Chaque langue correspond à une fiche séparée, reliée par un groupe de traduction.

### Créer une traduction

Depuis la liste :

1. repérer la fiche source ;
2. cliquer sur **Dupliquer trad** ;
3. ouvrir le brouillon créé ;
4. traduire le titre, le résumé, le contenu et les métadonnées ;
5. vérifier l’aperçu ;
6. publier uniquement quand la traduction est complète.

### Modifier une traduction existante

En édition, utiliser les boutons de navigation entre versions FR/EN pour ouvrir l’autre langue liée. Ne pas recréer une traduction si elle existe déjà.

### Bonnes pratiques

- Garder le même sens éditorial entre FR et EN.
- Ne pas publier une traduction vide ou partielle.
- Vérifier les slugs dans chaque langue.
- Si seule une langue est prête, publier uniquement cette langue.

## Slug

Le slug est la partie de l’URL qui identifie la fiche.

Exemple : pour `/projects/sante-maternelle`, le slug est `sante-maternelle`.

Règles fonctionnelles :

- utiliser des minuscules ;
- éviter les accents ;
- séparer les mots par des tirets ;
- garder un slug stable une fois la fiche publiée ;
- ne pas changer un slug publié sans vérifier les liens existants.

## Aperçus

L’aperçu sert à relire le rendu public avant publication.

Selon le module, deux usages peuvent être disponibles :

- **Aperçu sans enregistrer** : montre le contenu actuellement saisi dans le formulaire.
- **Aperçu enregistré** : montre la dernière version sauvegardée.

Workflow recommandé :

1. rédiger ou modifier ;
2. lancer l’aperçu sans enregistrer ;
3. corriger si nécessaire ;
4. enregistrer ;
5. vérifier la version enregistrée ;
6. publier ;
7. ouvrir la fiche publique.

## Pages

Menu : **Contenu → Pages**

Les pages servent aux contenus fixes du site :

- accueil ;
- présentation ;
- contact ;
- pages institutionnelles ;
- pages d’information.

### Champs à vérifier

- **Langue** : français ou anglais.
- **Titre** : titre public de la page.
- **Slug** : URL de la page.
- **Statut** : brouillon ou publié.
- **Contenu** : texte principal.
- **Métadonnées** : titre SEO et description si disponibles.

### Workflow de modération

1. Créer ou ouvrir la page.
2. Vérifier la langue.
3. Rédiger ou corriger le titre.
4. Vérifier le slug.
5. Relire le contenu.
6. Tester les liens internes et externes.
7. Vérifier l’aperçu.
8. Enregistrer.
9. Passer en **Publié** si la page est validée.

### Points de vigilance

- Une page publiée peut être liée dans le menu.
- Un changement de slug peut casser un lien du menu ou un lien partagé.
- Une page importante doit être relue dans les deux langues si elle existe en FR et EN.

### Blocs de pages

Pour éviter le HTML, les nouvelles pages peuvent être construites en blocs :

- **Texte** : titre, chapô, paragraphes, puces et source.
- **Cartes** : contenus type « Qui sommes-nous », ADN, secteurs ou cartes simples.
- **Chiffres** : statistiques type « Étude jeunesse ».
- **Organisation** : bloc central + fonctions / équipes.
- **Contacts** : grille de contacts avec lien ou e-mail.
- **Appel à action** : texte court + boutons.
- **Mentions / texte long** : sections longues avec titres, paragraphes et puces.
- **Grille secteurs** : reprise automatique des secteurs configurés.
- **Sources** : notes ou références.

Le bloc HTML reste un secours avancé : il ne doit pas être utilisé pour une page modérée au quotidien.

Pour choisir rapidement le bon bloc selon le rendu attendu, voir aussi : `docs/AIDE-BLOCS-PAGES.md`.

## Positions

Menu : **Positions → Positions**

Les positions sont des prises de position publiques. Elles peuvent être classées par type et par secteur.

### Types de position

- **Alerte**
- **Félicitation**
- **Analyse**
- **Solution**

### Champs à vérifier

- **Langue** : français ou anglais.
- **Titre** : titre public de la position.
- **Slug** : URL de la position.
- **Résumé / introduction** : texte court affiché en liste ou en haut de fiche.
- **Types** : nature de la position.
- **Secteurs** : thématiques concernées.
- **Temps de lecture** : si renseigné.
- **Statut de publication** : brouillon ou publié.
- **Blocs de contenu** : corps éditorial structuré.
- **Métadonnées** : titre SEO et description si disponibles.

### Workflow de modération

1. Créer ou ouvrir la position.
2. Vérifier le type de position.
3. Sélectionner les secteurs concernés.
4. Relire le résumé.
5. Relire les blocs de contenu.
6. Vérifier les sources si un bloc sources est utilisé.
7. Utiliser l’aperçu.
8. Enregistrer.
9. Publier uniquement après validation éditoriale.

### Points de vigilance

- Le type doit correspondre au ton réel du contenu.
- Les secteurs servent aux filtres publics : éviter les secteurs trop larges si la position est spécifique.
- Les sources doivent être utiles et lisibles.
- Une position sensible doit rester en brouillon tant que la validation politique ou éditoriale n’est pas terminée.

## Projets

Menu : **Projets → Projets**

Les projets présentent les initiatives du programme. Ils peuvent contenir des informations de statut, secteurs, budget, géographie et blocs détaillés.

### Statut métier

Le statut métier décrit l’état du projet. Il est différent du statut de publication.

- **Candidat** : projet proposé ou en préparation.
- **En validation** : projet en cours d’étude.
- **Actif** : projet lancé ou ouvert.
- **Complété** : projet terminé.

Le statut métier peut être visible ou utilisé sur le site. Le statut **Publié** décide si la fiche est accessible publiquement.

### Champs à vérifier

- **Langue** : français ou anglais.
- **Titre** : titre public du projet.
- **Slug** : URL du projet.
- **Résumé** : texte court visible en liste et sur la fiche.
- **Statut métier** : candidat, en validation, actif ou complété.
- **Statut de publication** : brouillon ou publié.
- **Secteurs** : thématiques du projet.
- **Nombre de volontaires** : si affiché.
- **Budget** : montant, échelle et affichage.
- **Géographie** : zone concernée si renseignée.
- **Date de lancement / durée / progression** : si utilisées.
- **Blocs de contenu** : détail éditorial du projet.
- **Métadonnées** : titre SEO et description si disponibles.

### Workflow de modération

1. Créer ou ouvrir le projet.
2. Vérifier la langue.
3. Vérifier titre, slug et résumé.
4. Choisir le statut métier correct.
5. Sélectionner les secteurs.
6. Vérifier les informations budget et géographie.
7. Relire les blocs.
8. Vérifier la cohérence avec la traduction liée.
9. Utiliser l’aperçu.
10. Enregistrer.
11. Publier uniquement quand la fiche est complète.

### Points de vigilance

- Ne pas confondre **Actif** et **Publié** : un projet peut être actif côté métier mais rester en brouillon.
- Si le projet reçoit des financements, vérifier que budget et besoins matériels sont compréhensibles.
- Le résumé doit rester court : il sert aussi aux listes et cartes.
- Les secteurs doivent correspondre aux filtres publics.
- Une fiche projet publiée doit être lisible sans contexte interne.

## Éditeur de blocs

Les **Pages**, **Positions** et **Projets** utilisent un éditeur de blocs pour construire le corps de page.

### Actions disponibles

- ajouter un bloc ;
- modifier un bloc ;
- supprimer un bloc ;
- déplacer un bloc avec la poignée de déplacement ;
- ajouter ou retirer des lignes dans les blocs qui le permettent ;
- prévisualiser le rendu.

### Blocs communs

Ces blocs peuvent être utilisés dans les positions et les projets :

- section texte ;
- note / encadré ;
- sources ;
- HTML avancé si le rôle le permet.

### Blocs orientés projet

Ces blocs sont surtout prévus pour les projets :

- budget détaillé ;
- besoins matériels ;
- calendrier ;
- indicateurs ;
- suivi d’impact ;
- équipe ;
- sources.

### Bonnes pratiques

- Utiliser un bloc texte pour les sections éditoriales classiques.
- Utiliser le bloc sources pour les liens de référence.
- Éviter le HTML avancé sauf besoin précis.
- Garder un ordre logique : contexte, problème, solution, impact, sources.
- Prévisualiser après un déplacement de bloc.

## Checklist avant publication

Avant de passer une fiche en **Publié**, vérifier :

- la bonne langue ;
- le titre ;
- le slug ;
- le résumé ou l’introduction ;
- le statut de publication ;
- le statut métier pour les projets ;
- les types pour les positions ;
- les secteurs ;
- les blocs de contenu ;
- les liens ;
- les images ou médias ;
- les sources ;
- les métadonnées SEO ;
- l’aperçu ;
- la traduction liée si elle existe.

## Checklist après publication

Après publication :

1. ouvrir la fiche publique ;
2. vérifier le rendu desktop ;
3. vérifier le rendu mobile si la fiche est importante ;
4. tester les liens ;
5. vérifier la langue publique ;
6. contrôler que la fiche apparaît correctement dans les listes.

## Dépublication

Pour retirer une fiche du site public, passer son statut en **Brouillon**.

À faire avant dépublication :

- vérifier si la fiche est liée dans le menu ;
- vérifier si elle est référencée depuis une autre page ;
- vérifier s’il existe une traduction liée ;
- prévenir l’équipe si la fiche était déjà partagée publiquement.

## Suppression

La suppression doit rester exceptionnelle.

Préférer **Brouillon** quand :

- le contenu doit être caché temporairement ;
- une correction est en cours ;
- la publication est en attente de validation ;
- la fiche peut être utile plus tard.

Supprimer uniquement si :

- la fiche est un test ;
- la fiche est un doublon ;
- la suppression a été validée ;
- aucune traduction utile n’est liée.

## Règles de modération éditoriale

- Publier seulement ce qui est relu et validé.
- Garder les brouillons tant qu’un doute existe.
- Ne pas modifier un slug publié sans vérifier l’impact.
- Ne pas publier une traduction incomplète.
- Prévisualiser avant et après enregistrement.
- Vérifier le rendu public après publication.
- Demander validation pour les contenus sensibles.
