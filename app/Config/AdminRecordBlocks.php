<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Blocs ajoutables dans les builders admin. La vue partagée peut recevoir ces
 * listes depuis une future table back-office sans changer le contrat HTML/JS.
 */
class AdminRecordBlocks extends BaseConfig
{
    /** @var list<string> */
    public array $project = [
        'section_rich',
        'budget_table',
        'material_needs',
        'timeline',
        'kpi_grid',
        'impact_tracker',
        'note_panel',
        'team',
        'sources',
        'html',
    ];

    /** @var list<string> */
    public array $position = [
        'section_rich',
        'note_panel',
        'sources',
        'html',
    ];
}
