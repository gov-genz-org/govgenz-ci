<?php

declare(strict_types=1);

use App\Libraries\ProgramListFilter;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ProgramListFilterTest extends CIUnitTestCase
{
    public function testSanitizeListRejectsUnknownAndDedupes(): void
    {
        $out = ProgramListFilter::sanitizeList(['Actif', 'actif', 'bogus'], ['actif', 'complete']);

        $this->assertSame(['actif'], $out);
    }

    public function testFilterBySectorsRequiresIntersection(): void
    {
        $rows = [
            ['id' => 1, 'sectors_csv' => 'health, edu'],
            ['id' => 2, 'sectors_csv' => 'climate'],
        ];

        $filtered = ProgramListFilter::filterBySectors($rows, ['edu']);

        $this->assertCount(1, $filtered);
        $this->assertSame(1, $filtered[0]['id']);
    }

    public function testFilterByExactField(): void
    {
        $rows = [
            ['project_status' => 'actif'],
            ['project_status' => 'complete'],
        ];

        $filtered = ProgramListFilter::filterByExactField($rows, ['actif'], 'project_status');

        $this->assertCount(1, $filtered);
        $this->assertSame('actif', $filtered[0]['project_status']);
    }
}
