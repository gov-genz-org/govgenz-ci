<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminDatetimeHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('admin');
        unset($_COOKIE['admin_client_tz']);
    }

    public function testParseStorageRejectsEmptyAndZeroDates(): void
    {
        $this->assertNull(admin_datetime_parse_storage(null));
        $this->assertNull(admin_datetime_parse_storage(''));
        $this->assertNull(admin_datetime_parse_storage('0000-00-00 00:00:00'));
    }

    public function testParseStorageAndIsoUtc(): void
    {
        $dt = admin_datetime_parse_storage('2026-05-21 14:30:00');
        $this->assertInstanceOf(\DateTimeImmutable::class, $dt);

        $iso = admin_datetime_to_iso_utc('2026-05-21 14:30:00');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', (string) $iso);
    }

    public function testFormatDatetimeReturnsTimeElement(): void
    {
        $html = admin_format_datetime('2026-05-21 14:30:00');

        $this->assertStringContainsString('<time', $html);
        $this->assertStringContainsString('datetime=', $html);
    }

    public function testClientTimezoneFallsBackToUtc(): void
    {
        $_COOKIE['admin_client_tz'] = 'Not/A_Real_Timezone';

        $this->assertSame('UTC', admin_client_timezone());
    }

    public function testLocalToStorageRoundTrip(): void
    {
        $_COOKIE['admin_client_tz'] = 'UTC';
        $stored = admin_datetime_local_to_storage('2026-05-21T10:00');

        $this->assertSame('2026-05-21 10:00:00', $stored);
    }
}
