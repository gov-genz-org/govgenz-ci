# Aide rapide — Blocs Pages CMS

Ce document aide les modérateurs à choisir le bon bloc dans l’éditeur Pages, avec le rendu attendu côté public.

## Principe

- Le **hero** de page porte le titre principal et le chapô.
- Les blocs servent à structurer le **corps** de page.
- En cas de doute, préférer les blocs métier plutôt que `HTML libre`.
- Les grilles **secteurs** et **structures** affichent le contenu de la BDD (tables `sectors`, `structure_units`) : les textes des tuiles / départements ne se éditent pas dans le bloc.

## Page programme Déclaration (`slug` : `declaration`)

Trois endroits distincts :

| Zone | Où éditer | Contenu |
|------|-----------|---------|
| Bandeau + stats | Page CMS `declaration` (hero ; stats calculées côté site) | Titre, chapô |
| Listes de cartes | **Déclaration — cartes** (`/admin/declaration-items`) | Déclarations, plaidoyers, partenariats + fiches détail en blocs |
| Corps statique entre / sous les listes | Page CMS `declaration`, mode **Blocs** | Grilles, ADN, éthique, CTA |

**Ordre public fixe** sur `/declaration` :

1. Hero + statistiques + liste « Déclarations » (BDD)
2. Blocs CMS **avant** la coupure (grilles structures/secteurs, ADN, etc.)
3. Liste « Partenariats » (BDD)
4. Blocs CMS **après** la coupure (à partir du premier bloc **Mentions / texte long** ou **Appel à action**)

Les badges « Entre les listes » / « Sous partenariats » dans l’éditeur Pages suivent cette règle en direct.

**Ne pas utiliser** la variante de cartes `declaration_cards` sur cette page : les cartes de liste viennent de la BDD.

## Page Structure (`slug` : `structure`)

| Zone | Où éditer | Contenu |
|------|-----------|---------|
| Bandeau | Hero de page (facultatif) | Titre, chapô, image |
| Organigramme | **Un** bloc `structures_grid` | Présentation **Hub** (noyau + fonctions) |
| Données hub / cartes | **Structures** (`/admin/structures`) | Rôle **Noyau** (1 entrée), **Fonction** (cartes) |

- URL publique : `/structure` (FR), `/en/structure` (EN).
- Ne pas utiliser le layout **Cartes départements** sur cette page (réservé à la page Déclaration).
- Mode HTML legacy : marqueur `<!-- GG_CMS_STRUCTURES_HUB -->` ou `data-gg-cms="structures-hub"` (voir **Aide → Blocs HTML**).

## Blocs et rendu public

### 1) Texte (`section_text`)

- **Usage** : paragraphe éditorial, intro de section (kicker / titre / chapô en 3 paragraphes).
- **Champs clés** : paragraphes, puces, source.
- **Rendu** : section texte simple.

### 2) Cartes (`cards_grid`)

Le rendu dépend de la **variante** :

- **Cartes simples (`simple_cards`)** — cartes génériques.
- **Cartes cercles (`circle_cards`)** — style démographie / chiffres.
- **Cartes piliers ADN (`pillar_cards`)** — page Déclaration : 4 piliers (sur-titre, titre, puces).
- **Tuiles (`tile_grid`)** — entrées courtes + lien (préférer `sectors_grid` pour les secteurs BDD).
- **Cartes déclaration (`declaration_cards`)** — **obsolète** sur la page programme ; utiliser **Déclaration — cartes**.

### 3) Chiffres (`stats_grid`)

- **Usage** : statistiques clés + boutons d’action.
- **Page Déclaration** : les stats du bandeau sont en général **automatiques** ; éviter un doublon `stats_grid` dans le corps CMS.

### 4) Grille structures (`structures_grid`)

- **Usage** : organigramme depuis la table **Structures**.
- **Champs** : intro (kicker, titre, chapô), bandeau teal optionnel, `layout` :
  - `dept` — 7 départements (page Déclaration),
  - `hub` — noyau central + cartes fonctions.
- **Remplace** l’ancien bloc `organization_hub` (encore rendu en lecture seule si présent dans d’anciennes pages).

### 5) Grille secteurs (`sectors_grid`)

- **Usage** : équipes sectorielles depuis la table **Secteurs**.
- **Champs** : intro, bandeau teal, `layout` :
  - `compact` — grille Déclaration,
  - `wide` — 7 colonnes (style page `/secteurs`).

### 6) Contacts (`contact_grid`)

- **Usage** : e-mails / points de contact par thème.

### 7) Appel à action (`cta_panel`)

- **Usage** : message court + boutons.
- **Page Déclaration** : en fin de corps (zone « sous partenariats ») déclenche la coupure avec `legal_prose`.

### 8) Mentions / texte long (`legal_prose`)

- **Présentation** :
  - `prose` — texte continu (mentions, CGU),
  - `accordion` — engagements éthiques (page Déclaration).
- **Champs** : sections (titre, corps, puces).

### 9) Sources (`sources`)

- **Usage** : références en bas de page ou de fiche (fiches détail : blocs dédiés côté **Déclaration — cartes**).

### 10) Colonnes pied de page (`footer_columns`)

- **Usage** : page réservée `site-footer` uniquement.

### 11) HTML libre (`html`)

- **Usage** : cas avancé uniquement ; éviter au quotidien.

## Fiches détail Déclaration (hors Page Builder)

Les cartes de liste ont un **corps détaillé en blocs** (comme les positions) : `section_rich`, `note_panel`, `sources`, etc. — édité dans **Déclaration — cartes**, pas dans Pages CMS.

## Médias dans les cartes cercles

- `Médias` sélectionne un média depuis la médiathèque.
- `Retirer` enlève seulement la **référence dans la carte**.
- Supprimer un média de la carte **ne supprime pas** le fichier de la médiathèque.

## Guide visuel dans le back-office

**Aide → Blocs Pages (aide)** (`/admin/cms-guide-blocks`) : exemple JSON + aperçu pour chaque type, encarts **Déclaration** et **Structure**, grilles BDD avec la charte du site.

## Raccourci de validation avant publication

- Variante de cartes = rendu attendu.
- Liens et e-mails valides.
- Grilles secteurs/structures : données à jour dans **Secteurs** / **Structures**.
- Page `declaration` : ordre des blocs et zone avant/après partenariats.
- Page `structure` : un bloc `structures_grid` en layout **hub**, données dans **Structures**.
- Aperçu mobile/desktop.
