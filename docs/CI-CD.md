# CI/CD — GovGenZ CI

Pipeline GitHub Actions + rulesets de branche pour sécuriser **main** (prod) et **develop** (staging).

Dépôt : [gov-genz-org/govgenz-ci](https://github.com/gov-genz-org/govgenz-ci)

## Structure Git

| Branche | Rôle |
|---------|------|
| `main` | Production (stable) |
| `develop` | Staging / tests d’intégration |
| `feature/*` | Nouvelles fonctionnalités → PR vers `develop` |
| `fix/*` | Corrections → PR vers `develop` |
| `hotfix/*` | Urgence prod → PR vers `main` **et** `develop` |

## Workflow complet

```text
feature/*  ──PR──► develop  ──(CI)──► merge  ──► deploy FTP staging
develop    ──PR──► main     ──(CI)──► merge  ──► deploy FTP production  ──► tag `v*`
hotfix/*   ──PR──► main + develop (rebase des deux si besoin)
```

Les jobs de déploiement ne tournent **pas** sur les pull requests, uniquement après merge (push sur `develop` ou `main`).

### Tags de release (`main`)

Après un **push sur `main`** réussi (typiquement merge de `develop`) et un **`deploy/production` vert**, le job **`release/tag`** :

1. Calcule le prochain tag semver avec [`deploy/next-release-tag.sh`](../deploy/next-release-tag.sh) (`v1.0.0`, puis `v1.0.1`, `v1.0.2`, … selon les tags existants).
2. Crée un **tag annoté** sur le commit déployé et le pousse sur `origin`.
3. Ne recrée pas de tag si ce commit a déjà un tag `v*.*.*` (re-run du workflow).

Exemples : `git fetch --tags && git tag -l 'v*' --sort=-v:refname | tail -5`

Le workflow a besoin de la permission **`contents: write`** (déjà accordée au job `release/tag`).

## CI (GitHub Actions)

Fichier : [`.github/workflows/ci.yml`](../.github/workflows/ci.yml)

| Check GitHub (ruleset) | Job | Contenu |
|------------------------|-----|---------|
| `ci/test` | `test` | `composer install` + PHPUnit (**Unit** + **App**, PCOV, rapport Clover/HTML en artefact) |
| `ci/build` | `build` | `composer install --no-dev`, `php spark list`, artefact release |

Les noms de jobs (`name: ci/test`, `name: ci/build`) doivent correspondre **exactement** aux statuts requis dans les rulesets.

Jobs optionnels (non requis par ruleset) :

| Job | Branche | Rôle |
|-----|---------|------|
| `deploy/staging` | `develop` | FTP → environnement staging |
| `deploy/production` | `main` | FTP → production |
| `release/tag` | `main` (après deploy prod OK) | Tag Git annoté `vMAJOR.MINOR.PATCH` (semver auto) |

### Fichiers jamais écrasés par FTP

Le déploiement **exclut** notamment :

- `.env` (config serveur)
- `writable/uploads/**`, `public/uploads/**` (médiathèque)
- caches / logs / sessions

Après déploiement, exécuter les migrations sur le serveur si nécessaire (`php spark migrate --all`) via SSH ou outil hébergeur — le FTP ne lance pas Spark automatiquement.

### Déploiement incrémental (fichiers modifiés uniquement)

L’action [FTP-Deploy-Action](https://github.com/SamKirkland/FTP-Deploy-Action) compare le contenu (hash) des fichiers locaux (`release/`) au fichier d’état sur le FTP (`.ftp-deploy-sync-*.json`).

**Comportement actuel (fiabilité)** : avant chaque deploy, le workflow **supprime** l’état local de sync (`rm -f release/.ftp-deploy-sync-*.json`) pour forcer un **upload complet** de `app/`, `public/`, `vendor/`, etc. Cela évite le cas « le serveur a été vidé mais l’état FTP dit que public/ est déjà à jour » → CSS/JS manquants.

| Deploy | Fichier d’état (recréé sur le FTP à la fin du job) |
|--------|-----------------------------------------------------|
| Staging | `.ftp-deploy-sync-staging.json` |
| Production | `.ftp-deploy-sync-production.json` |

**Contrôle avant FTP** : [`deploy/verify-release.sh`](../deploy/verify-release.sh) (build + deploy) — compte les fichiers, exige `public/index.php`, `public/.htaccess`, `.htaccess` racine, les CSS principaux, `vendor/autoload.php`, ≥ 500 fichiers au total.

**Artifact GitHub** : `actions/upload-artifact@v4` exclut par défaut les fichiers cachés (depuis v4.4) — le job `build` utilise `include-hidden-files: true` pour inclure `public/.htaccess` et `.htaccess`.

Les deploys sont **plus longs** (tout `vendor/` est re-scanné) mais **complets**. Sur le FTP, supprimer aussi les anciens `.ftp-deploy-sync-*.json` à la racine du projet si un incident a laissé un état incohérent.

**Cas lent malgré tout :** mise à jour de `composer.lock` → presque tout `vendor/` est re-uploadé (normal). Les logs `log-level: standard` indiquent combien de fichiers sont réellement envoyés.

**⚠️ Ne jamais lancer une 2ᵉ passe FTP** sur la même racine avec un dossier local qui ne contient que `.env` : l’action **supprime** tout le reste sur le serveur (`removing app/`, `vendor/`, etc.). L’ancien workflow faisait cette erreur ; corrigé en fusionnant le `.env` dans la passe `release/`.

## Fichier `.env` (base, URLs, clés)

**Vous ne poussez pas `.env` sur Git — c’est le bon réflexe.**

PHP sur le serveur **ne peut pas** lire les secrets GitHub. En revanche, **GitHub Actions** (avant le FTP) peut générer un `.env` à partir de secrets, puis l’envoyer sur le FTP.

### Deux modes de déploiement du `.env`

| Mode | Variable d’environment | Comportement |
|------|------------------------|--------------|
| **Manuel** (défaut) | `DEPLOY_GENERATE_ENV` absent ou ≠ `true` | Le FTP **n’envoie pas** `.env` ; le fichier sur le serveur reste celui que vous avez créé à la main. |
| **Automatique** | `DEPLOY_GENERATE_ENV = true` sur l’environment **staging** ou **production** | Le workflow **écrase** `release/.env` via [`deploy/generate-dotenv.sh`](../deploy/generate-dotenv.sh) (fichier unique, sections comme `env.example`), puis **une seule** passe FTP (`release/` complet : `app/`, `vendor/`, `writable/`, `.env`). |

### Qui fait quoi

| Étape | Où | Rôle |
|-------|-----|------|
| Secrets GitHub | Settings → Environments → staging / production | Valeurs BDD, URL, `encryption.key`, etc. |
| `generate-dotenv.sh` | Runner GitHub Actions | Écrit le fichier `.env` (bash, pas PHP sur le serveur) |
| FTP | Deploy | Envoie le `.env` généré à la racine du projet sur l’hébergeur |
| PHP CodeIgniter | Serveur | Lit le `.env` déposé par FTP comme d’habitude |

### Secrets obligatoires (mode automatique)

À définir dans **chaque** environment (`staging` et `production`), avec des valeurs **différentes** :

| Secret | Exemple staging | Exemple prod |
|--------|-----------------|--------------|
| `APP_BASE_URL` | `https://staging.genzgov.org/` | `https://genzgov.org/` |
| `DATABASE_NAME` | BDD staging | BDD prod |
| `DATABASE_USERNAME` | user staging | user prod |
| `DATABASE_PASSWORD` | mot de passe staging | mot de passe prod |
| `ENCRYPTION_KEY` | `hex2bin:…` **propre au staging** | `hex2bin:…` **propre à la prod** |

Optionnels : voir tableau **Hôtes et URLs** ci-dessous.

### Hôtes et URLs — staging ≠ production

Trois niveaux de « host » à ne pas confondre :

| Niveau | Secret / variable GitHub | Production (exemple) | Staging (à adapter chez vous) |
|--------|--------------------------|----------------------|-------------------------------|
| **FTP** (où le code est envoyé) | `FTP_PRODUCTION_*` / `FTP_STAGING_*` | serveur FTP prod | serveur FTP préprod (souvent **autre** machine ou compte) |
| **Site principal** | `APP_BASE_URL` (secret) | `https://genzgov.org/` | URL de **votre** préprod (ex. `https://staging.genzgov.org/` ou autre domaine) |
| **MySQL** | `DATABASE_HOSTNAME` (secret, optionnel) | souvent `localhost` | souvent `localhost` sur le compte staging (peut différer) |
| **Sous-domaine projets** | `PROJECTS_HOST` (variable), `PROJECTS_BASE_URL` (secret) | ex. `projects.genzgov.org` + `https://projects.genzgov.org/` | host/URL **staging** ou laisser vide si préfixe `/projects/` uniquement |
| **Sous-domaine positions** | `POSITIONS_HOST` (variable), `POSITIONS_BASE_URL` (secret) | ex. `positions.genzgov.org` + `https://positions.genzgov.org/` | idem staging |

Sur la prod actuelle ([genzgov.org](https://genzgov.org/)), `app.projectsUsePathPrefix = true` : les projets sont en `/projects/…` sur le **même** domaine. Les lignes `app.projectsHost` / `app.positionsHost` ne sont nécessaires que si vous activez un **vhost séparé** (sous-domaine).

**Règle :** chaque environment GitHub (`staging` / `production`) a sa **propre** ligne pour chaque secret — jamais la même URL ou le même FTP entre les deux.

Variables (non sensibles) : `CI_ENVIRONMENT` (**production** sur l’environment GitHub **production**, pas `development`), `APP_FORCE_HTTPS`, `ANALYTICS_ENABLED`, `PROJECTS_USE_PATH_PREFIX`, `POSITIONS_USE_PATH_PREFIX`, `PROJECTS_HOST`, `POSITIONS_HOST`, `APP_ASSET_VERSION`, …

Secrets URL : `PROJECTS_BASE_URL`, `POSITIONS_BASE_URL` (si sous-domaines utilisés).

### Activer la génération automatique

1. **Settings → Environments → staging** (puis **production**).
2. Ajouter les secrets ci-dessus (valeurs de **cet** environment uniquement).
3. Variable d’environment : `DEPLOY_GENERATE_ENV` = `true`.
4. Merger sur `develop` / `main` : le job génère et pousse le `.env`.

Sans `DEPLOY_GENERATE_ENV=true`, rien ne change : édition manuelle du `.env` sur le FTP comme avant.

### CI (tests) — toujours sans `.env` serveur

PHPUnit exécute les suites **Unit** (`tests/unit/`) et **App** (`tests/feature/`, etc.) avec **PCOV** (résumé dans les logs, artefact `phpunit-coverage` : HTML + Clover). Pas de MySQL projet : les tests d’intégration lourds restent skipped ou sur SQLite d’exemple.

Reproduire localement (Docker) :

```bash
cd govgenz-local
docker compose exec web bash -lc 'cd /var/www/html && vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-text'
```

Les secrets BDD ne servent qu’aux jobs **deploy**.

### Secrets FTP ≠ secrets application

`FTP_*` = connexion FTP. `APP_BASE_URL`, `DATABASE_*`, `ENCRYPTION_KEY` = contenu du `.env` application.

### Première installation sur un serveur

1. Déployer une fois (ou uploader le code à la main).
2. Sur le FTP / panneau hébergeur, créer **`govgenz-ci/.env`** à la racine du projet (même niveau que `app/`, `spark`).
3. S’inspirer de [`env.example`](../env.example) (modèle versionné, **sans secrets réels**).
4. Renseigner pour **cet** environnement uniquement :
   - `app.baseURL` (URL staging ou prod)
   - `database.default.*` (BDD de ce serveur)
   - `encryption.key` (une clé **par** environnement ; ne pas copier la prod sur le staging)
5. Ne jamais committer ce fichier : il reste dans `.gitignore`.

`env.local` dans le dépôt décrit le **Docker local** ; ce n’est pas déployé (exclu du FTP).

### Quand la config évolue

| Mode | Nouvelle clé dans le code | Changer mot de passe BDD |
|------|---------------------------|---------------------------|
| **Manuel** | Éditer `.env` sur le serveur + `env.example` dans Git | Éditer `.env` sur le serveur |
| **Automatique** (`DEPLOY_GENERATE_ENV=true`) | Mettre à jour [`deploy/generate-dotenv.sh`](../deploy/generate-dotenv.sh) + ajouter le secret/variable GitHub | Modifier le secret dans l’environment GitHub, redéployer |

Après changement du script, un merge sur `develop` / `main` régénère et renvoie le `.env` par FTP.

Vérification manuelle possible : `php spark env:check` sur le serveur.

### Schéma (mode automatique)

```text
Secrets GitHub (staging/prod)  →  generate-dotenv.sh (Actions)  →  .env  →  FTP  →  PHP lit .env
```

## Secrets GitHub

**Settings → Secrets and variables → Actions** (et **Environments** `staging` / `production` si vous isolez les secrets par env).

### Staging (`develop`)

| Secret | Exemple |
|--------|---------|
| `FTP_STAGING_SERVER` | `ftp.example.com` |
| `FTP_STAGING_USERNAME` | utilisateur FTP |
| `FTP_STAGING_PASSWORD` | mot de passe |
| `FTP_STAGING_REMOTE_DIR` | `/chemin/vers/govgenz-ci/` (avec `/` final recommandé) |

### Production (`main`)

| Secret | Exemple |
|--------|---------|
| `FTP_PRODUCTION_SERVER` | `ftp.genzgov.org` |
| `FTP_PRODUCTION_USERNAME` | … |
| `FTP_PRODUCTION_PASSWORD` | … |
| `FTP_PRODUCTION_REMOTE_DIR` | `/home/.../govgenz-ci/` |

Recommandation : attacher les secrets de prod uniquement à l’environment **production** (approbation manuelle optionnelle).

## Rulesets (interface GitHub)

**Settings → Rules → Rulesets** (organisation ou dépôt).

### Ruleset `main` (production)

Cible : branche `main`

| Règle | Valeur |
|-------|--------|
| Restrict creations | oui |
| Restrict updates | oui (pas de push direct) |
| Require pull request | oui |
| Required approvals | **2** |
| Require review from Code Owners | oui |
| Require status checks | **ci/test**, **ci/build** |
| Require branches up to date | oui |
| Block force pushes | oui |
| Require linear history | oui |

### Ruleset `develop` (staging)

Cible : branche `develop`

| Règle | Valeur |
|-------|--------|
| Restrict updates | oui |
| Require pull request | oui |
| Required approvals | **1** |
| Require status checks | **ci/test** |
| Block force pushes | recommandé |
| Require linear history | optionnel |

### CODEOWNERS

Fichier [`.github/CODEOWNERS`](../.github/CODEOWNERS) — mettre à jour `@gov-genz-org/core` avec une **équipe GitHub réelle** de votre organisation, sinon les reviews « code owners » échoueront.

## Première mise en service

1. Pousser `.github/workflows/ci.yml` sur une branche et ouvrir une PR vers `develop` pour valider que `ci/test` et `ci/build` passent.
2. Créer la branche `develop` sur GitHub si absente : `git push -u origin develop`.
3. Configurer les secrets FTP staging, merger sur `develop`, vérifier le job `deploy/staging`.
4. Configurer les secrets prod + rulesets **après** un premier run CI réussi (les checks apparaissent dans la liste des statuts requis).
5. Activer les rulesets `develop` puis `main`.

## Hotfix

```bash
git checkout main && git pull
git checkout -b hotfix/description-courte
# … commits …
# PR → main (2 reviews + ci/test + ci/build)
# PR → develop (cherry-pick ou merge main après coup)
```

## Dépannage

- **Checks introuvables dans le ruleset** : au moins une exécution réussie du workflow `CI` sur la branche concernée.
- **CODEOWNERS ignoré** : fichier sur `main` ; équipe/org avec droits sur le dépôt.
- **FTP échoue** : vérifier `REMOTE_DIR`, mode passif FTP, pare-feu ; consulter les logs du job `deploy/*`.
- **Site cassé après deploy** : `.env` non déployé — vérifier la config sur le serveur ; lancer migrations manuellement.
- **Tout effacé sur le FTP** (`removing app/`, `vendor/`, …) : souvent la 2ᵉ passe `.env` (bug corrigé). **Restaurer** depuis sauvegarde hébergeur si possible, puis merger le correctif CI et **relancer** `deploy/staging` ou `deploy/production` (re-upload complet de `release/`). Supprimer sur le FTP les fichiers `.ftp-deploy-sync-*-env.json` s’ils existent encore.
- **`.env` illisible** (doublons, `app.assetVersion = …database.default.hostname` sur une ligne) : ne **jamais** coller l’ancien `.env` sous le fichier généré. Sur le serveur, remplacer **tout** le `.env` par une génération propre (`generate-dotenv.sh` ou édition à partir de `env.example`). Vérifier la variable GitHub `CI_ENVIRONMENT` sur l’environment **production**.
- **`public/`, `vendor/` ou `writable/` incomplets** : souvent **état FTP obsolète** après effacement serveur (corrigé : reset d’état + `verify-release.sh`). Vérifier dans les logs FTP beaucoup de `upload`/`update` sur `public/assets/…`, pas seulement `removing`. Le build nettoie `writable/` (structure + `index.html`, pas les caches locaux). **`public/uploads/**`** n’est pas écrasé (médias déjà sur le serveur).
