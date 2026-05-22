<?php

declare(strict_types=1);

use App\Libraries\ProjectAdminForm;
use App\Models\ProjectProjectModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\IncomingRequestFactory;

/**
 * @internal
 */
final class ProjectAdminFormTest extends CIUnitTestCase
{
    public function testValidationRulesIncludeLocaleOnCreate(): void
    {
        $create = ProjectAdminForm::validationRules(false);
        $edit   = ProjectAdminForm::validationRules(true);

        $this->assertArrayHasKey('locale', $create);
        $this->assertArrayNotHasKey('locale', $edit);
        $this->assertArrayHasKey('slug', $create);
        $this->assertArrayHasKey('body_content_mode', $create);
    }

    public function testValidateBudgetPostEmptyAmount(): void
    {
        $request = IncomingRequestFactory::withPost(['budget_amount' => '']);

        $this->assertSame([], ProjectAdminForm::validateBudgetPost($request));
    }

    public function testValidateBudgetPostRejectsInvalidAmount(): void
    {
        service('language')->setLocale('fr');
        $request = IncomingRequestFactory::withPost([
            'budget_amount' => '-1',
            'budget_scale'  => ProjectProjectModel::BUDGET_SCALE_MILLION,
        ]);

        $errors = ProjectAdminForm::validateBudgetPost($request);

        $this->assertArrayHasKey('budget_amount', $errors);
    }

    public function testValidateBudgetPostRejectsMissingScale(): void
    {
        service('language')->setLocale('fr');
        $request = IncomingRequestFactory::withPost([
            'budget_amount' => '10',
            'budget_scale'  => '',
        ]);

        $errors = ProjectAdminForm::validateBudgetPost($request);

        $this->assertArrayHasKey('budget_scale', $errors);
    }
}
