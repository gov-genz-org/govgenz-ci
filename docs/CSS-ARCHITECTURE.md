# Architecture CSS GovGenZ

Deux piles **indépendantes** (front public ≠ back-office). Source de vérité des couleurs et tailles : `public/assets/css/govgenz-tokens.css`.

## Front public (CodeIgniter)

Ordre de chargement (`app/Views/front/layout.php`) :

| # | Fichier | Rôle |
|---|---------|------|
| 1 | `govgenz-fonts.css` | Polices auto-hébergées |
| 2 | `govgenz-tokens.css` | `:root`, jetons `--ggz-*`, alias template |
| 3 | `govgenz-components.css` | Boutons, `.ggz-legal-prose`, cartes |
| 4 | `govgenz-template.css` | Maquette sections (`.section__*`, hero, footer) |
| 5 | **`govgenz-cms-shell.css`** | **CMS WYSIWYG** — `#main-content` + `.cms-guide-preview-host` |
| 6 | `govgenz-front-pages.css` | Layout `#main-content`, heroes, formulaires |
| 7 | `govgenz-bridge.css` | Shell body, nav active, skip-link, prose page |
| 8 | `ggz-legal-page.css` | Pages mentions / légal |
| 9 | `ggz-press-page.css` | Liste + détail presse |

Pages optionnelles : `join-enhancements.css`, `program-body-blocks.css`, `projects-program-*.css`, `positions-program-*.css`, `cookie-consent.css` via `extraHead`.

**Programme positions / projets :** `program-body-blocks.css` (blocs `content-section` / `content-title` du renderer) + feuille liste ou fiche dédiée. Tokens `--pp-*` = alias dans `govgenz-tokens.css` (pas de hex locaux).

**Règle :** ne pas recopier les styles `.section__header` / `.section__title` dans `admin-cms-guide-preview.css` — ils vivent dans `govgenz-cms-shell.css`.

**`.section__title` :** taille unique `--ggz-type-section-title` (`govgenz-template.css`). Les `h2` génériques du CMS (`govgenz-cms-shell.css`) et `article.wysiwyg h2` (`govgenz-front-pages.css`) **excluent** `.section__title` pour ne pas l’écraser.

Boutons template : scope `.ggz-public-theme .btn` (évite de casser Bootstrap en admin).

## Back-office

| # | Fichier | Rôle |
|---|---------|------|
| 1 | Bootstrap 5 (CDN) | UI admin |
| 2 | `govgenz-tokens.css` | Accent rouge partagé |
| 3 | `govgenz-admin.css` | Navbar, sidebar, tables, formulaires |

**Exception** — `admin/cms-guide` charge fonts → components → template → cms-shell → **`govgenz-cms-guide-front-scoped.css`** (dérivé de front-pages + bridge, **uniquement** sous `.cms-guide-preview-host`) → legal → press → `govgenz-guide-preview-parity.css` → `admin-cms-guide-preview.css`. **Ne pas** y inclure `govgenz-front-pages.css` ni `govgenz-bridge.css` en global (sinon `.btn` / `#main-content` altèrent la navbar admin). Régénérer le scoped : `python3 scripts/scope-cms-guide-css.py`.

## Site statique `site_govgenz/`

`site_govgenz/css/style.css` reste une **copie autonome** (HTML sans `#main-content`). Pour aligner une règle (ex. `.section__title`), modifier d’abord `govgenz-tokens.css` / `govgenz-template.css`, puis reporter si besoin dans `site_govgenz/css/style.css`.

## Fichiers dépréciés

- `ggz-project-cta-buttons.css` → `govgenz-components.css`
