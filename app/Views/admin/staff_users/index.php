<?php

declare(strict_types=1);

use App\Libraries\StaffInvite;

helper('admin');

/** @var list<array<string, mixed>> $users */
/** @var array<string, string> $roles */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */
/** @var bool $hasNotifyColumn */
$hasNotifyColumn = $hasNotifyColumn ?? false;
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_staff')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_staff_index')) ?></p>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <a href="<?= site_url('admin/staff-users/create') ?>" class="btn btn-primary btn-sm"><?= esc(lang('Admin.action_invite_account')) ?></a>
    <?php if ($users !== []) : ?>
        <form method="post" action="<?= site_url('admin/staff-users/clear-table') ?>" class="ms-md-auto"
              onsubmit="return confirm(<?= json_encode(lang('Admin.confirm_clear_staff_table'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Admin.action_clear_table')) ?></button>
        </form>
    <?php endif; ?>
</div>

<?php if ($users === []) : ?>
    <div class="admin-empty">
        <p class="mb-0 text-muted"><?= esc(lang('Admin.empty_no_staff')) ?></p>
    </div>
<?php else : ?>
<div class="table-responsive admin-table-wrap shadow-sm rounded border bg-white">
<table class="table table-striped align-middle mb-0">
    <thead class="table-light">
        <tr>
            <th><?= admin_list_sort_th('email', lang('Admin.col_email'), $sort, $dir) ?></th>
            <th><?= admin_list_sort_th('role', lang('Admin.col_role'), $sort, $dir) ?></th>
            <th><?= esc(lang('Admin.col_activation')) ?></th>
            <th><?= admin_list_sort_th('is_active', lang('Admin.col_state'), $sort, $dir) ?></th>
            <?php if ($hasNotifyColumn) : ?>
            <th><?= esc(lang('Admin.col_notifications')) ?></th>
            <?php endif; ?>
            <th class="text-end"><?= esc(lang('Admin.col_actions')) ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u) :
        $rid = (string) ($u['role'] ?? '');
        $roleLabel = $roles[$rid] ?? $rid;
        $active = (int) ($u['is_active'] ?? 1) === 1;
        $uid = (int) ($u['id'] ?? 0);
        $isSelf = $uid === (int) session()->get('staff_user_id');
        $pending = StaffInvite::isPending($u);
        $expired = StaffInvite::isExpired($u);
        $notifyOn = (int) ($u['notify_form_submissions'] ?? 1) === 1;
        ?>
        <tr>
            <td><?= esc((string) ($u['email'] ?? '')) ?></td>
            <td><span class="badge text-bg-secondary"><?= esc($roleLabel) ?></span></td>
            <td>
                <?php if ($pending) : ?>
                    <span class="badge text-bg-warning text-dark"><?= esc(lang('Admin.badge_invite_pending')) ?></span>
                <?php elseif ($expired) : ?>
                    <span class="badge text-bg-danger"><?= esc(lang('Admin.badge_link_expired')) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-success"><?= esc(lang('Admin.badge_activated')) ?></span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($active) : ?>
                    <span class="badge text-bg-success"><?= esc(lang('Admin.form_staff_active_on')) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-secondary"><?= esc(lang('Admin.form_staff_active_off')) ?></span>
                <?php endif; ?>
            </td>
            <?php if ($hasNotifyColumn) : ?>
            <td>
                <?php if ($notifyOn) : ?>
                    <span class="badge text-bg-info"><?= esc(lang('Admin.badge_emails_on')) ?></span>
                <?php else : ?>
                    <span class="badge text-bg-secondary"><?= esc(lang('Admin.badge_emails_off')) ?></span>
                <?php endif; ?>
                <form method="post" action="<?= site_url('admin/staff-users/toggle-notify/' . $uid) ?>" class="d-inline mt-1">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <?= esc($notifyOn ? lang('Admin.action_disable_notifications') : lang('Admin.action_enable_notifications')) ?>
                    </button>
                </form>
            </td>
            <?php endif; ?>
            <td class="text-end text-nowrap">
                <a href="<?= site_url('admin/staff-users/edit/' . $uid) ?>" class="btn btn-outline-primary btn-sm"><?= esc(lang('Admin.action_modify')) ?></a>
                <?php if (! $isSelf) : ?>
                    <form method="post" action="<?= site_url('admin/staff-users/delete/' . $uid) ?>" class="d-inline ms-1"
                          onsubmit="return confirm(<?= json_encode(lang('Admin.confirm_delete_staff'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Admin.action_delete')) ?></button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_accounts')]) ?>
<?php endif; ?>
