<?php

declare(strict_types=1);

use App\Models\DeclarationItemModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DeclarationHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('declaration');
    }

    public function testDefaultCtaReplacesVagueSupportLabel(): void
    {
        $item = [
            'kind'         => DeclarationItemModel::KIND_PLEDGE,
            'list_section' => DeclarationItemModel::SECTION_DECLARATIONS,
            'cta_label'    => 'Nous soutenir',
            'cta_href'     => 'mailto:contact@govgenz.org',
        ];

        $cta = declaration_default_cta_for_item($item, 'Test titre');
        $this->assertNotNull($cta);
        $this->assertStringContainsString('mailto:contact@govgenz.org', $cta['href']);
        $this->assertStringContainsString('subject=', $cta['href']);
        $this->assertNotSame('Nous soutenir', $cta['label']);
    }

    public function testShowActionCtasIncludesContactPage(): void
    {
        helper('locale');
        $item = [
            'kind'         => DeclarationItemModel::KIND_OFFICIAL,
            'list_section' => DeclarationItemModel::SECTION_DECLARATIONS,
            'cta_label'    => 'Nous contacter',
            'cta_href'     => 'mailto:contact@govgenz.org',
        ];

        $ctas = declaration_show_action_ctas($item, 'Ma déclaration');
        $this->assertCount(2, $ctas);
        $this->assertStringContainsString('contact', $ctas[1]['href']);
    }

    public function testCtaPanelActionLabelHidesRawEmail(): void
    {
        $label = declaration_cta_panel_action_label('contact@govgenz.org', 'mailto:contact@govgenz.org');
        $this->assertStringNotContainsString('@', $label);
    }
}
