<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ProjectBudgetHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('project');
    }

    public function testSectorCodesFromCsv(): void
    {
        $codes = project_sector_codes_from_csv(' Health, EDU ,, ');

        $this->assertSame(['health', 'edu'], $codes);
    }

    public function testBudgetAriaryFromParts(): void
    {
        $ariary = project_budget_ariary_from_parts(2.5, \App\Models\ProjectProjectModel::BUDGET_SCALE_BILLION);

        $this->assertSame(2_500_000_000, $ariary);
    }

    public function testBudgetAriaryForProjectUsesStoredAriary(): void
    {
        $parsed = project_budget_ariary_for_project(['budget_ariary' => 1_500_000]);

        $this->assertSame(1_500_000.0, $parsed);
    }
}
