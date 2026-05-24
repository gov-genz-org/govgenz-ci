<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminFormHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('admin');
    }

    public function testJunkRepeatLineDetection(): void
    {
        $this->assertTrue(admin_pp_is_junk_repeat_line('Retirer'));
        $this->assertTrue(admin_pp_is_junk_repeat_line('×'));
        $this->assertFalse(admin_pp_is_junk_repeat_line('Vrai contenu'));
    }

    public function testRepeatScalarLinesFiltersJunkAndAddsTrailingEmpty(): void
    {
        $lines = admin_pp_repeat_scalar_lines(['OK', 'retirer', '  ']);

        $this->assertSame(['OK', ''], $lines);
    }

    public function testRepeatObjectRowsKeepsNonEmptyPlusTemplate(): void
    {
        $rows = admin_pp_repeat_object_rows(
            [['label' => 'A'], ['label' => '']],
            static fn (array $row): bool => trim((string) ($row['label'] ?? '')) === '',
            ['label' => ''],
        );

        $this->assertCount(2, $rows);
        $this->assertSame('A', $rows[0]['label']);
        $this->assertSame('', $rows[1]['label']);
    }
}
