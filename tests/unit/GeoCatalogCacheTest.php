<?php

declare(strict_types=1);

use App\Libraries\GeoCatalogCache;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class GeoCatalogCacheTest extends CIUnitTestCase
{
    public function testIdsSuffixIsStableForSameIds(): void
    {
        $a = GeoCatalogCache::idsSuffix('district', [3, 1, 3]);
        $b = GeoCatalogCache::idsSuffix('district', [1, 3]);

        $this->assertSame($a, $b);
        $this->assertStringStartsWith('district_', $a);
    }

    public function testRememberCachesProducerResult(): void
    {
        GeoCatalogCache::flushAll();
        $calls = 0;
        $producer = static function () use (&$calls): array {
            $calls++;

            return ['items' => [1, 2]];
        };

        $first  = GeoCatalogCache::remember('test_unit', $producer);
        $second = GeoCatalogCache::remember('test_unit', $producer);

        $this->assertSame(['items' => [1, 2]], $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $calls);
    }
}
