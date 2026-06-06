# Base de connaissance design — GoV Gen Z Madagascar

Version : 2026-06-05

> Document réutilisable pour créer des pages, dashboards, applications, landing pages ou interfaces liées à **GoV Gen Z Madagascar**.  
> À utiliser avec le logo officiel GoV Gen Z.

---

## 1. Identité visuelle générale

GoV Gen Z Madagascar doit avoir une identité :

- civitech ;
- sobre ;
- institutionnelle ;
- moderne ;
- technique ;
- structurée ;
- militante mais propre ;
- orientée action citoyenne ;
- compatible mode clair et mode sombre.

L’interface ne doit pas ressembler à un SaaS générique.  
Elle doit donner l’impression d’une plateforme sérieuse, citoyenne, organisée et tournée vers la transformation de Madagascar.

---

## 2. Palette principale

### Couleurs centrales

| Usage | Couleur | Hex |
|---|---:|---|
| Rouge principal / action / urgence | Crimson GoV Gen Z | `#C41E30` |
| Rouge sombre / hover | Crimson dark | `#A81C22` |
| Turquoise civitech / liens / secteurs | Teal civitech | `#1FA6A6` |
| Turquoise sombre | Teal dark | `#148C8C` |
| Vert validation | Success | `#3FA66B` |
| Saumon / contexte / warning doux | Warning soft | `#E58C7D` |

---

## 3. Palette mode clair

```ts
const govGenZLightTheme = {
  background: "#F8F8F9",
  surface: "#FFFFFF",
  surfaceSoft: "#F3F4F6",
  card: "#FFFFFF",
  border: "#E4E7EB",

  text: "#111827",
  textMuted: "#6B7280",
  textSoft: "#9CA3AF",

  primary: "#C41E30",
  primaryDark: "#A81C22",
  primarySoft: "#F3E8EA",

  accent: "#1FA6A6",
  accentDark: "#148C8C",
  accentSoft: "#EAF6F6",

  warning: "#E58C7D",
  success: "#3FA66B",
  danger: "#C41E30"
}
```

### Usage recommandé en mode clair

- Fond général : `#F8F8F9`
- Cartes : `#FFFFFF`
- Bordures : `#E4E7EB`
- Texte principal : `#111827`
- Texte secondaire : `#6B7280`
- Bouton principal : `#C41E30`
- Liens / badges secteurs : `#1FA6A6`
- Éléments validés : `#3FA66B`

---

## 4. Palette mode sombre

```ts
const govGenZDarkTheme = {
  background: "#070C19",
  backgroundSoft: "#0D111D",
  surface: "#131727",
  surfaceSoft: "#181927",
  card: "#0E1626",
  border: "#252939",

  text: "#F3F4F6",
  textMuted: "#A4ABB5",
  textSoft: "#6B7280",

  primary: "#C41E30",
  primaryDark: "#A81C22",
  primarySoft: "#261A28",

  accent: "#1FA6A6",
  accentDark: "#148C8C",
  accentSoft: "#0B292A",

  warning: "#E58C7D",
  success: "#3FA66B",
  danger: "#DC2626"
}
```

### Usage recommandé en mode sombre

- Fond général : `#070C19`
- Fond secondaire : `#0D111D`
- Cartes : `#0E1626`
- Surfaces : `#131727`
- Bordures : `#252939`
- Texte principal : `#F3F4F6`
- Texte secondaire : `#A4ABB5`
- Bouton principal : `#C41E30`
- Liens / données / badges : `#1FA6A6`

---

## 5. Style visuel GoV Gen Z

### Direction artistique

L’interface doit être :

- épurée ;
- aérée ;
- structurée ;
- sérieuse ;
- lisible ;
- légèrement technique ;
- avec beaucoup d’espace vide ;
- avec des cartes bien séparées ;
- avec une hiérarchie visuelle claire.

### Typographie

Recommandation :

```txt
Titres : Oswald, Bebas Neue ou Barlow Condensed
Texte/UI : Space Mono, IBM Plex Mono ou JetBrains Mono
Fallback : system-ui, sans-serif
```

Style typographique :

- titres en majuscules ;
- menus en majuscules ;
- letter-spacing fort pour les menus, badges et petits titres ;
- textes courts et précis ;
- éviter les longs blocs compacts ;
- utiliser des labels courts.

### Formes

- coins légèrement arrondis ;
- bordures fines ;
- ombres très discrètes en mode clair ;
- peu ou pas d’ombre en mode sombre ;
- cartes rectangulaires propres ;
- boutons nets et sobres ;
- filtres sous forme de pills.

### Boutons

Bouton principal :

- fond rouge `#C41E30` ;
- texte blanc ;
- hover `#A81C22`.

Bouton secondaire :

- fond transparent ou blanc/surface ;
- bordure fine ;
- texte sombre ou turquoise ;
- hover discret.

Bouton analytique / lien :

- turquoise `#1FA6A6` ;
- utilisé pour détails, filtres, actions secondaires.

### Badges

Badges secteurs :

- fond turquoise doux ;
- texte turquoise ;
- bordure turquoise claire ;
- forme pill ;
- uppercase ;
- petit letter-spacing.

Badges statut :

- `verified` / `active` : vert ;
- `pending` / `under_review` : turquoise ou warning doux ;
- `rejected` / `critical` : rouge ;
- `archived` : gris.

---

## 6. Prompt design réutilisable

Copier-coller ce prompt à chaque fois qu’il faut créer une page, une application, un dashboard ou une interface GoV Gen Z.

```text
Crée une interface cohérente avec l’identité visuelle de GoV Gen Z Madagascar.

Direction artistique :
- civitech, sobre, institutionnelle, moderne et technique ;
- militante mais propre ;
- sérieuse, lisible, structurée ;
- ne pas faire un SaaS générique ;
- l’interface doit ressembler à une extension applicative de genzgov.org ;
- utiliser le logo officiel GoV Gen Z si disponible.

Mode clair :
- background principal : #F8F8F9
- surface / cartes : #FFFFFF
- surface douce : #F3F4F6
- bordures : #E4E7EB
- texte principal : #111827
- texte secondaire : #6B7280
- texte discret : #9CA3AF
- rouge principal : #C41E30
- rouge sombre : #A81C22
- rouge doux : #F3E8EA
- accent turquoise : #1FA6A6
- accent turquoise sombre : #148C8C
- accent turquoise doux : #EAF6F6
- warning saumon : #E58C7D
- success : #3FA66B

Mode sombre :
- background principal : #070C19
- background secondaire : #0D111D
- surface : #131727
- surface douce : #181927
- cartes : #0E1626
- bordures : #252939
- texte principal : #F3F4F6
- texte secondaire : #A4ABB5
- texte discret : #6B7280
- rouge principal : #C41E30
- rouge sombre : #A81C22
- rouge doux sombre : #261A28
- accent turquoise : #1FA6A6
- accent turquoise sombre : #148C8C
- accent turquoise doux sombre : #0B292A

Style UI :
- beaucoup d’espace vide ;
- titres en uppercase avec letter-spacing ;
- menus et badges en uppercase ;
- cartes propres avec bordures fines ;
- coins légèrement arrondis ;
- boutons principaux rouges ;
- boutons secondaires avec bordure fine ;
- liens et actions secondaires en turquoise ;
- filtres sous forme de pills ;
- badges sectoriels turquoise ;
- alertes critiques en rouge ;
- éléments validés en vert ;
- dark mode et light mode obligatoires ;
- interface responsive desktop/mobile ;
- compatible Safari.

Typographie recommandée :
- titres : Oswald, Bebas Neue ou Barlow Condensed ;
- texte/UI : Space Mono, IBM Plex Mono ou JetBrains Mono ;
- fallback : system-ui, sans-serif.

Le rendu final doit être sérieux, civique, moderne, clair, organisé et aligné avec l’image de GoV Gen Z Madagascar.
```

---

## 7. Prompt court réutilisable

Version courte à utiliser dans un brief rapide.

```text
Utilise l’identité GoV Gen Z Madagascar : interface civitech sobre, institutionnelle, moderne, technique, non générique. 
Mode clair avec fond #F8F8F9, cartes #FFFFFF, texte #111827, rouge action #C41E30, turquoise civitech #1FA6A6.
Mode sombre avec fond #070C19, cartes #0E1626, surfaces #131727, bordures #252939, texte #F3F4F6, rouge #C41E30, turquoise #1FA6A6.
Titres uppercase avec letter-spacing, typographie type Oswald/Bebas Neue pour titres et Space Mono/IBM Plex Mono pour UI.
Cartes propres, bordures fines, boutons rouges, badges secteurs turquoise, alertes critiques rouges, validations vertes, dark/light mode obligatoire.
```

---

## 8. CSS variables réutilisables

> **Implémentation site** : `public/assets/css/govgenz-tokens.css` — thèmes via `[data-theme="dark"]` (défaut) et `[data-theme="light"]` (pas la classe `.dark` ci-dessous). Les alias legacy `--ggz-red`, `--ggz-teal`, `--ggz-peach` pointent vers `--ggz-primary`, `--ggz-accent`, `--ggz-warning`.

```css
:root {
  --ggz-bg: #F8F8F9;
  --ggz-surface: #FFFFFF;
  --ggz-surface-soft: #F3F4F6;
  --ggz-card: #FFFFFF;
  --ggz-border: #E4E7EB;

  --ggz-text: #111827;
  --ggz-text-muted: #6B7280;
  --ggz-text-soft: #9CA3AF;

  --ggz-primary: #C41E30;
  --ggz-primary-dark: #A81C22;
  --ggz-primary-soft: #F3E8EA;

  --ggz-accent: #1FA6A6;
  --ggz-accent-dark: #148C8C;
  --ggz-accent-soft: #EAF6F6;

  --ggz-warning: #E58C7D;
  --ggz-success: #3FA66B;
  --ggz-danger: #C41E30;
}

.dark {
  --ggz-bg: #070C19;
  --ggz-bg-soft: #0D111D;
  --ggz-surface: #131727;
  --ggz-surface-soft: #181927;
  --ggz-card: #0E1626;
  --ggz-border: #252939;

  --ggz-text: #F3F4F6;
  --ggz-text-muted: #A4ABB5;
  --ggz-text-soft: #6B7280;

  --ggz-primary: #C41E30;
  --ggz-primary-dark: #A81C22;
  --ggz-primary-soft: #261A28;

  --ggz-accent: #1FA6A6;
  --ggz-accent-dark: #148C8C;
  --ggz-accent-soft: #0B292A;

  --ggz-warning: #E58C7D;
  --ggz-success: #3FA66B;
  --ggz-danger: #DC2626;
}
```

---

## 9. Tailwind tokens suggérés

```ts
colors: {
  gov: {
    bg: "var(--ggz-bg)",
    surface: "var(--ggz-surface)",
    surfaceSoft: "var(--ggz-surface-soft)",
    card: "var(--ggz-card)",
    border: "var(--ggz-border)",

    text: "var(--ggz-text)",
    muted: "var(--ggz-text-muted)",
    soft: "var(--ggz-text-soft)",

    primary: "var(--ggz-primary)",
    primaryDark: "var(--ggz-primary-dark)",
    primarySoft: "var(--ggz-primary-soft)",

    accent: "var(--ggz-accent)",
    accentDark: "var(--ggz-accent-dark)",
    accentSoft: "var(--ggz-accent-soft)",

    warning: "var(--ggz-warning)",
    success: "var(--ggz-success)",
    danger: "var(--ggz-danger)"
  }
}
```
