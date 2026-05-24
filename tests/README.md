# Tests GovGenZ

## Exécution (Docker, recommandé)

Depuis **`../govgenz-local/`** (voir aussi [`../govgenz-local/README.md`](../govgenz-local/README.md)) :

```bash
cd ../govgenz-local
docker compose exec web bash -lc 'cd /var/www/html && vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-text --coverage-clover build/coverage/clover.xml'
```

| Suite | Dossier | CI |
|-------|---------|-----|
| Unit | `tests/unit/` | oui |
| App (feature, session, …) | `tests/` hors `unit/` | oui |

Rapports : `build/coverage/html/index.html`, `build/coverage/clover.xml`.

## Composer (PHP + PCOV sur la machine)

```bash
composer test          # Unit seulement
composer test:all      # Unit + App
composer test:coverage # texte + HTML
```

## Périmètre couverture

Config : `phpunit.xml.dist` — `app/` hors `Views/` et `Routes.php`.

Stratégie et liste des tests : [`docs/CODE-STRUCTURE.md`](../docs/CODE-STRUCTURE.md).

---

## Référence CodeIgniter / PHPUnit

Documentation amont : [Testing in CI4](https://codeigniter.com/user_guide/testing/index.html), [PHPUnit](https://phpunit.de/documentation.html).

Les tests BDD « métier » complets (MySQL projet) restent optionnels ; la CI tourne sans `.env` serveur (SQLite d’exemple pour `tests/database/`).
