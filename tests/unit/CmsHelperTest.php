<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CmsHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('cms');
    }

    public function testStructuredHeroActiveWhenKeySet(): void
    {
        $this->assertTrue(cms_page_structured_hero_active([
            'hero_title' => 'Titre',
        ]));
    }

    public function testStructuredHeroInactiveWhenEmpty(): void
    {
        $this->assertFalse(cms_page_structured_hero_active([]));
        $this->assertFalse(cms_page_structured_hero_active([
            'hero_title' => '   ',
            'hero_lead'  => '',
        ]));
    }

    public function testListPageLayoutMainClassUsesLayoutKey(): void
    {
        $this->assertSame('ggz-layout-full', cms_list_page_layout_main_class(null));
        $this->assertSame('', cms_list_page_layout_main_class(['layout_key' => null]));
        $this->assertSame('', cms_list_page_layout_main_class(['layout_key' => '']));
        $this->assertSame('ggz-layout-narrow', cms_list_page_layout_main_class(['layout_key' => 'narrow']));
        $this->assertSame('ggz-layout-full', cms_list_page_layout_main_class(['layout_key' => 'full']));
    }
}
