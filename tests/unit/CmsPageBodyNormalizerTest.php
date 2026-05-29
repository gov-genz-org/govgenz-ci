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
}
