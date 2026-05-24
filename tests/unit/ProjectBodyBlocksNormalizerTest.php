<?php

declare(strict_types=1);

use App\Libraries\ProjectBodyBlocksNormalizer;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\IncomingRequestFactory;

/**
 * @internal
 */
final class ProjectBodyBlocksNormalizerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('admin');
    }

    public function testBodyBlocksJsonEncodesSectionRich(): void
    {
        $request = IncomingRequestFactory::withPost([
            'body_content_mode' => 'blocks',
            'blocks'            => [
                [
                    'type'    => 'section_rich',
                    'heading' => 'Contexte',
                    'bullets' => ['Point A', ''],
                ],
                [
                    'type' => 'html',
                    'html' => '<p>skip in strict mode below</p>',
                ],
            ],
        ]);

        $json = ProjectBodyBlocksNormalizer::bodyBlocksJson($request);
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);
        $this->assertSame('section_rich', $decoded[0]['type']);
        $this->assertSame('Contexte', $decoded[0]['heading']);
    }

    public function testBodyBlocksJsonIgnoringModeStripsHtmlBlocks(): void
    {
        $request = IncomingRequestFactory::withPost([
            'blocks' => [
                [
                    'type'    => 'note_panel',
                    'message' => 'Texte encadré',
                ],
                [
                    'type' => 'html',
                    'html' => '<script>x</script>',
                ],
            ],
        ]);

        $json = ProjectBodyBlocksNormalizer::bodyBlocksJsonIgnoringMode($request);
        $decoded = json_decode((string) $json, true);
        $this->assertIsArray($decoded);
        $this->assertCount(1, $decoded);
        $this->assertSame('note_panel', $decoded[0]['type']);
    }

    public function testContentModeHtmlReturnsNullBlocks(): void
    {
        $request = IncomingRequestFactory::withPost(['body_content_mode' => 'html']);

        $this->assertNull(ProjectBodyBlocksNormalizer::bodyBlocksJson($request));
    }
}
