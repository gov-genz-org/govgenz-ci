<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AdminUrlHelperTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['admin', 'url']);
        $this->get('admin/login');
    }

    public function testAdminSiteUrlForLocale(): void
    {
        $this->assertStringContainsString('/en/contact', admin_site_url_for_locale('contact', 'en'));
        $this->assertStringContainsString('/contact', admin_site_url_for_locale('contact', 'fr'));
    }

    public function testAdminPublicPageUrlHomeAndSlug(): void
    {
        $this->assertStringEndsWith('/en', (string) admin_public_page_url('home', 'en'));
        $this->assertNull(admin_public_page_url('', 'fr'));
        $this->assertStringContainsString('/qui-sommes-nous', (string) admin_public_page_url('qui-sommes-nous', 'fr'));
    }

    public function testAdminPublicPressUrl(): void
    {
        $url = admin_public_press_url('article-test', 'en');
        $this->assertStringContainsString('/en/press/article-test', (string) $url);
    }

    public function testAdminPublicProjectUrlNullWhenEmptySlug(): void
    {
        $this->assertNull(admin_public_project_url(''));
    }

    public function testAdminPublicProjectsListUrl(): void
    {
        $fr = admin_public_projects_list_url('fr');
        $this->assertIsString($fr);
        $this->assertStringContainsString('projects', $fr);
    }

    public function testAdminPublicPositionsListUrl(): void
    {
        $en = admin_public_positions_list_url('en');
        $this->assertIsString($en);
        $this->assertStringContainsString('en', $en);
    }
}
