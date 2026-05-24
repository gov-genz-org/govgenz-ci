<?php

declare(strict_types=1);

use App\Controllers\Admin\Pages;
use App\Controllers\Admin\Posts;
use App\Controllers\Admin\PositionItems;
use App\Libraries\ProjectAdminForm;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Locale figée en édition : règles de validation alignées sur les 4 modules bilingues.
 *
 * @internal
 */
final class AdminRecordLocaleRulesTest extends CIUnitTestCase
{
    public function testPagesValidationRequiresLocaleOnlyOnCreate(): void
    {
        $create = $this->invokePagesRules(false);
        $edit   = $this->invokePagesRules(true);

        $this->assertArrayHasKey('locale', $create);
        $this->assertArrayNotHasKey('locale', $edit);
    }

    public function testPostsValidationRequiresLocaleOnlyOnCreate(): void
    {
        $create = $this->invokePostsRules(false);
        $edit   = $this->invokePostsRules(true);

        $this->assertArrayHasKey('locale', $create);
        $this->assertArrayNotHasKey('locale', $edit);
    }

    public function testPositionItemsValidationRequiresLocaleOnlyOnCreate(): void
    {
        $controller = new PositionItems();
        $method     = new ReflectionMethod($controller, 'rules');
        $method->setAccessible(true);

        $create = $method->invoke($controller, false);
        $edit   = $method->invoke($controller, true);

        $this->assertArrayHasKey('locale', $create);
        $this->assertArrayNotHasKey('locale', $edit);
    }

    public function testProjectAdminFormValidationRequiresLocaleOnlyOnCreate(): void
    {
        $create = ProjectAdminForm::validationRules(false);
        $edit   = ProjectAdminForm::validationRules(true);

        $this->assertArrayHasKey('locale', $create);
        $this->assertArrayNotHasKey('locale', $edit);
    }

    /**
     * @return array<string, string>
     */
    private function invokePagesRules(bool $isEdit): array
    {
        $method = new ReflectionMethod(Pages::class, 'rules');
        $method->setAccessible(true);

        return $method->invoke(new Pages(), $isEdit);
    }

    /**
     * @return array<string, string>
     */
    private function invokePostsRules(bool $isEdit): array
    {
        $method = new ReflectionMethod(Posts::class, 'rules');
        $method->setAccessible(true);

        return $method->invoke(new Posts(), $isEdit);
    }
}
