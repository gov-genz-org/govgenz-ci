<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $events */
/** @var string $filterOutcome */
/** @var string $searchQuery */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */

$queryParams = array_filter([
    'outcome' => $filterOutcome !== 'all' ? $filterOutcome : null,
    'q'       => $searchQuery !== '' ? $searchQuery : null,
]);
$exportUrl = site_url('admin/login-events/export');
if ($queryParams !== []) {
    $exportUrl .= '?' . http_build_query($queryParams);
}
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_login_events')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_login_events')) ?></p>

<div class="d-flex flex-wrap align-items-end gap-2 gap-md-3 mb-3">
    <a href="<?= esc($exportUrl) ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_export_csv')) ?></a>
    <form method="post" action="<?= site_url('admin/login-events/clear-table') ?>"
          onsubmit="return confirm(<?= json_encode(lang('Admin.confirm_clear_login_log'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Admin.action_clear_table')) ?></button>
    </form>
    <form method="get" action="<?= site_url('admin/login-events') ?>" class="d-flex flex-wrap align-items-end gap-2 ms-md-auto">
        <?= admin_list_sort_hidden_fields($sort, $dir) ?>
        <div>
            <label class="small text-muted mb-0 d-block" for="le-search-q"><?= esc(lang('Admin.filter_email_fragment')) ?></label>
            <input type="search" name="q" id="le-search-q" value="<?= esc($searchQuery) ?>" class="form-control form-control-sm" placeholder="<?= esc(lang('Admin.placeholder_search'), 'attr') ?>" maxlength="120" autocomplete="off">
        </div>
        <div>
            <label class="small text-muted mb-0 d-block" for="le-outcome"><?= esc(lang('Admin.filter_outcome')) ?></label>
            <select name="outcome" id="le-outcome" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                <option value="" <?= $filterOutcome === 'all' ? 'selected' : '' ?>><?= esc(lang('Admin.filter_all_masc')) ?></option>
                <option value="success" <?= $filterOutcome === 'success' ? 'selected' : '' ?>><?= esc(lang('Admin.outcome_success')) ?></option>
                <option value="failure" <?= $filterOutcome === 'failure' ? 'selected' : '' ?>><?= esc(lang('Admin.outcome_failure')) ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.action_filter')) ?></button>
    </form>
</div>

<?php if ($events === []) : ?>
    <div class="admin-empty">
        <p class="mb-0 text-muted"><?= esc(lang('Admin.empty_no_login_events')) ?></p>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0 small">
    <thead class="table-light">
        <tr>
            <th><?= admin_list_sort_th('created_at', lang('Admin.col_date'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('outcome', lang('Admin.col_outcome'), $sort, $dir) ?></th>
            <th><?= esc(lang('Admin.col_detail')) ?></th>
            <th><?= admin_list_sort_th('email_attempt', lang('Admin.col_email'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('staff_user_id', lang('Admin.col_staff_id'), $sort, $dir) ?></th>
            <th><?= esc(lang('Admin.col_ip')) ?></th>
            <th><?= esc(lang('Admin.col_browser')) ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $ev) :
        $ua = (string) ($ev['user_agent'] ?? '');
        $uaShort = mb_strlen($ua) > 72 ? mb_substr($ua, 0, 69) . '…' : $ua;
        ?>
        <tr>
            <td class="text-nowrap"><?= admin_format_datetime($ev['created_at'] ?? null) ?></td>
            <td><span class="badge <?= (($ev['outcome'] ?? '') === 'success') ? 'text-bg-success' : 'text-bg-warning' ?>"><?= esc((string) ($ev['outcome'] ?? '')) ?></span></td>
            <td><?= esc((string) ($ev['detail'] ?? '')) ?></td>
            <td><?= esc((string) ($ev['email_attempt'] ?? '')) ?></td>
            <td><?= esc((string) ($ev['staff_user_id'] ?? '')) ?></td>
            <td class="text-nowrap"><code><?= esc((string) ($ev['ip_address'] ?? '')) ?></code></td>
            <td title="<?= esc($ua, 'attr') ?>"><?= esc($uaShort) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_events')]) ?>
<?php endif; ?>
