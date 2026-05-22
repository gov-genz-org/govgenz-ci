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

    public function testBodyBlocksJsonEncodesMetricsSection(): void
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
        $this->assertSame('metrics_section', $decoded[0]['type']);
        $this->assertSame('Chiffres clés', $decoded[0]['title']);
        $this->assertCount(1, $decoded[0]['metrics']);
        $this->assertSame('primary', $decoded[0]['actions'][0]['variant']);
    }
}
