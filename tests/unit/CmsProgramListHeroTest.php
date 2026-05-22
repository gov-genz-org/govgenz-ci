<?php

declare(strict_types=1);

use App\Libraries\CmsProgramListHero;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CmsProgramListHeroTest extends CIUnitTestCase
{
    public function testResolveUsesCmsFields(): void
    {
        $hero = CmsProgramListHero::resolve([
            'hero_overline'      => 'Over',
            'hero_title'         => 'CMS title',
            'hero_lead'          => 'Lead',
            'meta_title'         => 'Meta T',
            'meta_description'   => 'Meta D',
        ], 'Default list', 'Default layout');

        $this->assertSame('Over', $hero['heroOverline']);
        $this->assertSame('CMS title', $hero['heroTitle']);
        $this->assertSame('Meta T', $hero['layoutTitle']);
        $this->assertSame('Meta D', $hero['layoutMeta']);
    }

    public function testResolveFallsBackWhenPageNull(): void
    {
        $hero = CmsProgramListHero::resolve(null, 'List', 'Layout');

        $this->assertSame('List', $hero['heroTitle']);
        $this->assertSame('Layout', $hero['layoutTitle']);
        $this->assertSame('', $hero['layoutMeta']);
    }

    public function testResolveUsesPageTitleWhenHeroTitleEmpty(): void
    {
        $hero = CmsProgramListHero::resolve([
            'title' => 'Page title',
        ], 'Default', 'Layout');

        $this->assertSame('Page title', $hero['heroTitle']);
    }
}
