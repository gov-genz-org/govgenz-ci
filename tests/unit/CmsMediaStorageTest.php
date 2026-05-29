<?php

declare(strict_types=1);

use App\Libraries\CmsMediaStorage;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class CmsMediaStorageTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testFileExistsFindsFileInRootUploadsCms(): void
    {
        $name = $this->uniqueTestFilename();
        $path = $this->writeFixture($this->rootCmsDir(), $name);

        try {
            $this->assertFileExists($path);
            $this->assertTrue(CmsMediaStorage::fileExists($name));
            $this->assertSame($path, CmsMediaStorage::resolveReadablePath($name));
        } finally {
            $this->removeFixture($path);
            $this->removeFixture($this->publicCmsDir() . $name);
        }
    }

    public function testFileExistsFindsFileInPublicUploadsCms(): void
    {
        $name = $this->uniqueTestFilename();
        $path = $this->writeFixture($this->publicCmsDir(), $name);

        try {
            $this->assertFileExists($path);
            $this->assertTrue(CmsMediaStorage::fileExists($name));
            $resolved = CmsMediaStorage::resolveReadablePath($name);
            $this->assertFileExists($resolved);
        } finally {
            $this->removeFixture($path);
            $this->removeFixture($this->rootCmsDir() . $name);
        }
    }

    public function testFileExistsReturnsFalseForMissingFile(): void
    {
        $name = $this->uniqueTestFilename();

        $this->assertFalse(CmsMediaStorage::fileExists($name));
        $this->assertSame(CmsMediaStorage::filePath($name), CmsMediaStorage::resolveReadablePath($name));
    }

    public function testUnsafeFilenamesAreRejected(): void
    {
        $this->assertFalse(CmsMediaStorage::fileExists(''));
        $this->assertFalse(CmsMediaStorage::fileExists('../index.php'));
        $this->assertFalse(CmsMediaStorage::fileExists('subdir/evil.svg'));
        $this->assertSame('', CmsMediaStorage::publicUrl('../../.env'));
    }

    public function testPublicUrlPointsToUploadsCms(): void
    {
        $this->get('/admin/login');

        $url = CmsMediaStorage::publicUrl('sample-unit-test.svg');

        $this->assertStringContainsString('uploads/cms/sample-unit-test.svg', $url);
    }

    public function testStorageDirUsesProjectUploadsCmsByDefault(): void
    {
        if (trim((string) env('app.mediaStoragePath', '')) !== '') {
            $this->markTestSkipped('app.mediaStoragePath is set in the environment.');
        }

        $root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ROOTPATH), DIRECTORY_SEPARATOR);
        $publicIndex = $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
        $fcPath      = realpath(rtrim(FCPATH, '/\\')) ?: rtrim(FCPATH, '/\\');
        $pubPath     = is_file($publicIndex) ? (realpath(dirname($publicIndex)) ?: dirname($publicIndex)) : '';

        if ($pubPath !== '' && $fcPath === $pubPath) {
            $expected = $pubPath . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR;
        } else {
            $expected = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR;
        }

        $this->assertSame($expected, CmsMediaStorage::storageDir());
    }

    public function testFileExistsFindsFileOnlyUnderPublicUploadsCms(): void
    {
        $name = $this->uniqueTestFilename();
        $publicPath = $this->writeFixture($this->publicCmsDir(), $name);
        $this->removeFixture($this->rootCmsDir() . $name);

        try {
            $this->assertFileExists($publicPath);
            $this->assertTrue(CmsMediaStorage::fileExists($name));
        } finally {
            $this->removeFixture($publicPath);
        }
    }

    private function uniqueTestFilename(): string
    {
        return 'phpunit_cms_media_' . bin2hex(random_bytes(8)) . '.txt';
    }

    private function rootCmsDir(): string
    {
        return rtrim(ROOTPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR;
    }

    private function publicCmsDir(): string
    {
        return rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cms' . DIRECTORY_SEPARATOR;
    }

    private function writeFixture(string $dir, string $name): string
    {
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->fail('Cannot create directory: ' . $dir);
        }

        $path = $dir . $name;
        if (file_put_contents($path, 'phpunit') === false) {
            $this->fail('Cannot write fixture: ' . $path);
        }

        return $path;
    }

    private function removeFixture(string $path): void
    {
        if ($path !== '' && is_file($path)) {
            @unlink($path);
        }
    }
}
