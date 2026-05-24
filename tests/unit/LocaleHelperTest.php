<?php

declare(strict_types=1);

use App\Libraries\SiteContext;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class LocaleHelperTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function tearDown(): void
    {
        SiteContext::setMain();
        SiteContext::setLocale('fr');
        SiteContext::setPublicUriSegments([]);
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();
        helper(['locale', 'url']);
    }

    public function testSlugMappingFrToEn(): void
    {
        $this->assertSame('who-we-are', locale_slug_fr_to_en('qui-sommes-nous'));
        $this->assertSame('unchanged', locale_slug_fr_to_en('unchanged'));
    }

    public function testSlugMappingEnToFr(): void
    {
        $this->assertSame('secteurs', locale_slug_en_to_fr('sectors'));
    }

    public function testLocalizedSlugFromFrUsesSiteLocale(): void
    {
        SiteContext::setLocale('en');
        $this->assertSame('sectors', localized_slug_from_fr('secteurs'));

        SiteContext::setLocale('fr');
        $this->assertSame('secteurs', localized_slug_from_fr('secteurs'));
    }

    public function testLocalizedSiteUrlAddsEnPrefix(): void
    {
        $this->get('admin/login');
        SiteContext::setLocale('en');
        $this->assertStringContainsString('/en/contact', localized_site_url('contact'));

        SiteContext::setLocale('fr');
        $this->assertStringContainsString('/contact', localized_site_url('contact'));
        $this->assertStringNotContainsString('/en/contact', localized_site_url('contact'));
    }
}
