#!/usr/bin/env python3
"""
Génère govgenz-cms-guide-front-scoped.css : règles front-pages + bridge
uniquement sous .cms-guide-preview-host (aperçu admin/cms-guide).
"""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SCOPE = '.cms-guide-preview-host.ggz-public-theme.ggz-main-shell'
SOURCES = (
    ROOT / 'public/assets/css/govgenz-front-pages.css',
    ROOT / 'public/assets/css/govgenz-bridge.css',
)
OUT = ROOT / 'public/assets/css/govgenz-cms-guide-front-scoped.css'

SKIP_SELECTOR = re.compile(
    r'^('
    r'body\.ggz-public-theme\s*$|'
    r'body\.ggz-public-theme\s+#main-content\s*$|'
    r'\.nav\b|'
    r'\.ggz-skip-link\b|'
    r'::selection\b'
    r')',
    re.I,
)


def split_selectors(selector: str) -> list[str]:
    parts: list[str] = []
    buf: list[str] = []
    depth = 0
    for ch in selector:
        if ch == '(':
            depth += 1
        elif ch == ')':
            depth = max(0, depth - 1)
        elif ch == ',' and depth == 0:
            parts.append(''.join(buf).strip())
            buf = []
            continue
        buf.append(ch)
    tail = ''.join(buf).strip()
    if tail:
        parts.append(tail)
    return parts


def should_skip(selector: str) -> bool:
    for part in split_selectors(selector):
        if SKIP_SELECTOR.match(part.strip()):
            return True
    return False


def scope_selector(selector: str) -> str:
    scoped: list[str] = []
    for part in split_selectors(selector):
        part = part.strip()
        if not part:
            continue
        part = re.sub(
            r'body\.ggz-public-theme\s+#main-content(?:\.ggz-main-shell)?',
            SCOPE,
            part,
        )
        part = re.sub(r'#main-content\.ggz-main-shell', SCOPE, part)
        part = re.sub(r'#main-content', SCOPE, part)
        if part.startswith('.card') or part == '.card:hover' or part.startswith('.card '):
            part = f'{SCOPE} :is(article.wysiwyg, .ggz-shell-wysiwyg) {part}'
        elif re.match(r'^(button|\.btn)', part) and SCOPE not in part:
            part = f'{SCOPE} {part}'
        elif SCOPE not in part and not part.startswith('@'):
            part = f'{SCOPE} {part}'
        scoped.append(part)
    return ',\n'.join(scoped)


def parse_blocks(text: str) -> list[tuple[str, str]]:
    """Retourne [(kind, content)] kind in comment|at|rule."""
    blocks: list[tuple[str, str]] = []
    i = 0
    n = len(text)

    def skip_ws(pos: int) -> int:
        while pos < n and text[pos] in ' \t\r\n':
            pos += 1
        return pos

    while i < n:
        i = skip_ws(i)
        if i >= n:
            break
        if text.startswith('/*', i):
            end = text.find('*/', i)
            if end == -1:
                break
            blocks.append(('comment', text[i : end + 2]))
            i = end + 2
            continue
        if text[i] == '@':
            brace = text.find('{', i)
            if brace == -1:
                break
            depth = 0
            j = brace
            while j < n:
                if text[j] == '{':
                    depth += 1
                elif text[j] == '}':
                    depth -= 1
                    if depth == 0:
                        blocks.append(('at', text[i : j + 1]))
                        i = j + 1
                        break
                j += 1
            else:
                break
            continue
        brace = text.find('{', i)
        if brace == -1:
            break
        selector = text[i:brace].strip()
        depth = 0
        j = brace
        while j < n:
            if text[j] == '{':
                depth += 1
            elif text[j] == '}':
                depth -= 1
                if depth == 0:
                    body = text[brace + 1 : j].strip()
                    blocks.append(('rule', selector, body))
                    i = j + 1
                    break
            j += 1
        else:
            break

    return blocks


def process_blocks(blocks) -> str:
    out: list[str] = []

    for block in blocks:
        if block[0] == 'comment':
            out.append(block[1] + '\n')
            continue
        if block[0] == 'at':
            inner = block[1]
            brace = inner.find('{')
            prelude = inner[: brace + 1]
            rest = inner[brace + 1 : -1]
            nested = parse_blocks(rest)
            out.append(prelude + process_blocks(nested) + '}\n')
            continue
        _, selector, body = block
        if should_skip(selector):
            continue
        scoped = scope_selector(selector)
        out.append(f'{scoped} {{\n{body}\n}}\n\n')

    return ''.join(out)


def main() -> None:
    chunks = [
        '/**\n'
        ' * Aperçu admin/cms-guide uniquement — généré par scripts/scope-cms-guide-css.py\n'
        ' * (govgenz-front-pages.css + govgenz-bridge.css, scope .cms-guide-preview-host)\n'
        ' */\n\n',
    ]
    for path in SOURCES:
        chunks.append(f'/* --- {path.name} --- */\n\n')
        blocks = parse_blocks(path.read_text(encoding='utf-8'))
        chunks.append(process_blocks(blocks))

    OUT.write_text(''.join(chunks), encoding='utf-8')
    print(f'Wrote {OUT} ({OUT.stat().st_size} bytes)')


if __name__ == '__main__':
    main()
