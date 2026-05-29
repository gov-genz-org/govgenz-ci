# Aide rapide — Blocs Pages CMS

Ce document aide les modérateurs à choisir le bon bloc dans l’éditeur Pages, avec le rendu attendu côté public.

## Principe

- Le **hero** de page porte le titre principal et le chapô.
- Les blocs servent à structurer le **corps** de page.
- En cas de doute, préférer les blocs métier plutôt que `HTML libre`.

## Blocs et rendu public

### 1) Texte (`section_text`)

- **Usage** : paragraphe éditorial, intro de section, liste à puces.
- **Champs clés** : paragraphes, puces, source.
- **Rendu** : section texte simple avec paragraphes et liste.

### 2) Cartes (`cards_grid`)

Le rendu dépend de la **variante** :

- **Cartes simples (`simple_cards`)**
  - Usage : cartes générales (titre, texte, lien).
  - Rendu : grille de cartes génériques.
- **Cartes cercles (`circle_cards`)**
  - Usage : style "Qui sommes-nous" (valeur, unité, titre, sous-titre, médias).
  - Rendu : cartes circulaires avec chiffres.
- **Cartes piliers ADN (`pillar_cards`)**
  - Usage : style "Notre ADN" (sur-titre, numéro/valeur, titre, puces).
  - Rendu : cartes ADN (`adn-card`) en grille.
- **Tuiles (`tile_grid`)**
  - Usage : entrées courtes + lien.
  - Rendu : tuiles cliquables.

### 3) Chiffres (`stats_grid`)

- **Usage** : statistiques clés + éventuels boutons d’action.
- **Champs clés** : stats (valeur, suffixe, label), actions.
- **Rendu** : bloc de statistiques type "Étude jeunesse".

### 4) Organisation (`organization_hub`)

- **Usage** : noyau central + fonctions/équipes.
- **Champs clés** : bloc central (`core_*`), items (nom, sous-titre, lien).
- **Rendu** : hub structurel avec cartes de fonctions.

### 5) Contacts (`contact_grid`)

- **Usage** : e-mails/points de contact par thème.
- **Champs clés** : label, titre, sous-texte, lien.
- **Rendu** : grille de blocs contact.

### 6) Appel à action (`cta_panel`)

- **Usage** : message court + boutons.
- **Champs clés** : texte, actions.
- **Rendu** : bandeau CTA.

### 7) Mentions / texte long (`legal_prose`)

- **Usage** : pages longues, mentions, politique.
- **Champs clés** : sections, paragraphes, puces.
- **Rendu** : prose structurée par sections.

### 8) Sources (`sources`)

- **Usage** : notes de bas de page, références.
- **Champs clés** : lignes de sources.
- **Rendu** : liste de sources.

### 9) Grille secteurs (`sectors_grid`)

- **Usage** : afficher automatiquement les secteurs depuis la base.
- **Rendu** : grille dynamique des secteurs.

### 10) Colonnes pied de page (`footer_columns`)

- **Usage** : page réservée `site-footer` (colonnes sous le logo).
- **Champs clés** : colonnes (titre), liens (libellé, URL), case « À venir » pour les entrées sans lien.
- **Rendu** : même présentation que le pied de page du site (grille de colonnes). Ne pas envelopper dans un conteneur colonnes : le gabarit le fait déjà.

### 11) HTML libre (`html`)

- **Usage** : cas avancé uniquement.
- **Rendu** : HTML injecté tel quel.
- **Attention** : éviter pour la modération quotidienne.

## Médias dans les cartes cercles

- `Médias` sélectionne un média depuis la médiathèque.
- `Retirer` enlève seulement la **référence dans la carte**.
- Supprimer un média de la carte **ne supprime pas** le fichier de la médiathèque.

## Guide visuel dans le back-office

Dans l’admin : **Aide → Blocs Pages (aide)** (`/admin/cms-guide-blocks`).

Chaque type de bloc y est illustré avec un exemple JSON et un aperçu du rendu public (même charte que le site).

## Raccourci de validation avant publication

- Vérifier que la variante de cartes correspond au rendu attendu.
- Vérifier les liens et e-mails.
- Vérifier les images (pas de 404).
- Vérifier le rendu mobile/desktop.
- Vérifier l’aperçu avant publication (`/admin/cms-guide-blocks` ou aperçu de la page).
