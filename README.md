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

- Routes **front** : `/`, `/about`, `/contact`, `/press`, `/join`
- Routes **admin** : `/admin`, CRUD pages & posts, liste volontaires
- Charte : `public/assets/css/govgenz-tokens.css`

## Documentation

| Document | Rôle |
|----------|------|
| [GOVGENZ.md](../docs/GOVGENZ.md) | Vision, MVP, architecture |
| [GOVGENZ-CONCEPTION.md](../docs/GOVGENZ-CONCEPTION.md) | Modèle de données, routes |
| [GOVGENZ-DESIGN-TOKENS.css](../docs/GOVGENZ-DESIGN-TOKENS.css) | Source des jetons CSS |

Schéma SQL & Docker : **`../govgenz-local/`**.
