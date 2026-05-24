<?php

declare(strict_types=1);

if (! function_exists('project_published_material_lines_from_row')) {
    /**
     * @param array<string, mixed> $row
     *
     * @return list<array{item: string, qty: string}>
     */
    function project_published_material_lines_from_row(array $row): array
    {
        $items = trim((string) ($row['items'] ?? ''));
        if ($items === '') {
            return [];
        }

        $lines = [];
        foreach (project_published_material_split_items_string($items) as $chunk) {
            $parsed = project_published_material_parse_line($chunk);
            if ($parsed !== null) {
                $lines[] = $parsed;
            }
        }

        if ($lines === []) {
            return [];
        }

        if (count($lines) === 1 && $lines[0]['qty'] === '') {
            $qty = trim((string) ($row['quantity'] ?? ''));
            if ($qty !== '' && ! str_contains($qty, ',')) {
                $lines[0]['qty'] = $qty;
            }
        }

        return $lines;
    }
}

if (! function_exists('project_fund_material_storage_from_lines')) {
    /**
     * @param list<array{item: string, qty: string}> $lines
     *
     * @return array{items: string, quantity: string|null}
     */
    function project_fund_material_storage_from_lines(array $lines): array
    {
        $itemLines = [];
        $qtyParts  = [];
        foreach ($lines as $line) {
            $item = $line['item'];
            $qty  = $line['qty'];
            $itemLines[] = $qty !== '' ? $item . ' — ×' . $qty : $item;
            if ($qty !== '') {
                $qtyParts[] = $qty;
            }
        }

        return [
            'items'    => implode("\n", $itemLines),
            'quantity' => $qtyParts !== [] ? implode(', ', $qtyParts) : null,
        ];
    }
}

if (! function_exists('project_fund_post_url')) {
    /**
     * URL POST du formulaire « Financer ce projet ».
     */
    function project_fund_post_url(string $slug): string
    {
        helper('locale');
        $slug = trim($slug, '/');
        if (\App\Libraries\SiteContext::projectsPathPrefixEnabled()) {
            return localized_site_url('projects/' . $slug . '/fund');
        }

        return localized_site_url($slug . '/fund');
    }
}

if (! function_exists('project_fund_phone_contact_from_request')) {
    /**
     * Téléphone formaté (indicatif + numéro) depuis la requête POST.
     */
    function project_fund_phone_contact_from_request(string $prefix): string
    {
        $country = trim((string) service('request')->getPost($prefix . '_phone_country'));
        $number  = trim((string) service('request')->getPost($prefix . '_phone_number'));

        if ($number === '') {
            return '';
        }

        return $country !== '' ? trim($country . ' ' . $number) : $number;
    }
}

if (! function_exists('project_fund_material_lines_from_request')) {
    /**
     * @return list<array{item: string, qty: string}>
     */
    function project_fund_material_lines_from_request(): array
    {
        $names = service('request')->getPost('material_item_name');
        $qtys  = service('request')->getPost('material_item_qty');
        if (! is_array($names)) {
            $names = [];
        }
        if (! is_array($qtys)) {
            $qtys = [];
        }

        $lines = [];
        $count = max(count($names), count($qtys));
        for ($i = 0; $i < $count; $i++) {
            $item = trim((string) ($names[$i] ?? ''));
            $qty  = trim((string) ($qtys[$i] ?? ''));
            if ($item === '' && $qty === '') {
                continue;
            }
            $lines[] = ['item' => $item, 'qty' => $qty];
        }

        return $lines;
    }
}

if (! function_exists('project_fund_mailto_url')) {
    /**
     * @param array<string, mixed> $project
     * @param 'budget'|'material' $kind
     */
    function project_fund_mailto_url(array $project, string $kind): string
    {
        $title = trim((string) ($project['title'] ?? ''));
        if ($kind === 'material') {
            $subject = 'Matériel — ' . $title;
            $body    = "Bonjour,\n\nJe souhaite apporter du matériel pour le projet : " . $title . ".\n\n";
            foreach (project_body_blocks_list($project) as $block) {
                if (! is_array($block) || (string) ($block['type'] ?? '') !== 'material_needs') {
                    continue;
                }
                foreach ($block['rows'] ?? [] as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $item = trim((string) ($row['item'] ?? ''));
                    if ($item === '') {
                        continue;
                    }
                    $qty = trim((string) ($row['quantity'] ?? ''));
                    $body .= '• ' . $item;
                    if ($qty !== '') {
                        $body .= ' (' . $qty . ')';
                    }
                    $body .= "\n";
                }
            }
            $body .= "\nMerci.";

            return 'mailto:partnerships@govgenz.org?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($body);
        }

        $subject = 'Financement — ' . $title;
        $budget  = trim((string) ($project['budget_display'] ?? ''));

        return 'mailto:partnerships@govgenz.org?subject=' . rawurlencode($subject)
            . ($budget !== '' ? '&body=' . rawurlencode("Budget indicatif : {$budget}\n\n") : '');
    }
}

if (! function_exists('project_fund_material_lines_from_old_input')) {
    /**
     * Lignes article / quantité pour réaffichage du formulaire (old input).
     *
     * @return list<array{item: string, qty: string}>
     */
    function project_fund_material_lines_from_old_input(): array
    {
        $names = old('material_item_name');
        $qtys  = old('material_item_qty');
        if (is_array($names)) {
            $lines = [];
            $count = max(count($names), is_array($qtys) ? count($qtys) : 0);
            for ($i = 0; $i < $count; $i++) {
                $item = trim((string) ($names[$i] ?? ''));
                $qty  = trim((string) (is_array($qtys) ? ($qtys[$i] ?? '') : ''));
                if ($item === '' && $qty === '') {
                    continue;
                }
                $lines[] = ['item' => $item, 'qty' => $qty];
            }

            return $lines !== [] ? $lines : [['item' => '', 'qty' => '']];
        }

        $legacyItem = trim((string) old('material_items', old('items', '')));
        $legacyQty  = trim((string) old('material_quantity', old('quantity', '')));
        if ($legacyItem !== '') {
            return [['item' => $legacyItem, 'qty' => $legacyQty]];
        }

        return [['item' => '', 'qty' => '']];
    }
}

if (! function_exists('project_published_material_parse_line')) {
    /**
     * @return array{item: string, qty: string}|null
     */
    function project_published_material_parse_line(string $chunk): ?array
    {
        $chunk = trim($chunk);
        if ($chunk === '') {
            return null;
        }
        if (preg_match('/^(.+?)\s*[—–-]\s*[×x]\s*(.+)$/u', $chunk, $m)) {
            return ['item' => trim($m[1]), 'qty' => trim($m[2])];
        }

        return ['item' => $chunk, 'qty' => ''];
    }
}

if (! function_exists('project_published_material_split_items_string')) {
    /**
     * @return list<string>
     */
    function project_published_material_split_items_string(string $items): array
    {
        $items = trim($items);
        if ($items === '') {
            return [];
        }
        if (preg_match('/\R/u', $items)) {
            $chunks = preg_split('/\R+/u', $items) ?: [];

            return array_values(array_filter(array_map('trim', $chunks), static fn (string $c): bool => $c !== ''));
        }
        if (preg_match_all('/\s*[—–-]\s*[×x]\s*/u', $items) > 1) {
            $parts = preg_split('/(?=\s+[^\s—–-][^—\n]*?\s*[—–-]\s*[×x]\s*)/u', $items) ?: [];
            if (count($parts) > 1) {
                return array_values(array_filter(array_map('trim', $parts), static fn (string $c): bool => $c !== ''));
            }
        }

        return [$items];
    }
}

if (! function_exists('project_fund_validate_material_lines')) {
    /**
     * @param list<array{item: string, qty: string}> $lines
     *
     * @return array<string, string>
     */
    function project_fund_validate_material_lines(array $lines): array
    {
        helper('language');
        if ($lines === []) {
            return ['material_items' => lang('Projects.fund_validation_items_required')];
        }

        foreach ($lines as $line) {
            $item = $line['item'];
            $qty  = $line['qty'];
            if ($item === '') {
                return ['material_items' => lang('Projects.fund_validation_items_required')];
            }
            if (mb_strlen($item) > 255) {
                return ['material_items' => lang('Projects.fund_validation_items_max')];
            }
            if ($qty === '') {
                return ['material_quantity' => lang('Projects.fund_validation_quantity_required')];
            }
            if (mb_strlen($qty) > 120) {
                return ['material_quantity' => lang('Projects.fund_validation_quantity_max')];
            }
        }

        return [];
    }
}

if (! function_exists('project_fund_validation_messages')) {
    /**
     * Messages de validation localisés — formulaire financement projet.
     *
     * @param 'budget'|'material' $type
     *
     * @return array<string, array<string, string>>
     */
    function project_fund_validation_messages(string $type): array
    {
        helper('language');
        $name = [
            'required'   => lang('Projects.fund_validation_name_required'),
            'max_length' => lang('Projects.fund_validation_name_max'),
        ];
        $phoneCountry = [
            'required'    => lang('Site.join_phone_country_required'),
            'regex_match' => lang('Site.join_phone_country_required'),
        ];
        $phoneNumber = [
            'required'    => lang('Projects.fund_validation_phone_required'),
            'max_length'  => lang('Projects.fund_validation_phone_max'),
            'regex_match' => lang('Site.join_phone_invalid'),
        ];
        $donorEmail = [
            'valid_email' => lang('Projects.fund_validation_email_invalid'),
            'max_length'  => lang('Projects.fund_validation_email_invalid'),
        ];
        $prefix = $type === 'material' ? 'material' : 'budget';

        if ($type === 'material') {
            return [
                'material_donor_name'      => $name,
                $prefix . '_phone_country' => $phoneCountry,
                $prefix . '_phone_number'  => $phoneNumber,
                'material_donor_email'     => $donorEmail,
                'material_pickup_location' => ['max_length' => lang('Projects.fund_validation_pickup_max')],
                'material_remarks'         => ['max_length' => lang('Projects.fund_validation_remarks_max')],
            ];
        }

        return [
            'budget_donor_name'      => $name,
            $prefix . '_phone_country' => $phoneCountry,
            $prefix . '_phone_number'  => $phoneNumber,
            'budget_donor_email'       => $donorEmail,
            'budget_amount'            => [
                'required'    => lang('Projects.fund_validation_amount_required'),
                'regex_match' => lang('Projects.fund_validation_amount_invalid'),
                'max_length'  => lang('Projects.fund_validation_amount_max'),
            ],
            'budget_remarks'           => ['max_length' => lang('Projects.fund_validation_remarks_max')],
        ];
    }
}

if (! function_exists('project_published_amount_display')) {
    function project_published_amount_display(string $amount, string $locale): string
    {
        $amount = trim($amount);
        if ($amount === '') {
            return '';
        }
        $compact = preg_replace('/\s+/u', '', $amount) ?? $amount;
        if ($compact !== '' && preg_match('/^\d+([.,]\d+)?$/', $compact)) {
            $num = (float) str_replace(',', '.', $compact);
            $dec = fmod($num, 1.0) === 0.0 ? 0 : 2;
            if ($locale === 'en') {
                return number_format($num, $dec, '.', ',');
            }

            return number_format($num, $dec, ',', ' ');
        }

        return $amount;
    }
}

