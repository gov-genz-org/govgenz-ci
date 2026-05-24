<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Non-régression structurelle : partials record_*, listes uniformes, plus d’anciens noms program_*.
 *
 * @internal
 */
final class AdminRecordUiRegressionTest extends CIUnitTestCase
{
    /** @var list<string> */
    private const LEGACY_ADMIN_MARKERS = [
        'program_record_form_preview',
        'program_list_row_actions',
        'notes/program_list_header',
        'help_sitemenu_index',
        'help_sectors_index',
        'title_positions_program',
        'title_projects_program',
        'ribbon_program_preview_saved',
        'ribbon_program_preview_draft',
        'AdminProgramRecordPreview',
        'admin_public_projects_program_list_url',
        'admin_public_positions_program_list_url',
        '<motion',
        '</motion>',
    ];

    /** @var array<string, list<string>> */
    private const INDEX_VIEWS_REQUIRED = [
        'pages/index.php'            => ['record_list_row_actions', 'admin-table-wrap', 'table-striped', 'pager_results'],
        'posts/index.php'            => ['record_list_row_actions', 'admin-table-wrap', 'table-striped', 'pager_results'],
        'position_items/index.php'   => ['record_list_row_actions', 'record_list_header', 'admin-table-wrap', 'table-striped', 'pager_results'],
        'project_projects/index.php' => ['record_list_row_actions', 'record_list_header', 'admin-table-wrap', 'table-striped', 'pager_results'],
        'sectors/index.php'          => ['record_list_row_actions', 'admin-table-wrap', 'table-striped', 'pager_results', 'filter_active'],
        'site_menu/index.php'        => ['record_list_row_actions', 'admin-table-wrap', 'table-striped', 'pager_results', 'name="loc"'],
    ];

    /** @var array<string, list<string>> */
    private const BILINGUAL_FORM_VIEWS_REQUIRED = [
        'pages/form.php'            => ['record_form_nav', 'record_form_locale', 'record_form_preview', 'admin-editor-form'],
        'posts/form.php'            => ['record_form_nav', 'record_form_locale', 'record_form_preview', 'admin-editor-form'],
        'position_items/form.php'   => ['record_form_nav', 'record_form_locale', 'record_form_preview', 'admin-editor-form'],
        'project_projects/form.php' => ['record_form_nav', 'record_form_locale', 'record_form_preview', 'admin-editor-form'],
    ];

    /** @var list<string> */
    private const RECORD_PARTIAL_FILES = [
        'partials/record_form_nav.php',
        'partials/record_form_locale.php',
        'partials/record_form_preview.php',
        'partials/record_list_row_actions.php',
        'partials/notes/record_list_header.php',
    ];

    public function testRecordPartialFilesExist(): void
    {
        foreach (self::RECORD_PARTIAL_FILES as $relative) {
            $this->assertFileExists(APPPATH . 'Views/admin/' . $relative, $relative);
        }
    }

    public function testAdminRecordLibraryRenamed(): void
    {
        $this->assertFileExists(APPPATH . 'Libraries/AdminRecordPreview.php');
        $this->assertFileDoesNotExist(APPPATH . 'Libraries/AdminProgramRecordPreview.php');
    }

    public function testNoLegacyAdminMarkersUnderViewsAdmin(): void
    {
        $hits = [];
        $root = APPPATH . 'Views/admin';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }
            foreach (self::LEGACY_ADMIN_MARKERS as $marker) {
                if (str_contains($content, $marker)) {
                    $hits[] = str_replace(APPPATH, '', $file->getPathname()) . ' → ' . $marker;
                }
            }
        }

        $this->assertSame([], $hits, "Legacy markers found:\n" . implode("\n", $hits));
    }

    /**
     * @dataProvider indexViewsProvider
     *
     * @param list<string> $required
     */
    public function testAdminIndexViewsUseUniformListPattern(string $relative, array $required): void
    {
        $path = APPPATH . 'Views/admin/' . $relative;
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertIsString($content);

        foreach ($required as $needle) {
            $this->assertStringContainsString(
                $needle,
                $content,
                "{$relative} must contain {$needle}",
            );
        }
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function indexViewsProvider(): array
    {
        $out = [];
        foreach (self::INDEX_VIEWS_REQUIRED as $relative => $required) {
            $out[$relative] = [$relative, $required];
        }

        return $out;
    }

    /**
     * @dataProvider bilingualFormViewsProvider
     *
     * @param list<string> $required
     */
    public function testBilingualFormViewsUseRecordPartials(string $relative, array $required): void
    {
        $path = APPPATH . 'Views/admin/' . $relative;
        $content = file_get_contents($path);
        $this->assertIsString($content);

        foreach ($required as $needle) {
            $this->assertStringContainsString($needle, $content, "{$relative} must contain {$needle}");
        }
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function bilingualFormViewsProvider(): array
    {
        $out = [];
        foreach (self::BILINGUAL_FORM_VIEWS_REQUIRED as $relative => $required) {
            $out[$relative] = [$relative, $required];
        }

        return $out;
    }

    public function testSectorsAndSiteMenuFormsUseAdminEditorForm(): void
    {
        foreach (['sectors/form.php', 'site_menu/form.php'] as $relative) {
            $content = file_get_contents(APPPATH . 'Views/admin/' . $relative);
            $this->assertIsString($content);
            $this->assertStringContainsString('admin-editor-form', $content, $relative);
        }
    }

    public function testPreviewControllerUsesAdminRecordPreviewAndRibbonKeys(): void
    {
        $content = file_get_contents(APPPATH . 'Controllers/Admin/Preview.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('AdminRecordPreview', $content);
        $this->assertStringContainsString('ribbon_record_preview_saved', $content);
        $this->assertStringContainsString('ribbon_record_preview_draft', $content);
        $this->assertStringNotContainsString('AdminProgramRecordPreview', $content);
        $this->assertStringNotContainsString('ribbon_program_preview_', $content);
    }
}
