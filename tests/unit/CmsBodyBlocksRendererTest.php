<?php

declare(strict_types=1);

use App\Libraries\CmsBodyBlocksRenderer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CmsBodyBlocksRendererTest extends CIUnitTestCase
{
    public function testRenderIgnoresUnknownBlockTypes(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            ['type' => 'unknown_block'],
            ['type' => 'html', 'html' => '<p>OK</p>'],
        ]);

        $this->assertSame('<p>OK</p>', $html);
    }

    public function testSectionTextRendersEditorialProse(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            [
                'type'       => 'section_text',
                'title'      => 'Mission',
                'paragraphs' => ['Premier paragraphe'],
                'bullets'    => ['Point A'],
            ],
        ]);

        $this->assertStringContainsString('section--cms-text', $html);
        $this->assertStringContainsString('ggz-editorial-prose', $html);
        $this->assertStringContainsString('Premier paragraphe', $html);
        $this->assertStringContainsString('<li>Point A</li>', $html);
    }

    public function testSimpleCardsGridUsesGenericCardRow(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            [
                'type'    => 'cards_grid',
                'variant' => 'simple_cards',
                'cards'   => [
                    ['title' => 'Education', 'href' => '/contact', 'description' => 'Desc'],
                    ['title' => 'Sante'],
                ],
            ],
        ]);

        $this->assertStringContainsString('ggz-cards-row', $html);
        $this->assertStringContainsString('class="card"', $html);
        $this->assertStringContainsString('contact', $html);
        $this->assertStringContainsString('<a ', $html);
        $this->assertStringContainsString('Education', $html);
        $this->assertStringContainsString('Sante', $html);
        $this->assertStringNotContainsString('adn-card', $html);
    }

    public function testPillarCardsGridUsesAdnLayout(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            [
                'type'    => 'cards_grid',
                'variant' => 'pillar_cards',
                'cards'   => [
                    [
                        'eyebrow' => 'Pilier',
                        'title'   => 'Education',
                        'bullets' => ['Former', 'Accompagner'],
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('section--adn', $html);
        $this->assertStringContainsString('class="adn"', $html);
        $this->assertStringContainsString('adn-card', $html);
        $this->assertStringNotContainsString('ggz-cards-row', $html);
    }

    public function testCircleCardsGridUsesCerclesLayout(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            [
                'type'    => 'cards_grid',
                'variant' => 'circle_cards',
                'cards'   => [
                    [
                        'value' => '12',
                        'unit'  => 'M',
                        'title' => 'Enfants',
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('section--qui', $html);
        $this->assertStringContainsString('class="cercles"', $html);
        $this->assertStringContainsString('cercle__number', $html);
        $this->assertStringContainsString('Enfants', $html);
    }

    public function testStatsGridRendersFromLegacyMetricsKey(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            [
                'type'    => 'metrics_section',
                'title'   => 'Chiffres',
                'metrics' => [
                    ['value' => '42', 'label' => 'Projets'],
                ],
            ],
        ]);

        $this->assertStringContainsString('section--etude', $html);
        $this->assertStringContainsString('class="stats"', $html);
        $this->assertStringContainsString('stat__num', $html);
        $this->assertStringContainsString('42', $html);
        $this->assertStringContainsString('Projets', $html);
    }

    public function testCtaPanelRendersActions(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            [
                'type'    => 'cta_panel',
                'text'    => 'Participez',
                'actions' => [
                    ['label' => 'Contact', 'href' => '/contact', 'variant' => 'primary'],
                ],
            ],
        ]);

        $this->assertStringContainsString('section--cms-cta', $html);
        $this->assertStringContainsString('Participez', $html);
        $this->assertStringContainsString('contact', $html);
        $this->assertStringContainsString('btn--primary', $html);
    }

    public function testEmptyBlocksProduceNoOutput(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            ['type' => 'section_text', 'title' => '', 'paragraphs' => [], 'bullets' => []],
            ['type' => 'cards_grid', 'variant' => 'simple_cards', 'cards' => []],
        ]);

        $this->assertSame('', $html);
    }

    public function testFooterColumnsRendersFooterColMarkup(): void
    {
        $html = CmsBodyBlocksRenderer::render([
            [
                'type'    => 'footer_columns',
                'columns' => [
                    [
                        'title' => 'Contacts',
                        'links' => [
                            ['label' => 'contact@govgenz.org', 'href' => 'mailto:contact@govgenz.org', 'soon' => 0],
                            ['label' => 'declaration.govgenz.org', 'href' => '', 'soon' => 1],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertStringContainsString('footer__col', $html);
        $this->assertStringContainsString('<h4>Contacts</h4>', $html);
        $this->assertStringContainsString('mailto:contact@govgenz.org', $html);
        $this->assertStringContainsString('footer__soon', $html);
        $this->assertStringContainsString('declaration.govgenz.org', $html);
    }
}
