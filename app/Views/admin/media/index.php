<?php

declare(strict_types=1);

helper('admin');

/** @var list<array<string, mixed>> $items */
/** @var int $totalMedia */
/** @var \CodeIgniter\Pager\Pager $pager */
/** @var string $sort */
/** @var string $dir */
?>
<h1 class="h3 mb-1"><?= esc(lang('Admin.title_media')) ?></h1>
<p class="text-muted small mb-3"><?= esc(lang('Admin.help_media_intro')) ?></p>

<form id="admin-media-csrf" class="d-none"><?= csrf_field() ?></form>
<div id="media-dropzone" class="dropzone border rounded mb-4 p-3 bg-white"></div>

<?php if ($totalMedia === 0) : ?>
    <div class="admin-empty">
        <p class="mb-2 text-muted"><?= esc(lang('Admin.empty_no_media_files')) ?></p>
        <p class="small text-muted mb-3"><?= esc(lang('Admin.help_media_dropzone')) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= site_url('admin/pages/create') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.breadcrumb_page_new')) ?></a>
            <a href="<?= site_url('admin/posts/create') ?>" class="btn btn-outline-secondary btn-sm"><?= esc(lang('Admin.breadcrumb_post_new')) ?></a>
        </div>
    </div>
<?php else : ?>
<div class="row mb-3 g-2 align-items-end">
    <div class="col-md-6 col-lg-4">
        <label class="form-label small text-muted mb-1" for="media-search"><?= esc(lang('Admin.form_media_filter_name')) ?></label>
        <input type="search" class="form-control form-control-sm" id="media-search" placeholder="<?= esc(lang('Admin.placeholder_media_grid'), 'attr') ?>" autocomplete="off">
    </div>
    <div class="col-auto ms-md-auto small text-muted d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted"><?= esc(lang('Admin.label_sort_colon')) ?></span>
        <?= admin_list_sort_th('id', lang('Admin.col_added'), $sort, $dir) ?>
        <?= admin_list_sort_th('original_name', lang('Admin.col_name'), $sort, $dir) ?>
        <?= admin_list_sort_th('size_bytes', lang('Admin.col_size'), $sort, $dir) ?>
    </div>
</div>
    <div id="media-library-grid" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
        <?php foreach ($items as $row) :
            $mime = (string) ($row['mime_type'] ?? '');
            $url  = base_url('uploads/cms/' . ($row['stored_filename'] ?? ''));
            $name = (string) ($row['original_name'] ?? '');
            $needle = mb_strtolower($name . ' ' . $mime);
            $isImg = str_starts_with($mime, 'image/');
            ?>
            <div class="col js-media-item" data-filter="<?= esc($needle) ?>">
                <div class="card h-100 shadow-sm">
                    <?php if ($isImg) : ?>
                        <a href="<?= esc($url) ?>" target="_blank" rel="noopener" class="ratio ratio-4x3 bg-light">
                            <img src="<?= esc($url) ?>" alt="" class="card-img-top object-fit-contain p-2" loading="lazy">
                        </a>
                    <?php else : ?>
                        <div class="card-body d-flex align-items-center justify-content-center bg-light ratio ratio-4x3">
                            <span class="text-muted small text-center px-2">PDF<br><?= esc($name) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="card-body py-2 px-3">
                        <div class="small text-truncate" title="<?= esc($name) ?>"><?= esc($name) ?></div>
                        <div class="small text-muted"><?= esc(number_format((int) ($row['size_bytes'] ?? 0))) ?> o</div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm js-copy-media-url" data-url="<?= esc($url, 'attr') ?>"><?= esc(lang('Admin.action_copy_url')) ?></button>
                            <a href="<?= esc($url) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><?= esc(lang('Admin.action_open')) ?></a>
                            <form action="<?= site_url('admin/media/delete/' . (int) ($row['id'] ?? 0)) ?>" method="post" class="d-inline js-confirm-submit" data-confirm-message="<?= esc(lang('Admin.confirm_delete_media'), 'attr') ?>">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm"><?= esc(lang('Admin.action_delete')) ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?= view('admin/partials/list_pager', ['pager' => $pager, 'resultLabel' => lang('Admin.pager_files')]) ?>
<script defer src="<?= base_url('js/admin/media-library-grid.js') ?>"></script>
<?php endif; ?>
