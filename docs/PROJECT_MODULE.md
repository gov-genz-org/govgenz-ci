# Module « Projects » (Gov Gen Z)

Référence contenu statique : `site_govgenz/projects-govgenz/` (liste, cartes, fiches HTML, statuts, secteurs, métadonnées).

## Conception : « penser CMS » + contenu dynamique

Un **projet** côté public = une **carte** (aperçu) + une **fiche détail** (contenu riche). Le back-office doit refléter cette même lecture : **les mêmes données que le visuel**, pas seulement un formulaire plat.

### 1. Trois familles de contenu (à ne pas mélanger sans règles)

| Famille | Exemples | Où ça vit | Édition |
|--------|-----------|-----------|---------|
| **Données structurées** | slug, titre court, statut métier, publication, secteurs, volontaires (nombre), budget (texte), géo, dates, % avancement | `project_projects` (+ tables de liaison futures) | Champs formulaire, validations fortes, listes / filtres |
| **Contenu éditorial libre** | long texte, blocs HTML, timeline, citations, sections marketing | `body` (MVP) puis `project_pages` / blocs JSON / champs i18n | Comportement **type CMS** : TinyMCE, prévisualisation, brouillon / publié |
| **Dynamique calculé ou référencé** | libellés secteurs depuis `sectors`, compteurs futurs agrégés, « projets liés » | Services / requêtes, pas du HTML statique | Non éditable dans la fiche projet (source de vérité ailleurs) |

**Règle d’or :** ce qui apparaît sur **plusieurs** vues (liste + détail, ou site principal + projects) doit être **en base ou en service**, pas dupliqué en HTML dans le corps. Le corps CMS sert au **spécifique** de la fiche (récit, mise en page), pas aux champs carte.

### 2. Back-office aligné sur le visuel (objectif produit)

L’état actuel (un long formulaire) est un **MVP technique**. L’évolution recommandée :

1. **Liste admin** : mini **aperçu carte** (titre, statut, pill, extrait tronqué, secteurs badges) + actions — comme la grille publique, pour repérer un projet sans lire tout le formulaire.
2. **Édition** : disposition en **deux zones** (ou onglets) calquées sur le site :
   - **« Carte »** : champs visibles sur la liste (titre, extrait, statut, publication, secteurs, indicateurs carte : volontaires, budget affiché, géo, dates, %).
   - **« Détail »** : SEO (`meta_*`), corps riche (`body`), éventuellement médias / blocs structurés plus tard.
3. **Prévisualisation** (phase suivante) : lien « Voir comme publié » (brouillon réservé admin), même gabarit que le front `projects`.

Cela évite la dérive « on remplit des champs sans voir la carte » et rapproche l’expérience du **référentiel statique** `projects-govgenz`.

### 3. Front office (cohérent avec la conception)

- **Liste** : requête sur `project_projects` publiés + non supprimés ; cartes alimentées par les **champs carte** (pas le `body` entier).
- **Fiche** : slug ; `body` rendu dans le layout projects ; métadonnées SEO ; secteurs résolus via `sectors` (comme Join / page Secteurs).
- **Cache** : clé par locale + slug (ou par liste) ; invalidation à la publication / mise à jour.
- **i18n** : aujourd’hui une ligne par locale côté CMS pages ; pour les projets, prévoir soit **deux lignes** `project_projects` liées (`translation_group`), soit champs `title_fr` / `title_en` — à trancher avant d’empiler le contenu.

### 4. Schéma données : MVP vs cible

- **MVP** : une table `project_projects` + `sectors_csv` + `body` monolithique = rapide à livrer, limite claire (pas de révisions ni blocs par projet sans migration).
- **Cible CMS** : `project_pages` (ou `body_blocks` JSON par projet), révisions, `project_media` ou `cms_media_id`, table de liaison `project_project_sectors` (intégrité référentielle), champs traduits ou lignes par locale.

#### Découpe statique → données (règle de conception)

D’après les fichiers `site_govgenz/projects-govgenz/projects/*.html` :

| Zone page statique | Rôle | Où ça vit (dynamique) |
|---------------------|------|------------------------|
| `<nav>`, `<footer>`, fil d’Ariane | Coque site / module | Gabarit vue CodeIgniter + routes, pas dans le corps projet |
| `<div class="project-hero">` (tags, H1, méta, chapô) | Carte + entête fiche | Champs `title`, `excerpt`, `sectors_csv`, `project_status`, `volunteers_count`, `budget_display`, `geography`, `launched_at`, `duration_months` (+ rendu pills côté front) |
| Barre `<div class="progress-wrap">` sous le héros | Avancement global | `progress_percent` (+ libellés phase optionnels plus tard) |
| `<div class="project-main">` | Colonne narrative | **`body_content_mode`** : `html` (TinyMCE, `body`) ou **`blocks`** (`body_blocks` JSON) — blocs typés qui reproduisent les `<div class="content-section">` |
| Sections « problème / solution » (titre warm/teal + p + ul) | Bloc **`section_rich`** | JSON |
| « Budget détaillé » + `<table class="budget-table">` | **`budget_table`** | JSON |
| « Calendrier » + `.phases` / `.phase` | **`timeline`** | JSON |
| « Indicateurs d’impact » + `.kpis-grid` | **`kpi_grid`** | JSON |
| Encadré pointillé (suivi d’impact) | **`note_panel`** | JSON |
| « Équipe projet » + `.team-list` | **`team`** | JSON |
| « Sources & documents » + liste | **`sources`** | JSON |
| Cas non couverts | **`html`** (libre) | JSON (dernier recours) |
| `<div class="project-sidebar">` (widgets, CTA, devises) | Données + gabarit | Hors `body` : champs dédiés ou widgets calculés au rendu (évolution) |

Migration : `body_content_mode` (`html`|`blocks`), `body_blocks` (MEDIUMTEXT JSON). Rendu public : helper `project_render_main_body($project)` (applique aussi les embeds CMS type grille secteurs).

### 5. Synthèse

La conception « CMS + dynamique » = **champs structurés** pour tout ce qui structure la carte et le SEO + **zone éditoriale** pour le détail narratif + **référentiels** (`sectors`, futurs partenaires) hors HTML. Le back-office **doit** converger vers une UI **carte / détail** alignée sur `projects-govgenz` ; le MVP formulaire est une étape, pas l’état final.

## Décision d’architecture

- **Une seule application CodeIgniter** (`govgenz-ci`), **une base MySQL**, tables préfixées **`project_`**.
- Front public : routes existantes `Front\Projects\…` + contexte `SiteContext` (préfixe `/projects` ou sous-domaine, voir `.env`).
- Back-office : **`/admin/project-projects`** (rôle **éditeur ou admin** : `authadmin`), même session que le reste du CMS.
- Référentiel secteurs : **`/admin/sectors`** (libellés FR/EN, e-mails, ordre, actif ; codes stables après création).
- Évolution : pages CMS dédiées (`project_pages`), médias (`project_media` ou lien `cms_media`), API `/api/v1/project/…`, sans dupliquer la charte (réutiliser CSS/layout site principal).

## Tables (phase actuelle)

| Table | Rôle |
|-------|------|
| `sectors` | Référentiel global des secteurs (codes stables en minuscules : `legal`, `education`, …) — **Join**, page **Secteurs** du site principal, cases à cocher **Projets** (`sectors_csv`). Libellés FR/EN + e-mail de contact en base. |
| `project_projects` | Fiche projet : slug, titres, extrait, **corps** (`body` HTML ou `body_blocks` JSON selon `body_content_mode`), statut métier, publication, secteurs (CSV de codes `sectors.code`), métadonnées, indicateurs type carte (volontaires, budget texte, géo, dates). |

Index : `slug` unique (lignes non supprimées gérées en appli avec soft delete), filtres sur `project_status`, `publication_state`, `deleted_at`.

## Conventions

- **Soft delete** : `deleted_at` sur `project_projects`.
- **Timestamps** : `created_at`, `updated_at`.
- **Statut métier** (`project_status`) : `actif`, `candidat`, `validation`, `complete` (comme les pills du statique).
- **Publication** (`publication_state`) : `draft`, `published` (+ `published_at` quand publié).
- **Secteurs** : plusieurs codes (référence `sectors.code`) séparés par des virgules dans `sectors_csv` (MVP) ; normalisation possible vers table de liaison plus tard.

## Roadmap (petit à petit)

1. ~~Conception + schéma MVP~~ (ce document + migration + modèles).
2. **Back-office projets** (liste, création, édition) — MVP formulaire ; **suivant** : liste type cartes + formulaire onglet « Carte / Détail » + prévisualisation.
3. Front dynamique : liste / fiche depuis BDD, cache, SEO.
4. `project_pages` / révisions / brouillons si besoin au-delà d’un seul champ `body`.
5. `project_media` ou intégration bibliothèque `cms_media`.
6. Rôles fins (`project.publish`) si distinction éditeur / relecteur.
7. API versionnée pour mobile / SPA.
8. **Données de démo** : `database/seed_project_projects_from_static.sql` (6 projets extraits de `site_govgenz/projects-govgenz`). Régénérer avec `python3 database/build_seed_project_projects_static.py` (ou `php database/build_seed_project_projects_static.php` si PHP disponible). Conversion HTML → blocs : `php spark projects:migrate-body-blocks` (`--dry-run`, `--slug=…`, `--keep-body`, `--force`).

## Pièges à éviter

- Ne pas recopier tout le HTML statique en dur : importer ou saisir en base.
- Slugs uniques y compris avec soft delete (contrôler en `store` / `update`).
- Aligner les codes secteurs sur la table `sectors` pour les filtres front futurs.
