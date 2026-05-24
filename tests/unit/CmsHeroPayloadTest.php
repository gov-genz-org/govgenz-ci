<?php

declare(strict_types=1);

use App\Libraries\CmsHeroPayload;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\IncomingRequestFactory;

/**
 * @internal
 */
final class CmsHeroPayloadTest extends CIUnitTestCase
{
    public function testFromPostTrimsAndCasts(): void
    {
        $request = IncomingRequestFactory::withPost([
            'hero_overline'  => '  Over  ',
            'hero_title'     => '',
            'hero_lead'      => 'Lead',
            'hero_image_id'  => '12',
            'hero_image_alt' => '   ',
        ]);

        $payload = CmsHeroPayload::fromPost($request);

        $this->assertSame('Over', $payload['hero_overline']);
        $this->assertNull($payload['hero_title']);
        $this->assertSame('Lead', $payload['hero_lead']);
        $this->assertSame(12, $payload['hero_image_id']);
        $this->assertNull($payload['hero_image_alt']);
    }

    public function testFromPostRejectsInvalidImageId(): void
    {
        $request = IncomingRequestFactory::withPost(['hero_image_id' => '0']);

        $payload = CmsHeroPayload::fromPost($request);

        $this->assertNull($payload['hero_image_id']);
    }
}
