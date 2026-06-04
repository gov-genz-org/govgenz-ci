<?php

declare(strict_types=1);

use App\Libraries\PublicStaticAsset;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PublicStaticAssetTest extends CIUnitTestCase
{
    public function testUrlIfExistsFindsSectorIconUnderPublic(): void
    {
        $url = PublicStaticAsset::urlIfExists('assets/icons/sectors/education.svg');
        $this->assertNotNull($url);
        $this->assertStringContainsString('assets/icons/sectors/education.svg', $url);
    }

    public function testUrlIfExistsReturnsNullForMissingFile(): void
    {
        $this->assertNull(PublicStaticAsset::urlIfExists('assets/icons/sectors/__missing__.svg'));
    }
}
