<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use Config\AdminRecordBlocks;

/**
 * @internal
 */
final class AdminRecordBlocksTest extends CIUnitTestCase
{
    private const VIEW_OPTIONS = ['saveData' => false];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['admin', 'form']);
        service('language')->setLocale('fr');
    }

    public function testBlockConfigKeepsProjectAndPositionCapabilitiesSeparate(): void
    {
        $config = config(AdminRecordBlocks::class);

        $this->assertContains('budget_table', $config->project);
        $this->assertContains('material_needs', $config->project);
        $this->assertContains('section_rich', $config->position);
        $this->assertContains('note_panel', $config->position);
        $this->assertNotContains('budget_table', $config->position);
        $this->assertNotContains('material_needs', $config->position);
    }

    public function testPositionBuilderDoesNotExposeProjectOnlyAddButtons(): void
    {
        $config = config(AdminRecordBlocks::class);
        $html = view('admin/record_blocks/builder', [
            'contentMode'        => 'blocks',
            'blocksForForm'      => [
                [
                    'type'          => 'budget_table',
                    'section_title' => 'Budget existant',
                    'rows'          => [
                        ['poste' => 'Poste', 'detail' => 'Détail', 'montant' => '100 000 Ar'],
                    ],
                ],
            ],
            'canUseAdvancedHtml' => true,
            'ppLocale'           => 'fr',
            'helpText'           => 'Aide test',
            'allowedBlockTypes'  => $config->position,
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString(lang('Admin.block_type_budget'), $html);
        $this->assertStringContainsString('Budget existant', $html);
        $this->assertStringNotContainsString('data-pp-add="budget_table"', $html);
        $this->assertStringNotContainsString('data-pp-proto="budget_table"', $html);
    }

    public function testSharedBuilderHidesHtmlPrototypeForEditorOnlyMode(): void
    {
        $html = view('admin/record_blocks/builder', [
            'contentMode'        => 'blocks',
            'blocksForForm'      => [],
            'canUseAdvancedHtml' => false,
            'ppLocale'           => 'fr',
            'helpText'           => '',
            'allowedBlockTypes'  => ['section_rich', 'html'],
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString('data-pp-add="section_rich"', $html);
        $this->assertStringNotContainsString('data-pp-add="html"', $html);
        $this->assertStringNotContainsString('data-pp-proto="html"', $html);
    }

    public function testRenderedBlocksExposeDragHandle(): void
    {
        $html = view('admin/record_blocks/builder', [
            'contentMode'        => 'blocks',
            'blocksForForm'      => [
                [
                    'type'    => 'section_rich',
                    'heading' => 'Contexte',
                ],
            ],
            'canUseAdvancedHtml' => true,
            'ppLocale'           => 'fr',
            'helpText'           => '',
            'allowedBlockTypes'  => ['section_rich'],
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString('project-block-drag-handle', $html);
        $this->assertStringContainsString('aria-label=', $html);
        $this->assertStringContainsString('draggable="true"', $html);
    }

    public function testProjectBlocksScriptReindexesAfterDragAndDrop(): void
    {
        $script = file_get_contents(ROOTPATH . 'public/js/admin/project-blocks-form.js');
        $this->assertIsString($script);

        $this->assertStringContainsString('function bindDrag', $script);
        $this->assertStringContainsString('dragstart', $script);
        $this->assertStringContainsString('dragover', $script);
        $this->assertStringContainsString('reindexBlocks(container)', $script);
        $this->assertStringContainsString('is-dragging', $script);
    }
}
