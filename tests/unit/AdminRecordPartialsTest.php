<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Rendu des partials admin record_* (non-régression UI partagée).
 *
 * @internal
 */
final class AdminRecordPartialsTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const VIEW_OPTIONS = ['saveData' => false];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['form', 'admin', 'url']);
        service('language')->setLocale('fr');
        $this->get('admin/login');
    }

    public function testRecordListRowActionsShowsEditDeleteWithoutDuplicate(): void
    {
        $html = view('admin/partials/record_list_row_actions', [
            'previewUrl'           => null,
            'editUrl'              => site_url('admin/sectors/edit/1'),
            'deleteUrl'            => site_url('admin/sectors/delete/1'),
            'deleteConfirmMessage' => 'Supprimer ?',
            'showDuplicateTrad'    => false,
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString(lang('Admin.action_edit'), $html);
        $this->assertStringContainsString(lang('Admin.action_delete'), $html);
        $this->assertStringNotContainsString(lang('Admin.action_duplicate_trad'), $html);
    }

    public function testRecordListRowActionsShowsPreviewAndDuplicateWhenEnabled(): void
    {
        $html = view('admin/partials/record_list_row_actions', [
            'previewUrl'            => 'https://example.com/page',
            'editUrl'               => site_url('admin/pages/edit/2'),
            'duplicateUrl'          => site_url('admin/pages/duplicate/2'),
            'deleteUrl'             => site_url('admin/pages/delete/2'),
            'deleteConfirmMessage'  => 'Supprimer ?',
            'duplicateTradDisabled' => false,
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString(lang('Admin.action_view'), $html);
        $this->assertStringContainsString(lang('Admin.action_duplicate_trad'), $html);
        $this->assertStringContainsString('target="_blank"', $html);
    }

    public function testRecordListRowActionsDisablesDuplicateButton(): void
    {
        $html = view('admin/partials/record_list_row_actions', [
            'previewUrl'            => null,
            'editUrl'               => site_url('admin/pages/edit/2'),
            'duplicateUrl'          => site_url('admin/pages/duplicate/2'),
            'deleteUrl'             => site_url('admin/pages/delete/2'),
            'deleteConfirmMessage'  => 'Supprimer ?',
            'duplicateTradDisabled' => true,
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString('disabled', $html);
        $this->assertStringContainsString(lang('Admin.action_duplicate_trad'), $html);
    }

    public function testRecordListRowActionsSaveDataDoesNotLeakDuplicateFlag(): void
    {
        view('admin/partials/record_list_row_actions', [
            'previewUrl'           => null,
            'editUrl'              => site_url('admin/sectors/edit/1'),
            'deleteUrl'            => site_url('admin/sectors/delete/1'),
            'deleteConfirmMessage' => 'Supprimer ?',
            'showDuplicateTrad'    => false,
        ], self::VIEW_OPTIONS);

        $html = view('admin/partials/record_list_row_actions', [
            'previewUrl'            => null,
            'editUrl'               => site_url('admin/pages/edit/2'),
            'duplicateUrl'          => site_url('admin/pages/duplicate/2'),
            'deleteUrl'             => site_url('admin/pages/delete/2'),
            'deleteConfirmMessage'  => 'Supprimer ?',
            'duplicateTradDisabled' => false,
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString(lang('Admin.action_duplicate_trad'), $html);
    }

    public function testRecordFormNavShowsTranslationPartnerLinks(): void
    {
        $html = view('admin/partials/record_form_nav', [
            'publicPreviewUrl'      => 'https://example.com/fr/page',
            'translationPartnerNav' => [
                'editUrl'   => site_url('admin/pages/edit/99'),
                'publicUrl' => 'https://example.com/en/page',
                'viewLabel' => lang('Admin.translation_view_en'),
                'editLabel' => lang('Admin.translation_edit_en'),
            ],
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString(lang('Admin.action_view_published_record'), $html);
        $this->assertStringContainsString(lang('Admin.translation_edit_en'), $html);
        $this->assertStringContainsString(lang('Admin.translation_view_en'), $html);
    }

    public function testRecordFormLocaleReadonlyInEditMode(): void
    {
        $html = view('admin/partials/record_form_locale', [
            'locale'  => 'fr',
            'isEdit'  => true,
            'fieldId' => 'test-locale',
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString('readonly', $html);
        $this->assertStringNotContainsString('name="locale"', $html);
        $this->assertStringContainsString(lang('Admin.help_record_locale'), $html);
    }

    public function testRecordFormLocaleSelectInCreateMode(): void
    {
        $html = view('admin/partials/record_form_locale', [
            'locale'  => 'en',
            'isEdit'  => false,
            'fieldId' => 'test-locale',
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString('name="locale"', $html);
        $this->assertStringContainsString('value="en"', $html);
        $this->assertStringNotContainsString('readonly', $html);
    }

    public function testRecordFormPreviewRendersDraftAndSavedActions(): void
    {
        $html = view('admin/partials/record_form_preview', [
            'recordId'         => 42,
            'draftPreviewPath' => 'admin/pages/preview-draft',
            'savedPreviewPath' => 'admin/pages/preview',
        ], self::VIEW_OPTIONS);

        $this->assertStringContainsString(lang('Admin.action_preview_draft'), $html);
        $this->assertStringContainsString(lang('Admin.action_preview_saved'), $html);
        $this->assertStringContainsString('admin/pages/preview-draft/42', $html);
        $this->assertStringContainsString('admin/pages/preview/42', $html);
        $this->assertStringContainsString('formaction=', $html);
    }
}
