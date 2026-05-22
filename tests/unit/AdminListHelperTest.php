<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AdminListHelperTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['admin', 'url']);
    }

    public function testAdminListSortUrlTogglesDirection(): void
    {
        $this->get('admin/pages?sort=title&dir=asc&q=foo');

        $url = admin_list_sort_url('title', 'title', 'asc');

        $this->assertStringContainsString('dir=desc', $url);
        $this->assertStringContainsString('sort=title', $url);
        $this->assertStringContainsString('q=foo', $url);
        $this->assertStringNotContainsString('page=', $url);
    }

    public function testAdminListSortThMarksActiveColumn(): void
    {
        $this->get('admin/pages');

        $html = admin_list_sort_th('slug', 'Slug', 'slug', 'desc');

        $this->assertStringContainsString('admin-sort-link--active', $html);
        $this->assertStringContainsString('aria-sort="descending"', $html);
        $this->assertStringContainsString('Slug', $html);
    }

    public function testAdminListSortHiddenFields(): void
    {
        $html = admin_list_sort_hidden_fields('updated_at', 'DESC');

        $this->assertStringContainsString('name="sort"', $html);
        $this->assertStringContainsString('value="updated_at"', $html);
        $this->assertStringContainsString('value="desc"', $html);
    }
}
