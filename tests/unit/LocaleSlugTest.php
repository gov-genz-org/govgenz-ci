<?php

declare(strict_types=1);

use App\Libraries\LocaleSlug;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LocaleSlugTest extends CIUnitTestCase
{
    public function testNormalizeLocale(): void
    {
        $this->assertSame('fr', LocaleSlug::normalizeLocale('fr'));
        $this->assertSame('en', LocaleSlug::normalizeLocale(' EN '));
        $this->assertSame('fr', LocaleSlug::normalizeLocale('de'));
        $this->assertSame('fr', LocaleSlug::normalizeLocale(null));
    }

    public function testNormalizeSlug(): void
    {
        $this->assertSame('mon-projet-2026', LocaleSlug::normalizeSlug('  Mon Projet 2026! '));
        $this->assertSame('legal-aid', LocaleSlug::normalizeSlug('Legal---Aid'));
        $this->assertSame('', LocaleSlug::normalizeSlug('   '));
    }

    public function testNormalizeTranslationGroup(): void
    {
        $this->assertSame('group-a', LocaleSlug::normalizeTranslationGroup(' group-a '));
        $this->assertNull(LocaleSlug::normalizeTranslationGroup(''));
        $this->assertNull(LocaleSlug::normalizeTranslationGroup(null));
    }
}
