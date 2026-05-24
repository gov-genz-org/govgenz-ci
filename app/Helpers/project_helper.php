<?php

declare(strict_types=1);

helper(['cms']);

foreach ([
    __DIR__ . '/project_partial_admin_budget.php',
    __DIR__ . '/project_partial_budget.php',
    __DIR__ . '/project_partial_blocks.php',
    __DIR__ . '/project_partial_fund.php',
    __DIR__ . '/project_partial_urls.php',
    __DIR__ . '/project_partial_share.php',
    __DIR__ . '/project_partial_render.php',
] as $partial) {
    require_once $partial;
}
