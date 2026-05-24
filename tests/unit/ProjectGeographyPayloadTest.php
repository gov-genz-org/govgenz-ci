<?php

declare(strict_types=1);

use App\Libraries\ProjectGeographyPayload;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\IncomingRequestFactory;

/**
 * @internal
 */
final class ProjectGeographyPayloadTest extends CIUnitTestCase
{
    public function testFromRequestNational(): void
    {
        $request = IncomingRequestFactory::withPost(['geo_national' => '1']);

        $out = ProjectGeographyPayload::fromRequest($request);

        $this->assertSame('National', $out['geography']);
        $this->assertStringContainsString('"national":true', (string) $out['geography_data']);
    }

    public function testFromRequestEmptySelection(): void
    {
        $request = IncomingRequestFactory::withPost([]);

        $out = ProjectGeographyPayload::fromRequest($request);

        $this->assertNull($out['geography']);
        $this->assertNull($out['geography_data']);
    }

    public function testFormStateNationalJson(): void
    {
        $state = ProjectGeographyPayload::formState([
            'geography_data' => json_encode(['national' => true], JSON_THROW_ON_ERROR),
        ]);

        $this->assertTrue($state['national']);
        $this->assertSame([], $state['region_ids']);
    }

    public function testBuildDisplayLabelNational(): void
    {
        $label = ProjectGeographyPayload::buildDisplayLabel(['national' => true]);

        $this->assertSame('National', $label);
    }

    public function testCountLabelTemplatesFrAndEn(): void
    {
        $fr = ProjectGeographyPayload::countLabelTemplates('fr');
        $en = ProjectGeographyPayload::countLabelTemplates('en');

        $this->assertArrayHasKey('regions', $fr);
        $this->assertArrayHasKey('regions', $en);
        $this->assertStringContainsString('{0}', $fr['regions']);
        $this->assertStringContainsString('{0}', $en['regions']);
        $this->assertNotEmpty($fr['districts']);
        $this->assertNotEmpty($en['fokontany']);
    }
}
