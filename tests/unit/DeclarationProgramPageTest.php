<?php

declare(strict_types=1);

use App\Libraries\DeclarationProgramPage;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DeclarationProgramPageTest extends CIUnitTestCase
{
    public function testPartitionKeepsEditorialBlocksForFront(): void
    {
        $partition = DeclarationProgramPage::partitionBlocks([
            ['type' => 'cta_panel', 'text' => 'Contact us', 'actions' => [['label' => 'X', 'href' => '/x']]],
            ['type' => 'section_text', 'bullets' => ['Trust line']],
            [
                'type'  => 'stats_grid',
                'stats' => [['value' => '3', 'suffix' => '', 'label' => 'Déclarations']],
            ],
            ['type' => 'metrics_section', 'metrics' => [['value' => '14', 'suffix' => '', 'label' => 'Secteurs']]],
            ['type' => 'html', 'html' => '<span id="declarations"></span>'],
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [['title' => 'Carte']],
            ],
        ]);

        $this->assertSame([], $partition['stats']);
        $this->assertCount(6, $partition['body']);
        $this->assertSame('cta_panel', $partition['body'][0]['type']);
        $this->assertSame('metrics_section', $partition['body'][3]['type']);
    }

    public function testPartitionSkipsDeclarationCardGridsOnly(): void
    {
        $partition = DeclarationProgramPage::partitionBlocks([
            [
                'type'    => 'cards_grid',
                'variant' => 'declaration_cards',
                'cards'   => [['title' => 'Legacy']],
            ],
            ['type' => 'section_text', 'paragraphs' => ['Organigramme']],
        ]);

        $this->assertCount(1, $partition['body']);
        $this->assertSame('section_text', $partition['body'][0]['type']);
    }

    public function testSplitStaticPlacesEthicsAndCtaAfterPartnerships(): void
    {
        $split = DeclarationProgramPage::splitStaticAroundPartnerships([
            ['type' => 'section_text', 'paragraphs' => ['Organigramme']],
            ['type' => 'legal_prose', 'presentation' => 'accordion', 'sections' => []],
            ['type' => 'cta_panel', 'text' => 'Question ?'],
        ]);

        $this->assertCount(1, $split['before']);
        $this->assertSame('section_text', $split['before'][0]['type']);
        $this->assertCount(2, $split['after']);
        $this->assertSame('legal_prose', $split['after'][0]['type']);
        $this->assertSame('cta_panel', $split['after'][1]['type']);
    }

    public function testSplitIndexAtFindsFirstEthicsOrCtaBlock(): void
    {
        $blocks = [
            ['type' => 'section_text'],
            ['type' => 'structures_grid', 'layout' => 'dept'],
            ['type' => 'legal_prose'],
            ['type' => 'cta_panel'],
        ];

        $this->assertSame(2, DeclarationProgramPage::splitIndexAt($blocks));
        $this->assertSame(['before', 'before', 'after', 'after'], array_values(DeclarationProgramPage::blockZoneLabels($blocks)));
    }
}
