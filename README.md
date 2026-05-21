# GovGenZ — application CodeIgniter 4

Ce dossier contient **uniquement** le projet CodeIgniter 4 (code PHP, `public/`, `vendor/`, etc.).

## Développement local avec Docker

L’image Apache / PHP / MySQL et les scripts SQL MVP sont dans **`../govgenz-local/`** :

```bash
cd ../govgenz-local
docker compose up -d --build
```

Puis, dans **`govgenz-ci/`** : copier **`env.ci.example`** → **`.env`** et renseigner **`encryption.key`** (voir `govgenz-local/README.md` pour fusionner le fragment Docker si besoin).

- Site : http://localhost:8082  
- Routes : `cd ../govgenz-local && docker compose exec web php spark routes`

## Sans Docker

Prérequis : **PHP 8.1+**, [**Composer**](https://getcomposer.org/).

```bash
composer install
php spark serve
```

Configurer **`.env`** (base MySQL ou SQLite selon ton environnement).

## État MVP

- Routes **front** : pages CMS (`/qui-sommes-nous`, …), `/contact`, `/press`, `/join`, programmes **`/projects`** et **`/positions`** (préfixe chemin ou sous-domaine selon `.env`)
- Routes **admin** : `/admin`, CMS, menu du site, projets programme, positions, presse, volontaires
- **CSS front** : pile documentée dans **[docs/CSS-ARCHITECTURE.md](docs/CSS-ARCHITECTURE.md)** — jetons `govgenz-tokens.css`, composants `govgenz-components.css` (boutons `--red` / `--teal` / `--ghost`, tags, cartes), template + shell CMS + pages + bridge ; feuilles **optionnelles** par page via `extraHead` (`projects-program-*.css`, `positions-program-*.css`, `program-body-blocks.css`, etc.)

## CI/CD

Pipeline GitHub Actions (tests, build, déploiement FTP staging/prod) et configuration des rulesets : **[docs/CI-CD.md](docs/CI-CD.md)**.

## Documentation

| Document | Rôle |
|----------|------|
| [CI-CD.md](docs/CI-CD.md) | Branches, rulesets, secrets FTP, workflow |
| [CSS-ARCHITECTURE.md](docs/CSS-ARCHITECTURE.md) | **Ordre de chargement CSS**, front vs admin, programmes projets/positions, règles `.section__title` |
| [GOVGENZ.md](../docs/GOVGENZ.md) | Vision, MVP, architecture |
| [GOVGENZ-CONCEPTION.md](../docs/GOVGENZ-CONCEPTION.md) | Modèle de données, routes |
| [GOVGENZ-DESIGN-TOKENS.css](../docs/GOVGENZ-DESIGN-TOKENS.css) | Source des jetons CSS (référence ; appli = `public/assets/css/govgenz-tokens.css`) |

Schéma SQL & Docker : **`../govgenz-local/`**.

## Migration du module Projets vers un sous-domaine

Le programme projets peut tourner de deux façons avec **la même base de code** (un seul dépôt `govgenz-ci/`, une seule BDD) :

| Mode | Exemple d’URL fiche | Variable `.env` |
|------|---------------------|-----------------|
| **Préfixe chemin** (actuel en prod) | `https://genzgov.org/projects/sante-maternelle` | `app.projectsUsePathPrefix = true` |
| **Sous-domaine** (cible) | `https://projects.genzgov.org/sante-maternelle` | `app.projectsUsePathPrefix = false` + `app.projectsHost` |

Le routage est géré par `SiteContext` et `app/Filters/SiteContextFilter.php` : détection par **hôte HTTP** (`projects.*`) ou par le segment `/projects/` sur le site principal.

### Fichiers fournis pour le vhost « projets »

Dans **`deploy/host-b/`** :

- `index.php` — point d’entrée PHP du sous-domaine (même appli CI4, pas de copie de `app/` ni `vendor/`)
- `.htaccess` — réécriture Apache + cache des statiques

À placer sur l’hébergeur dans le **document root du sous-domaine** (voir cas A ou B ci-dessous).

### Démarche de migration (production)

#### 1. Préparer le DNS et le vhost

1. Créer un enregistrement DNS (souvent **A** ou **CNAME**) pour le sous-domaine, par ex. `projects.genzgov.org`, vers le même serveur que le site principal.
2. Dans le panneau hébergeur, créer un **vhost** (ou sous-domaine) dont la racine web pointe vers le dossier « projets » (étape 2).

#### 2. Déployer le point d’entrée du sous-domaine

**Cas A — docroot = sous-dossier `projects/` dans la racine CI** (recommandé en FTP classique) :

```text
/home/.../govgenz-ci/          ← racine du projet (app/, public/, vendor/, .env)
/home/.../govgenz-ci/projects/ ← document root du sous-domaine
    index.php      ← copie de deploy/host-b/index.php
    .htaccess      ← copie de deploy/host-b/.htaccess
```

Dans `projects/index.php`, laisser le **cas A** actif (`$projectARoot = dirname(__DIR__);`).

**Cas B — docroot ailleurs** : adapter `$projectARoot` vers le chemin absolu de la racine `govgenz-ci/` (voir commentaires dans `deploy/host-b/index.php`).

#### 3. Exposer les assets statiques

Depuis le docroot du sous-domaine, les URLs `/assets/…` et `/js/…` doivent servir les fichiers de `public/`.

Créer des **liens symboliques** (SSH) ou copies synchronisées (FTP) :

```text
projects/assets  → ../public/assets
projects/js        → ../public/js
projects/uploads → ../public/uploads   (si utilisé en public)
```

Sans cela, le sous-domaine affichera le HTML mais pas les CSS/JS.

#### 4. Mettre à jour `.env` (racine du projet, une seule fois)

Sur le **site principal** et le **sous-domaine**, c’est le même fichier `.env` à la racine de `govgenz-ci/`.

```ini
# Site principal (inchangé)
app.baseURL = 'https://genzgov.org/'

# Sous-domaine projets
app.projectsHost = 'projects.genzgov.org'
app.projectsBaseURL = 'https://projects.genzgov.org/'

# Après bascule : plus de /projects/ sur genzgov.org
app.projectsUsePathPrefix = false
```

- **`app.projectsHost`** : valeur de `HTTP_HOST` vue par PHP (sans `https://`). Le port n’est en général pas envoyé par le navigateur en prod.
- **`app.projectsBaseURL`** : URL de base du sous-domaine (avec `/` final) pour `site_url()`, liens admin, partage, etc.

Tant que `app.projectsUsePathPrefix = true`, les deux modes peuvent coexister en local (`/projects/…` sur `localhost:8082` **et** sous-domaine de test).

#### 5. Couper le préfixe `/projects/` sur le site principal

Quand le sous-domaine est validé :

1. Passer `app.projectsUsePathPrefix = false`.
2. Les URLs `/projects/…` sur `genzgov.org` renvoient alors **404** (comportement voulu tant que le mini-site n’est pas sur le domaine principal).
3. Mettre à jour les liens du **menu du site** (admin → Menu du site) pour pointer vers `https://projects.genzgov.org/…` au lieu de `/projects`.

#### 6. Contrôles après mise en ligne

| Test | Attendu |
|------|---------|
| `https://projects.genzgov.org/` | Liste des projets |
| `https://projects.genzgov.org/fr/…` ou `/en/…` | Locale OK |
| Fiche `https://projects.genzgov.org/mon-slug` | CSS/JS chargés, formulaire, QR partage |
| `https://genzgov.org/projects/…` | 404 (si préfixe désactivé) |
| Admin → lien « voir en ligne » projet | Pointe vers le sous-domaine |

Commande utile (Docker local) :

```bash
cd ../govgenz-local && docker compose exec web php spark routes
```

#### 7. Redirections (recommandé)

Pour ne pas perdre les anciens liens indexés ou partagés :

- Rediriger **301** `https://genzgov.org/projects/*` → `https://projects.genzgov.org/*` (règle Apache sur le vhost principal ou page `.htaccess` à la racine).

Exemple Apache (à adapter) :

```apache
RewriteRule ^projects/(.*)$ https://projects.genzgov.org/$1 [R=301,L]
```

### Test local du sous-domaine (Docker)

Dans **`govgenz-ci/.env`** (en plus du fragment Docker) :

```ini
app.projectsUsePathPrefix = true
app.projectsHost = projects.localhost
app.projectsBaseURL = 'http://projects.localhost:8082/'
```

Ajouter `127.0.0.1 projects.localhost` dans `/etc/hosts`, configurer le vhost Docker ou un second port si besoin — le code accepte `projects.localhost` avec le port `8082` du `.env` lorsque `HTTP_HOST` ne contient pas le port.

Fichiers de référence : **`env.production.example`** (section sous-domaine), **`deploy/host-b/`**.

## Module Positions (même principe que Projets)

| Mode | Exemple | Variable `.env` |
|------|---------|-----------------|
| Préfixe chemin | `https://genzgov.org/positions/…` | `app.positionsUsePathPrefix = true` |
| Sous-domaine | `https://positions.genzgov.org/…` | `app.positionsUsePathPrefix = false` + `app.positionsHost` |

Détection : `SiteContext` + `SiteContextFilter` (hôte `positions.*` ou segment `/positions/`). Styles dédiés : `positions-program-list.css`, `positions-program-show.css` (réutilisent les classes fiche `projects-program-show` + variantes `--pp-*` / boutons dans `govgenz-components.css`). Voir **[docs/CSS-ARCHITECTURE.md](docs/CSS-ARCHITECTURE.md)**.
