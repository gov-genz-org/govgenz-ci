<?php

declare(strict_types=1);

use App\Models\CmsPageModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AdminTranslationHelperTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const VIEW_OPTIONS = ['saveData' => false];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['admin', 'url']);
        service('language')->setLocale('fr');
        $this->get('admin/login');
    }

    public function testTranslationPartnerNavReturnsNullWithoutItem(): void
    {
        $this->assertNull(admin_translation_partner_nav(null, CmsPageModel::class, 'admin/pages'));
    }

    public function testTranslationPartnerNavReturnsNullWithoutTranslationGroup(): void
    {
        $nav = admin_translation_partner_nav(
            ['id' => 1, 'locale' => 'fr', 'translation_group' => '', 'slug' => 'about'],
            CmsPageModel::class,
            'admin/pages',
        );

        $this->assertNull($nav);
    }
}
