<?php

declare(strict_types=1);

use App\Libraries\SiteContext;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SiteContextTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        SiteContext::setMain();
        SiteContext::setLocale('fr');
        SiteContext::setPublicUriSegments([]);
        SiteContext::setNavMainLinks([]);
        parent::tearDown();
    }

    public function testSiteIdAndLocale(): void
    {
        SiteContext::setProjects();
        $this->assertTrue(SiteContext::isProjectsSite());
        $this->assertFalse(SiteContext::isPositionsSite());
        $this->assertSame(SiteContext::SITE_PROJECTS, SiteContext::id());

        SiteContext::setPositions();
        $this->assertTrue(SiteContext::isPositionsSite());

        SiteContext::setMain();
        $this->assertSame(SiteContext::SITE_MAIN, SiteContext::id());

        SiteContext::setLocale('en');
        $this->assertSame('en', SiteContext::locale());
        SiteContext::setLocale('xx');
        $this->assertSame('fr', SiteContext::locale());
    }

    public function testPublicUriSegments(): void
    {
        SiteContext::setPublicUriSegments(['projects', '', 'foo', null]);
        $this->assertSame(['projects', 'foo'], SiteContext::publicUriSegments());
        $this->assertSame('projects', SiteContext::publicSegment(1));
        $this->assertSame('foo', SiteContext::publicSegment(2));
        $this->assertSame('', SiteContext::publicSegment(9));
    }

    public function testNavMainLinksRoundTrip(): void
    {
        $links = [
            ['href' => '/contact', 'label' => 'Contact', 'match_key' => 'contact', 'css_class' => ''],
        ];
        SiteContext::setNavMainLinks($links);
        $this->assertSame($links, SiteContext::navMainLinks());
    }

    public function testHttpHostMatchesReturnsFalseInCli(): void
    {
        $this->assertFalse(SiteContext::httpHostMatchesProjectsHost());
        $this->assertFalse(SiteContext::httpHostMatchesPositionsHost());
    }
}
