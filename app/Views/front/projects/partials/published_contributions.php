<?php

declare(strict_types=1);

helper(['language', 'project']);

use App\Models\ProjectContributionModel;

/** @var list<array<string, mixed>> $contributions */
$contributions = $contributions ?? [];
if ($contributions === []) {
    return;
}

$locale = service('request')->getLocale();
?>
<section id="project-contributions" class="content-section project-published-contributions" aria-labelledby="project-contributions-heading">
    <h2 id="project-contributions-heading" class="content-title teal"><?= esc(lang('Projects.fund_published_title')) ?></h2>
    <table class="budget-table">
        <thead>
            <tr>
                <th scope="col"><?= esc(lang('Projects.fund_published_col_name')) ?></th>
                <th scope="col"><?= esc(lang('Projects.fund_published_col_type')) ?></th>
                <th scope="col"><?= esc(lang('Projects.fund_published_col_detail')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contributions as $row) :
                $ctype = (string) ($row['contribution_type'] ?? '');
                $name  = trim((string) ($row['donor_name'] ?? ''));
                $displayName = $name !== '' ? $name : lang('Projects.fund_published_anonymous');
                $typeLabel = $ctype === ProjectContributionModel::TYPE_MATERIAL
                    ? lang('Projects.fund_published_type_material')
                    : lang('Projects.fund_published_type_budget');
                $isMaterial = $ctype === ProjectContributionModel::TYPE_MATERIAL;
                $rowClass = $isMaterial ? 'contribution-row--material' : 'contribution-row--budget';
                ?>
                <tr class="<?= esc($rowClass, 'attr') ?>">
                    <td><?= esc($displayName) ?></td>
                    <td><?= esc($typeLabel) ?></td>
                    <td>
                        <?php if ($isMaterial) :
                            $materialLines = project_published_material_lines_from_row($row);
                            if ($materialLines === []) {
                                echo '—';
                            } else {
                                $parts = [];
                                foreach ($materialLines as $line) {
                                    $itemLabel = trim((string) ($line['item'] ?? ''));
                                    $qtyLabel  = trim((string) ($line['qty'] ?? ''));
                                    if ($itemLabel === '') {
                                        continue;
                                    }
                                    $parts[] = $qtyLabel !== ''
                                        ? $itemLabel . ' ×' . $qtyLabel
                                        : $itemLabel;
                                }
                                if ($parts === []) {
                                    echo '—';
                                } else {
                                    echo nl2br(esc(implode("\n", $parts)), false);
                                }
                            }
                        else :
                            $amount = trim((string) ($row['amount'] ?? ''));
                            if ($amount !== '') {
                                echo esc(project_published_amount_display($amount, $locale));
                            } else {
                                echo '—';
                            }
                        endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
