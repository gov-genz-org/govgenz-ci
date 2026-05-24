#!/usr/bin/env python3
"""Génère database/seed_project_projects_from_static.sql depuis site_govgenz/projects-govgenz/projects/*.html"""

import html as html_lib
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
STATIC = ROOT.parent / "site_govgenz" / "projects-govgenz" / "projects"
OUT = ROOT / "database" / "seed_project_projects_from_static.sql"

SECTOR_MAP = {
    "EDUCATION": "education",
    "DIGITAL": "digital",
    "FOOD": "food",
    "ECONOMY": "economy",
    "WATER": "water",
    "HEALTH": "health",
    "ENERGY": "energy",
    "ENVIRONMENT": "environment",
    "LEGAL": "legal",
    "CITIZEN": "citizen",
    "TERRITORIES": "territories",
    "INFRASTRUCTURE": "infrastructure",
    "MINES": "mines",
    "SECURITY": "security",
}

CARDS = [
    {
        "slug": "connectez-ecoles",
        "file": "connectez-ecoles.html",
        "sectors_upper": ["EDUCATION", "DIGITAL"],
        "status": "actif",
        "volunteers": 127,
        "budget_display": "850 M Ar",
        "geography": "10 régions",
        "excerpt": "Connectivité internet et énergie solaire pour 500 écoles rurales, avec formation des enseignants au numérique éducatif.",
        "launched_at": "2026-03-01",
        "duration_months": 30,
        "progress_percent": 38,
    },
    {
        "slug": "agrotech-jeunesse",
        "file": "agrotech-jeunesse.html",
        "sectors_upper": ["FOOD", "ECONOMY"],
        "status": "actif",
        "volunteers": 89,
        "budget_display": "420 M Ar",
        "geography": "Vakinankaratra",
        "excerpt": "Former 1 000 jeunes agriculteurs avec technologies durables, semences améliorées et accès direct aux marchés via une plateforme digitale.",
        "launched_at": "2026-01-01",
        "duration_months": 24,
        "progress_percent": 55,
    },
    {
        "slug": "eau-potable-2026",
        "file": "eau-potable-2026.html",
        "sectors_upper": ["WATER", "HEALTH"],
        "status": "candidat",
        "volunteers": 45,
        "budget_display": "280 M Ar",
        "geography": "Hauts Plateaux",
        "excerpt": "Construction de 50 points d'eau potable dans les Hauts Plateaux avec formation locale en maintenance et gouvernance communautaire.",
        "launched_at": "2026-04-01",
        "duration_months": 18,
        "progress_percent": 0,
    },
    {
        "slug": "energie-solaire",
        "file": "energie-solaire.html",
        "sectors_upper": ["ENERGY", "ENVIRONMENT"],
        "status": "actif",
        "volunteers": 156,
        "budget_display": "920 M Ar",
        "geography": "Sud & DIANA",
        "excerpt": "200 mini-grids solaires en zones isolées pour créer des emplois locaux et réduire les émissions de CO₂ de manière mesurable.",
        "launched_at": "2026-02-01",
        "duration_months": 36,
        "progress_percent": 22,
    },
    {
        "slug": "acces-justice",
        "file": "acces-justice.html",
        "sectors_upper": ["LEGAL", "CITIZEN"],
        "status": "validation",
        "volunteers": 67,
        "budget_display": "195 M Ar",
        "geography": "National",
        "excerpt": "500 jeunes paralegals formés et 20 centres d'aide juridique gratuite dans les zones reculées pour garantir l'accès à la justice.",
        "launched_at": "2026-03-01",
        "duration_months": 24,
        "progress_percent": 5,
    },
    {
        "slug": "sante-maternelle",
        "file": "sante-maternelle.html",
        "sectors_upper": ["HEALTH", "TERRITORIES"],
        "status": "actif",
        "volunteers": 112,
        "budget_display": "780 M Ar",
        "geography": "ATSIMO ANDREFANA",
        "excerpt": "Renforcer 100 cliniques rurales pour réduire la mortalité maternelle de 40 % en 3 ans — équipements, formations, protocoles.",
        "launched_at": "2026-04-01",
        "duration_months": 36,
        "progress_percent": 18,
    },
]


def sql_escape(s: str) -> str:
    return (
        s.replace("\\", "\\\\")
        .replace("'", "''")
        .replace("\0", "\\0")
        .replace("\n", "\\n")
        .replace("\r", "\\r")
        .replace("\x1a", "\\Z")
    )


def extract_project_main(html: str) -> str | None:
    m = re.search(
        r'<div class="project-main">(.+?)</div>\s*<!--\s*/project-main\s*-->',
        html,
        re.DOTALL,
    )
    if not m:
        return None
    return m.group(1).strip()


def meta_description(content: str) -> str:
    m = re.search(r'<meta\s+name="description"\s+content="([^"]*)"', content, re.I)
    if not m:
        return ""
    return html_lib.unescape(m.group(1))


def h1_title(content: str) -> str:
    m = re.search(r"<h1>([^<]+)</h1>", content)
    if not m:
        return ""
    return html_lib.unescape(m.group(1).strip())


def main() -> None:
    if not STATIC.is_dir():
        raise SystemExit(f"Dossier statique introuvable : {STATIC}")

    lines: list[str] = []
    lines.append(
        "-- Seed project_projects depuis site_govgenz/projects-govgenz (export statique)."
    )
    lines.append(
        "-- Prérequis : migrations (project_projects) + table sectors (codes minuscules)."
    )
    lines.append("-- Charset : utf8mb4.")
    lines.append("")
    lines.append("SET NAMES utf8mb4;")
    lines.append("SET FOREIGN_KEY_CHECKS = 0;")
    lines.append("")

    slugs = ", ".join(f"'{sql_escape(c['slug'])}'" for c in CARDS)
    lines.append(f"DELETE FROM project_projects WHERE slug IN ({slugs});")
    lines.append("")

    from datetime import datetime

    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    for c in CARDS:
        path = STATIC / c["file"]
        raw = path.read_text(encoding="utf-8")
        main = extract_project_main(raw)
        if not main:
            raise SystemExit(f"project-main introuvable : {path}")
        body = "<div class=\"project-main\">" + main + "</div>"
        meta_desc = meta_description(raw)
        title = h1_title(raw) or c["slug"]

        sectors_csv = ",".join(SECTOR_MAP.get(u, u.lower()) for u in c["sectors_upper"])
        pub = "published" if c["status"] in ("actif", "complete") else "draft"
        published_at = f"'{sql_escape(now)}'" if pub == "published" else "NULL"
        meta_title = title[:255] if len(title) <= 255 else title[:252] + "…"
        meta_desc = meta_desc[:512]

        lines.append(
            "INSERT INTO project_projects ("
            "slug, title, excerpt, body, project_status, publication_state, sectors_csv, "
            "volunteers_count, budget_display, geography, launched_at, duration_months, progress_percent, "
            "meta_title, meta_description, published_at, created_at, updated_at, deleted_at"
            ") VALUES ("
        )
        lines.append(f"  '{sql_escape(c['slug'])}',")
        lines.append(f"  '{sql_escape(title)}',")
        lines.append(f"  '{sql_escape(c['excerpt'])}',")
        lines.append(f"  '{sql_escape(body)}',")
        lines.append(f"  '{sql_escape(c['status'])}',")
        lines.append(f"  '{sql_escape(pub)}',")
        lines.append(f"  '{sql_escape(sectors_csv)}',")
        lines.append(f"  {int(c['volunteers'])},")
        lines.append(f"  '{sql_escape(c['budget_display'])}',")
        lines.append(f"  '{sql_escape(c['geography'])}',")
        lines.append(f"  '{sql_escape(c['launched_at'])}',")
        lines.append(f"  {int(c['duration_months'])},")
        lines.append(f"  {int(c['progress_percent'])},")
        lines.append(f"  '{sql_escape(meta_title)}',")
        lines.append(f"  '{sql_escape(meta_desc)}',")
        lines.append(f"  {published_at},")
        lines.append(f"  '{sql_escape(now)}',")
        lines.append(f"  '{sql_escape(now)}',")
        lines.append("  NULL")
        lines.append(");")
        lines.append("")

    lines.append("SET FOREIGN_KEY_CHECKS = 1;")
    lines.append("")

    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text("\n".join(lines), encoding="utf-8")
    print(f"Écrit : {OUT}")


if __name__ == "__main__":
    main()
