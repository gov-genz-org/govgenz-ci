<?php

declare(strict_types=1);

if (! function_exists('admin_pp_is_junk_repeat_line')) {
    /** Valeurs parasites (ancien bug formulaire : libellé du bouton enregistré comme ligne). */
    function admin_pp_is_junk_repeat_line(string $line): bool
    {
        $t = mb_strtolower(trim($line));

        return $t === '' || $t === 'retirer' || $t === 'retire' || $t === '×' || $t === 'x';
    }
}

if (! function_exists('admin_pp_scrub_junk_text')) {
    /** Champ texte : vide si valeur parasite (ex. libellé bouton enregistré par erreur). */
    function admin_pp_scrub_junk_text(string $line): string
    {
        $s = trim($line);

        return admin_pp_is_junk_repeat_line($s) ? '' : $s;
    }
}

if (! function_exists('admin_pp_repeat_scalar_lines')) {
    /**
     * Lignes texte pour formulaire blocs : valeurs remplies + une ligne vide finale.
     *
     * @param list<mixed> $raw
     * @return list<string>
     */
    function admin_pp_repeat_scalar_lines(array $raw): array
    {
        $lines = [];
        foreach (array_values($raw) as $item) {
            $s = trim(is_string($item) ? $item : (string) $item);
            if ($s !== '' && ! admin_pp_is_junk_repeat_line($s)) {
                $lines[] = $s;
            }
        }
        $lines[] = '';

        return $lines;
    }
}

if (! function_exists('admin_pp_repeat_object_rows')) {
    /**
     * Lignes objet pour formulaire blocs : lignes non vides + un modèle vide final.
     *
     * @param list<mixed> $raw
     * @param callable(array<string, mixed>): bool $isEmpty
     * @param array<string, mixed> $emptyTemplate
     * @return list<array<string, mixed>>
     */
    function admin_pp_repeat_object_rows(array $raw, callable $isEmpty, array $emptyTemplate): array
    {
        $rows = [];
        foreach (array_values($raw) as $row) {
            if (! is_array($row) || $isEmpty($row)) {
                continue;
            }
            $rows[] = $row;
        }
        $rows[] = $emptyTemplate;

        return $rows;
    }
}
