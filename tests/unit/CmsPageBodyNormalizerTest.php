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
}
