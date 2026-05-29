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

Les branches `feature/*`, `fix/*` et `hotfix/*` lancent le build sur push, mais ne déploient pas automatiquement. Les jobs de déploiement automatiques ne tournent **pas** sur les pull requests : staging après `push` sur `develop`, production après `push` sur `main`. Pour tester une feature branch en staging sans la merger dans `develop`, lancer manuellement le workflow **CI** sur cette branche.

### Tags de release (`main`)

Après un **push sur `main`** réussi et un **`deploy/production` vert**, le job **`release/tag`** :

1. Détecte le type de bump avec [`deploy/detect-release-bump.sh`](../deploy/detect-release-bump.sh) :
   - **merge `develop` → `main`** → **incrément mineur**, patch remis à `0` (`v1.0.0` → `v1.1.0`) ;
   - **merge `fix/*` ou `hotfix/*` → `main`** → **incrément patch** (`v1.1.0` → `v1.1.1`).
2. Calcule le tag avec [`deploy/next-release-tag.sh`](../deploy/next-release-tag.sh) (`minor` ou `patch`).
3. Crée un **tag annoté** sur le commit déployé et le pousse sur `origin`.
4. Met à jour **`CHANGELOG.md` sur `main`** (section `[X.Y.Z]`, commit `docs(changelog): vX.Y.Z`) — idempotent ; si push refusé, PR `release/changelog-vX.Y.Z-main` + **`release/sync-merge-main`**.
5. Ne recrée pas de tag si ce commit a déjà un tag `v*.*.*` (re-run du workflow).
6. **Prépare `develop` sans PR `release/post-*`** : push direct `chore(release): prepare develop X.Y.Z after vA.B.C` — `VERSION` = prochaine cible (ex. `v1.12.0` → `1.13.0`), `CHANGELOG` repris de `main` si la section existe, sinon généré. **`develop` ≠ `main`** : `develop` garde le code d’intégration + version suivante ; `main` = prod + `VERSION` de la release déployée.

**Flux des branches**

| Branche | Contenu |
|---------|---------|
| **`develop`** | Dernière intégration (staging), `VERSION` = **prochaine** release |
| **`main`** | Prod taguée, `VERSION` = **release en cours** |

Seul merge de code prévu : **`develop` → `main`** (PR). Pas de merge `release/post-*` → `develop` ni `main` → `develop` automatique.

**Ne pas re-tagger après la PR changelog** : merge `release/changelog-*` → `main` ignoré par **`ci/main-push-kind`** (`git log`, pas `head_commit`).

**Détection** (message du commit HEAD sur `main`) :

- **patch** — merge PR : `Merge pull request #N from …/fix/…` ou `…/hotfix/…` ; squash : sujet `fix:` / `hotfix:` ;
- **minor** — merge PR : `Merge pull request #N from …/develop` ; sinon défaut (release normale).

Exemples : `git fetch --tags && git tag -l 'v*' --sort=-v:refname | tail -5`

Le workflow a besoin des permissions **`contents: write`** et **`pull-requests: write`** (job `release/tag`).

**Création de PR après release** — si le job affiche *GitHub Actions is not permitted to create … pull requests*, le réglage **dépôt** ne suffit pas : l’**organisation** `gov-genz-org` bloque souvent la création de PR par `GITHUB_TOKEN`.

**Option 1 — Organisation** (owner org requis)  
[github.com/organizations/gov-genz-org/settings/actions](https://github.com/organizations/gov-genz-org/settings/actions) → **Workflow permissions** → **Allow GitHub Actions to create and approve pull requests** → Save. Puis re-lancer le job `release/tag` (ou ouvrir la PR à la main une fois).

**Option 2 — Secret `RELEASE_PR_TOKEN`** (recommandé)  
PAT fine-grained : **Contents** + **Pull requests** (read/write). Secret `RELEASE_PR_TOKEN` — utilisé pour `git push` / `gh` dans `release/tag` et **`release/sync-merge-main`** (PR changelog → `main` uniquement).

Push direct sur **`develop`** après release : nécessite bypass **GitHub Actions** sur le ruleset `develop` ou PAT avec droit push (via `Configure Git credentials` dans le job).

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
| `deploy/staging` | `develop`, ou lancement manuel du workflow `CI` | FTP → environnement staging |
| `deploy/production` | `main` | FTP → production |
| `release/tag` | `main` (après deploy prod OK) | Tag, CHANGELOG `main`, préparation `develop` (push direct) |
| `release/sync-merge-main` | PR `release/changelog-*` → `main` (après `ci/test` + `ci/build`) | Merge auto CHANGELOG sur `main` |

Déploiement manuel d’une feature branch en staging : **Actions → CI → Run workflow**, choisir la branche dans le sélecteur GitHub, puis lancer. Le workflow exécute `ci/test`, `ci/build`, puis `deploy/staging`.

### Fichiers jamais écrasés par FTP

Le déploiement **exclut** notamment :

- `.env` (mode manuel uniquement — voir ci-dessous)
- `writable/uploads/**`, `uploads/**`, `public/uploads/**` (médiathèque — contenu préservé sur le serveur)

Les dossiers `writable/cache/`, `logs/`, `session/`, `debugbar/` sont **envoyés** avec leur `index.html` (structure vide). Le contenu runtime (fichiers de cache, logs) n’est pas dans l’artifact CI et reste créé **sur le serveur** par PHP.

**Médiathèque CMS** : avec docroot = racine projet (`index.php` à la racine), les fichiers sont enregistrés dans **`uploads/cms/`** (hors `public/`), pas dans `public/uploads/`. L’URL reste `https://domaine/uploads/cms/fichier.svg` grâce au `.htaccess` racine. Option `.env` : `app.mediaStoragePath` (chemin absolu ou relatif). Si le dossier disque est ailleurs (ex. `/home2/…/upload/cms`), définir cette variable **ou** créer un lien symbolique `uploads/cms` → ce dossier.

Après déploiement, exécuter les migrations sur le serveur si nécessaire (`php spark migrate --all`) via SSH ou outil hébergeur — le FTP ne lance pas Spark automatiquement.

### Dépannage prod : cache non writable / encryption.key placeholder

**Erreur** : `Cache unable to write to ".../writable/cache/"`

1. Vérifier que le dossier existe sur le FTP : `writable/cache/index.html`.
2. **Permissions cPanel** (Gestionnaire de fichiers) : dossier `writable/` → **775** (ou **755** selon hébergeur), cocher « Appliquer récursivement » sur `cache/`, `logs/`, `session/`, `debugbar/`, `uploads/`.
3. Si le dossier manque : redéployer après merge (le workflow envoie désormais la structure `writable/*/index.html`).

**Erreur / `.env`** : `encryption.key = hex2bin:REMPLACER_PAR_CLE_GENEREE`

Le script [`deploy/generate-dotenv.sh`](../deploy/generate-dotenv.sh) **ne génère pas** la clé : il recopie le secret GitHub `ENCRYPTION_KEY`. Un placeholder signifie :

- `DEPLOY_GENERATE_ENV` ≠ `true` → `.env` manuel copié depuis `env.example` ; **ou**
- secret `ENCRYPTION_KEY` absent ou invalide dans **Settings → Environments → production**.

**Créer la clé (une fois par environnement)** :

```bash
php spark key:generate --show
# ex. hex2bin:0123456789abcdef...
```

Coller la valeur dans le secret GitHub **production** `ENCRYPTION_KEY`, activer `DEPLOY_GENERATE_ENV=true`, redéployer. **Ne pas changer** cette clé ensuite (sessions / données chiffrées).

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

### Ruleset `develop` (staging) {#ruleset-develop-staging}

Cible : branche `develop`

**Politique** : push direct interdit pour les humains, merge via **PR** + **ci/test** + 1 approbation. Exception : **`release/tag`** pousse un commit `chore(release): prepare develop …` sur `develop` (bypass **GitHub Actions** ou `RELEASE_PR_TOKEN` requis).

| Règle | Valeur |
|-------|--------|
| Restrict updates | oui (push direct interdit) |
| Require pull request | oui |
| Required approvals | **1** |
| Require status checks | **ci/test** |
| Block force pushes | recommandé |
| Require linear history | optionnel |

#### Pourquoi pas « GitHub Actions » dans Bypass ?

L’UI ne propose pas **GitHub Actions** : seulement rôles (admin, write…), équipes, apps (Copilot, Merge Queue…). Le **`GITHUB_TOKEN`** du workflow **ne contourne pas** les rulesets — cocher *Repository admin* ne s’applique pas aux pushes faits par Actions.

#### Option avancée : push direct (compte machine)

1. Compte machine collaborateur **Write**, PAT fine-grained (contents sur ce dépôt).
2. Équipe dédiée (ex. `@gov-genz-org/release-sync`) avec **uniquement** ce compte → **Bypass list** `develop` (PR + status checks).
3. Secret `RELEASE_SYNC_GITHUB_TOKEN` et push avec ce token dans le job (pas `GITHUB_TOKEN`).

Par défaut le dépôt utilise la **PR automatique** pour changelog → `main` uniquement ; **push direct** sur `develop` après release (pas de PR `release/post-*`).

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

## Déploiement manuel staging / rollback

Deux besoins différents :

| Approche | Quand l’utiliser | Effet sur Git |
|----------|------------------|---------------|
| **Run workflow sur `CI`** | Tester une feature branch directement en staging | **`main` / `develop` inchangés** |
| **`git revert` + merge sur `main`** | Correction durable, historique propre | `main` reflète la prod |

### Déploiement manuel en staging (`CI`)

1. GitHub → **Actions** → workflow **CI** → **Run workflow**.
2. Choisir la branche à déployer dans le sélecteur GitHub (ex. `feature/x`, `fix/y`).
3. Attendre `ci/test` → `ci/build` → `deploy/staging`.

Le job repasse les tests PHPUnit sur la branche choisie, rebuild l’artefact release et envoie un upload FTP complet vers staging. **`deploy/production` et `release/tag` ne tournent pas** sur lancement manuel.

**Limites :**

- Ne déploie pas en production.
- Ne rollback **pas** la base MySQL.
- Si `DEPLOY_GENERATE_ENV=true`, le `.env` staging actuel des secrets GitHub est régénéré.

**Retrouver un tag :**

```bash
git fetch --tags
git tag -l 'v*' --sort=-v:refname | head -5
git show v1.0.2 --no-patch
```

Sur le serveur, `writable/deploy_version.txt` contient le SHA, l’événement GitHub, la branche et l’identifiant de run.

### Rollback Git (recommandé à terme)

```bash
git checkout main && git pull origin main
git revert <sha-du-mauvais-commit>   # ou merge d’une PR hotfix/revert
git push origin main
```

→ CI normal → deploy prod → nouveau tag `v*`.

## Dépannage

- **Checks introuvables dans le ruleset** : au moins une exécution réussie du workflow `CI` sur la branche concernée.
- **`release/tag` : push develop refusé** : activer bypass **GitHub Actions** sur ruleset `develop` ou vérifier `RELEASE_PR_TOKEN` (voir [Tags de release](#tags-de-release-main)).
- **PR `docs(changelog): vX.Y.Z on main` ouverte** : `release/sync-merge-main` merge après `ci/test` + `ci/build` (re-run CI sur la PR si le job n’existait pas encore).
- **Tag fantôme après merge changelog** (ex. `v1.11.0` alors que la release était `v1.10.0`) : supprimer le tag `v1.11.0` sur GitHub ; garder `main` tel quel ; merger `develop` → `main` au prochain cycle. Les merges `release/changelog-*` ne déclenchent plus deploy/tag.
- **CODEOWNERS ignoré** : fichier sur `main` ; équipe/org avec droits sur le dépôt.
- **FTP échoue** : vérifier `REMOTE_DIR`, mode passif FTP, pare-feu ; consulter les logs du job `deploy/*`.
- **Site cassé après deploy** : `.env` non déployé — vérifier la config sur le serveur ; lancer migrations manuellement.
- **Tout effacé sur le FTP** (`removing app/`, `vendor/`, …) : souvent la 2ᵉ passe `.env` (bug corrigé). **Restaurer** depuis sauvegarde hébergeur si possible, puis merger le correctif CI et **relancer** `deploy/staging` ou `deploy/production` (re-upload complet de `release/`). Supprimer sur le FTP les fichiers `.ftp-deploy-sync-*-env.json` s’ils existent encore.
- **`.env` illisible** (doublons, `app.assetVersion = …database.default.hostname` sur une ligne) : ne **jamais** coller l’ancien `.env` sous le fichier généré. Sur le serveur, remplacer **tout** le `.env` par une génération propre (`generate-dotenv.sh` ou édition à partir de `env.example`). Vérifier la variable GitHub `CI_ENVIRONMENT` sur l’environment **production**.
- **`public/`, `vendor/` ou `writable/` incomplets** : souvent **état FTP obsolète** après effacement serveur (corrigé : reset d’état + `verify-release.sh`). Vérifier dans les logs FTP beaucoup de `upload`/`update` sur `public/assets/…`, pas seulement `removing`. Le build nettoie `writable/` (structure + `index.html`, pas les caches locaux). **`public/uploads/**`** n’est pas écrasé (médias déjà sur le serveur).
