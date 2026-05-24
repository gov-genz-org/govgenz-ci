<?php

declare(strict_types=1);

use App\Libraries\AdminRecordPreview;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\IncomingRequestFactory;

/**
 * @internal
 */
final class AdminRecordPreviewTest extends CIUnitTestCase
{
    public function testMergePositionFromPostReturnsNullWhenBodyPayloadMissing(): void
    {
        helper('admin');
        $request = IncomingRequestFactory::withPost([
            'body_content_mode' => 'blocks',
            'blocks'            => '[]',
        ]);

        $merged = AdminRecordPreview::mergePositionFromPost(
            ['id' => 1, 'locale' => 'fr', 'slug' => 'test', 'body_content_mode' => 'blocks'],
            $request,
        );

        $this->assertNull($merged);
    }

    public function testAdminRecordPreviewClassIsLoadable(): void
    {
        $this->assertTrue(class_exists(AdminRecordPreview::class));
        $this->assertTrue(method_exists(AdminRecordPreview::class, 'renderPosition'));
        $this->assertTrue(method_exists(AdminRecordPreview::class, 'renderProject'));
    }
}
