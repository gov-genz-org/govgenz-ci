# Structure du code — GovGenZ CI (CodeIgniter 4)

Guide pour éviter le « vibe coding » : où mettre quoi, et comment lire le dépôt.

## Couches

```text
Requête HTTP
    → Filters (auth, locale, SiteContext)
    → Controller (orchestration mince)
    → Library / Model / Helper
    → View
```

| Couche | Rôle | Exemples |
|--------|------|----------|
| **Controller** | HTTP : lire la requête, appeler le métier, choisir la vue | `Front\Projects\Home`, `Admin\Pages` |
| **Library** | Logique métier réutilisable, classes testables | `LocaleSlug`, `ProgramListFilter`, `SiteContext` |
| **Model** | Accès BDD, requêtes nommées | `ProjectProjectModel::findPublishedBySlug` |
| **Helper** | Fonctions procédurales liées aux vues / URLs | `project_helper`, `cms_helper` |
| **View** | HTML, partials, assets | `app/Views/front/...` |

Pas de dossier `Services/` pour l’instant : une **Library** remplit ce rôle.

## Règles de lecture

### Contrôleurs

- **&lt; 200 lignes** idéal pour un contrôleur front simple ; admin CRUD peut être plus long mais sans dupliquer la normalisation slug/locale.
- Pas de **chaînes HTML** (`&lt;link`, `&lt;script`) dans le contrôleur → utiliser `FrontPageAssets` ou une vue partial.
- Pas de **requêtes SQL complexes** inline → modèle ou `AdminListQuery`.
- **Admin** : réutiliser `BaseController::adminPaginatedList`, `adminRedirectToEdit`, `editorFormExtraScripts`.

### Slug, locale, traduction

Toujours via **`App\Libraries\LocaleSlug`** :

```php
$slug   = LocaleSlug::normalizeSlug($this->request->getPost('slug'));
$locale = LocaleSlug::normalizeLocale($this->request->getPost('locale'));
$group  = LocaleSlug::normalizeTranslationGroup($this->request->getPost('translation_group'));
```

### Assets front (CSS/JS)

Bundles dans **`app/Views/front/partials/head_assets/`**, exposés par **`FrontPageAssets`** :

```php
$extraHead = FrontPageAssets::projectsProgramList();
```

### Listes programme (projets / positions)

- Filtres POST JSON : **`ProgramListFilter`**
- Hero liste : **`CmsProgramListHero`**
- Stats bandeau projets : **`ProgramListProjectStats`**

### i18n

- **Front public** : `lang('Projects.*')`, fichiers `app/Language/`
- **Admin** : encore beaucoup de libellés en dur en français — à migrer progressivement vers `app/Language/fr/Admin.php`

### Multi-site (genzgov.org / projects / positions)

- **`SiteContext`** + **`SiteContextFilter`** : ne pas recopier la logique vhost dans chaque contrôleur.
- Routes : `app/Config/Routes.php` (registre unique, commenté).

## Fichiers à ne pas grossir sans extraction

| Fichier | Action si modification |
|---------|-------------------------|
| `Admin/ProjectProjects.php` | Extraire validation / budget / blocs vers une Library dédiée |
| `Front/Projects/Home.php` | Extraire `fundSubmit` vers un service contribution |
| `Helpers/admin_helper.php` | Point d’entrée ; logique dans `admin_*_helper.php` |
| `Libraries/MdgGeographyImporter.php` | OK en import one-shot ; pas de logique métier quotidienne dedans |

## Bonnes références dans le dépôt

- `Libraries/AdminListQuery.php` — pagination admin
- `Libraries/LocaleSlug.php` — normalisation CMS
- `Controllers/Admin/Auth.php` — délégation `StaffAuthPolicy`
- `Controllers/Front/Page.php` — contrôleur fin
- `Models/CmsPageModel.php` — cache publication

## Refactors livrés (backlog initial)

| Élément | Fichier |
|---------|---------|
| Slug / locale centralisés | `Libraries/LocaleSlug.php` |
| Assets front | `Libraries/FrontPageAssets.php`, `Views/front/partials/head_assets/` |
| Stats liste projets | `Libraries/ProgramListProjectStats.php` |
| Stats liste positions | `Libraries/ProgramListPositionStats.php` |
| Filtre positions (types) | `ProgramListFilter::filterByPositionTypes()` |
| Trait listes programme | `Controllers/Front/Traits/ProgramListFrontTrait.php` |
| Formulaire contribution | `Libraries/ProjectContributionSubmitter.php` |
| Formulaire admin projet | `Libraries/ProjectAdminForm.php` |
| Lang admin | `Language/fr/Admin.php`, `Language/en/Admin.php` (flash, erreurs, traduction) |
| Filtre JSON listes programme | `Libraries/FrontProgramListFilter.php` |
| Helpers admin découpés | `admin_url_helper.php`, `admin_translation_helper.php`, `admin_list_helper.php`, `admin_datetime_helper.php`, `admin_form_helper.php`, `admin_staff_helper.php` |
| Assets page Rejoindre | `head_assets/join.php`, `head_assets/join_scripts.php`, `FrontPageAssets::join()` |
| Libellés chrome admin | Sidebar, layout, breadcrumbs, modale confirm — `lang('Admin.nav_*')`, `Admin.ui_*` |
| Flash enregistrement admin | `flash_*_created` / `flash_*_updated` dans les contrôleurs CRUD |
| Erreurs admin (contrôleurs) | `error_*` dans `Admin.php` — Auth, Pages, Posts, Staff, etc. |
| Libellés vues admin (listes/form) | Titres, Dupliquer trad, confirmations suppression, auth |

## Prochaines étapes (backlog)

1. Libellés restants : sous-champs des blocs projet (puces, timeline, sources), formulaires pages/posts, guide CMS.
2. Switcher optionnel de locale UI admin (`en/Admin.php` déjà prêt).
3. Tests feature (DB) dans la CI si/base de test dédiée.

Voir aussi [CI-CD.md](CI-CD.md) pour le pipeline de déploiement.
