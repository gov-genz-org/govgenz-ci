<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Garde-fous i18n admin : parité fr/en et clés référencées dans le code.
 *
 * @internal
 */
final class AdminLangIntegrityTest extends CIUnitTestCase
{
    /** @var list<string> Chemins relatifs à APPPATH */
    private const SCAN_RELATIVE = [
        'Views/admin',
        'Controllers/Admin',
        'Controllers/BaseController.php',
        'Libraries/ProjectAdminForm.php',
        'Helpers/admin_translation_helper.php',
    ];

    /** @var list<string> Clés critiques (auth + navigation) */
    private const CRITICAL_KEYS = [
        'auth_login_title',
        'auth_login_submit',
        'auth_invite_invalid_title',
        'action_back_to_login',
        'nav_dashboard',
        'pager_results',
        'ui_close',
    ];

    public function testFrenchAndEnglishAdminLangKeysMatch(): void
    {
        $frKeys = array_keys($this->loadAdminLang('fr'));
        $enKeys = array_keys($this->loadAdminLang('en'));
        sort($frKeys);
        sort($enKeys);

        $onlyFr = array_values(array_diff($frKeys, $enKeys));
        $onlyEn = array_values(array_diff($enKeys, $frKeys));

        $this->assertSame(
            [],
            $onlyFr,
            'Keys only in fr/Admin.php: ' . implode(', ', $onlyFr),
        );
        $this->assertSame(
            [],
            $onlyEn,
            'Keys only in en/Admin.php: ' . implode(', ', $onlyEn),
        );
    }

    public function testReferencedAdminLangKeysExistInLanguageFiles(): void
    {
        $defined = array_fill_keys(array_keys($this->loadAdminLang('fr')), true);
        $missing = [];

        foreach ($this->collectReferencedAdminKeys() as $key) {
            if (! isset($defined[$key])) {
                $missing[] = $key;
            }
        }

        sort($missing);
        $this->assertSame(
            [],
            $missing,
            'lang(\'Admin.*\') used in code but missing from Language files: ' . implode(', ', $missing),
        );
    }

    public function testCriticalAdminLangKeysAreNonEmpty(): void
    {
        foreach (['fr', 'en'] as $locale) {
            $lines = $this->loadAdminLang($locale);
            foreach (self::CRITICAL_KEYS as $key) {
                $this->assertArrayHasKey($key, $lines, "{$locale}/Admin.php missing {$key}");
                $this->assertNotSame('', trim((string) $lines[$key]), "{$locale}/Admin.{$key} is empty");
            }
        }
    }

    public function testLangHelperResolvesFrenchAdminStrings(): void
    {
        $fr = $this->loadAdminLang('fr');
        service('language')->setLocale('fr');

        foreach (self::CRITICAL_KEYS as $key) {
            $this->assertSame($fr[$key], lang('Admin.' . $key), "lang('Admin.{$key}') with locale fr");
        }
    }

    public function testLangHelperResolvesEnglishAdminStrings(): void
    {
        $en = $this->loadAdminLang('en');
        service('language')->setLocale('en');

        foreach (self::CRITICAL_KEYS as $key) {
            $this->assertSame($en[$key], lang('Admin.' . $key), "lang('Admin.{$key}') with locale en");
        }
    }

    /**
     * @return array<string, string>
     */
    private function loadAdminLang(string $locale): array
    {
        $path = $this->adminLangFilePath($locale);
        $this->assertFileExists($path);

        $lines = require $path;
        $this->assertIsArray($lines);

        return $lines;
    }

    private function adminLangFilePath(string $locale): string
    {
        return APPPATH . 'Language/' . $locale . '/Admin.php';
    }

    /**
     * @return list<string>
     */
    private function collectReferencedAdminKeys(): array
    {
        $keys = [];

        foreach (self::SCAN_RELATIVE as $relative) {
            $path = APPPATH . $relative;
            if (is_file($path)) {
                $this->extractAdminKeysFromFile($path, $keys);

                continue;
            }

            if (! is_dir($path)) {
                $this->fail('Admin lang scan path missing: ' . $relative);
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $this->extractAdminKeysFromFile($file->getPathname(), $keys);
            }
        }

        $list = array_keys($keys);
        sort($list);

        return $list;
    }

    /**
     * @param array<string, true> $keys
     */
    private function extractAdminKeysFromFile(string $path, array &$keys): void
    {
        $content = file_get_contents($path);
        if ($content === false) {
            $this->fail('Cannot read ' . $path);
        }

        if (
            preg_match_all(
                '/lang\([\'"]Admin\.([a-zA-Z0-9_]+)[\'"]/',
                $content,
                $matches,
            ) < 1
        ) {
            return;
        }

        foreach ($matches[1] as $key) {
            $keys[$key] = true;
        }
    }
}
