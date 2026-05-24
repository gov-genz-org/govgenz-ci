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

    public function testBudgetPayloadFromPostBuildsAriary(): void
    {
        helper('project');
        $request = IncomingRequestFactory::withPost([
            'budget_amount' => '2.5',
            'budget_scale'  => \App\Models\ProjectProjectModel::BUDGET_SCALE_BILLION,
        ]);

        $payload = ProjectAdminForm::budgetPayloadFromPost($request, 'fr');

        $this->assertSame(2.5, $payload['budget_amount']);
        $this->assertSame(2_500_000_000, $payload['budget_ariary']);
        $this->assertNotEmpty($payload['budget_display']);
    }

    public function testBudgetPayloadFromPostEmptyReturnsNulls(): void
    {
        $request = IncomingRequestFactory::withPost(['budget_amount' => '']);

        $payload = ProjectAdminForm::budgetPayloadFromPost($request, 'fr');

        $this->assertNull($payload['budget_ariary']);
    }
}
