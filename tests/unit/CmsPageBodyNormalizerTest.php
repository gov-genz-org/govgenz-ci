<?php

declare(strict_types=1);

use App\Libraries\CmsPageBodyNormalizer;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\IncomingRequestFactory;

/**
 * @internal
 */
final class CmsPageBodyNormalizerTest extends CIUnitTestCase
{
    public function testContentModeDefaultsToHtml(): void
    {
        $request = IncomingRequestFactory::withPost([]);

        $this->assertSame('html', CmsPageBodyNormalizer::contentMode($request));

        $request = IncomingRequestFactory::withPost(['content_mode' => 'blocks']);
        $this->assertSame('blocks', CmsPageBodyNormalizer::contentMode($request));
    }

    public function testBodyBlocksJsonFiltersEmptyHtml(): void
    {
        $request = IncomingRequestFactory::withPost([
            'content_mode' => 'blocks',
            'blocks'       => [
                ['type' => 'html', 'html' => '  '],
                ['type' => 'html', 'html' => '<p>OK</p>'],
                ['type' => 'unknown'],
            ],
        ]);

        $json = CmsPageBodyNormalizer::bodyBlocksJson($request);
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertCount(1, $decoded);
        $this->assertSame('html', $decoded[0]['type']);
    }

    public function testBodyBlocksJsonEncodesStatsGridFromLegacyMetricsSection(): void
    {
        $request = IncomingRequestFactory::withPost([
            'content_mode' => 'blocks',
            'blocks'       => [
                [
                    'type'    => 'metrics_section',
                    'title'   => 'Chiffres clés',
                    'metrics' => [
                        ['value' => '10', 'label' => 'Projets'],
                    ],
                    'actions' => [
                        ['label' => 'Voir', 'href' => '/contact', 'variant' => 'primary'],
                    ],
                ],
            ],
        ]);

        $json    = CmsPageBodyNormalizer::bodyBlocksJson($request);
        $decoded = json_decode((string) $json, true);
        $this->assertIsArray($decoded);
        $this->assertSame('stats_grid', $decoded[0]['type']);
        $this->assertSame('Chiffres clés', $decoded[0]['title']);
        $this->assertCount(1, $decoded[0]['stats']);
        $this->assertSame('primary', $decoded[0]['actions'][0]['variant']);
    }

    public function testBodyBlocksJsonEncodesMigrablePageBlocks(): void
    {
        $request = IncomingRequestFactory::withPost([
            'content_mode' => 'blocks',
            'blocks'       => [
                [
                    'type'       => 'section_text',
                    'title'      => 'Qui sommes-nous',
                    'paragraphs' => ['Notre mission', ''],
                    'bullets'    => ['Écoute', 'Action'],
                ],
                [
                    'type'    => 'cards_grid',
                    'variant' => 'pillar_cards',
                    'cards'   => [
                        [
                            'eyebrow'      => 'Pilier',
                            'title'        => 'Éducation',
                            'bullets_text' => "Former\nAccompagner",
                        ],
                    ],
                ],
                [
                    'type'     => 'legal_prose',
                    'sections' => [
                        [
                            'heading'      => 'Éditeur',
                            'body'         => 'GovGenZ',
                            'bullets_text' => "Contact\nHébergement",
                        ],
                    ],
                ],
            ],
        ]);

        $json = CmsPageBodyNormalizer::bodyBlocksJson($request);
        $decoded = json_decode((string) $json, true);

        $this->assertIsArray($decoded);
        $this->assertCount(3, $decoded);
        $this->assertSame('section_text', $decoded[0]['type']);
        $this->assertSame(['Écoute', 'Action'], $decoded[0]['bullets']);
        $this->assertSame('cards_grid', $decoded[1]['type']);
        $this->assertSame(['Former', 'Accompagner'], $decoded[1]['cards'][0]['bullets']);
        $this->assertSame('legal_prose', $decoded[2]['type']);
        $this->assertSame(['Contact', 'Hébergement'], $decoded[2]['sections'][0]['bullets']);
    }

    public function testFooterColumnsBlockNormalizesColumnsAndSoonLinks(): void
    {
        $request = IncomingRequestFactory::withPost([
            'content_mode' => 'blocks',
            'blocks'       => [
                [
                    'type'    => 'footer_columns',
                    'columns' => [
                        [
                            'title' => 'Le mouvement',
                            'links' => [
                                ['label' => 'Contact', 'href' => '/contact', 'soon' => '0'],
                                ['label' => 'declaration.govgenz.org', 'href' => '/x', 'soon' => '1'],
                                ['label' => '  ', 'href' => '/skip'],
                            ],
                        ],
                        ['title' => '', 'links' => []],
                    ],
                ],
            ],
        ]);

        $decoded = json_decode((string) CmsPageBodyNormalizer::bodyBlocksJson($request), true);
        $this->assertIsArray($decoded);
        $this->assertCount(1, $decoded);
        $this->assertSame('footer_columns', $decoded[0]['type']);
        $this->assertCount(1, $decoded[0]['columns']);
        $this->assertSame('Le mouvement', $decoded[0]['columns'][0]['title']);
        $this->assertCount(2, $decoded[0]['columns'][0]['links']);
        $this->assertSame('/contact', $decoded[0]['columns'][0]['links'][0]['href']);
        $this->assertSame(0, $decoded[0]['columns'][0]['links'][0]['soon']);
        $this->assertSame('', $decoded[0]['columns'][0]['links'][1]['href']);
        $this->assertSame(1, $decoded[0]['columns'][0]['links'][1]['soon']);
    }

    public function testBodyBlocksJsonPreservesSectorsGridLayout(): void
    {
        $request = IncomingRequestFactory::withPost([
            'content_mode' => 'blocks',
            'blocks'       => [
                [
                    'type'            => 'sectors_grid',
                    'layout'          => 'wide',
                    'kicker'          => 'Équipes de terrain',
                    'title'           => '14 Équipes sectorielles',
                    'lead'            => 'Chapô test',
                    'banner_title'    => 'Bandeau',
                    'banner_subtitle' => '14 secteurs',
                ],
            ],
        ]);

        $decoded = json_decode((string) CmsPageBodyNormalizer::bodyBlocksJson($request), true);
        $this->assertIsArray($decoded);
        $this->assertSame('sectors_grid', $decoded[0]['type']);
        $this->assertSame('wide', $decoded[0]['layout']);
        $this->assertSame('Équipes de terrain', $decoded[0]['kicker']);
        $this->assertSame('14 Équipes sectorielles', $decoded[0]['title']);
        $this->assertSame('Bandeau', $decoded[0]['banner_title']);
        $this->assertSame('14 secteurs', $decoded[0]['banner_subtitle']);
    }

    public function testBodyBlocksJsonPreservesStructuresGridIntro(): void
    {
        $request = IncomingRequestFactory::withPost([
            'content_mode' => 'blocks',
            'blocks'       => [
                [
                    'type'            => 'structures_grid',
                    'layout'          => 'dept',
                    'kicker'          => 'Organigramme',
                    'title'           => 'Structure organisationnelle',
                    'lead'            => 'Chapô test',
                    'banner_title'    => 'Bandeau',
                    'banner_subtitle' => '7 départements',
                ],
            ],
        ]);

        $decoded = json_decode((string) CmsPageBodyNormalizer::bodyBlocksJson($request), true);
        $this->assertIsArray($decoded);
        $this->assertSame('structures_grid', $decoded[0]['type']);
        $this->assertSame('dept', $decoded[0]['layout']);
        $this->assertSame('Organigramme', $decoded[0]['kicker']);
        $this->assertSame('Structure organisationnelle', $decoded[0]['title']);
        $this->assertSame('Bandeau', $decoded[0]['banner_title']);
    }
}
