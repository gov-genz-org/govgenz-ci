<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Routes admin record modules : non-régression sans session (redirection login).
 *
 * @internal
 */
final class AdminRecordRoutesFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    /**
     * @return array<string, array{0: string}>
     */
    public static function protectedAdminListRoutesProvider(): array
    {
        return [
            'pages'            => ['admin/pages'],
            'posts'            => ['admin/posts'],
            'position-items'   => ['admin/position-items'],
            'project-projects' => ['admin/project-projects'],
            'sectors'          => ['admin/sectors'],
            'site-menu'        => ['admin/site-menu'],
        ];
    }

    /**
     * @dataProvider protectedAdminListRoutesProvider
     */
    public function testAdminListRoutesRequireAuthentication(string $path): void
    {
        $result = $this->get($path);
        $result->assertRedirect();
        $this->assertStringContainsString('admin/login', (string) $result->response()->getHeaderLine('Location'));
    }

    public function testSavedPreviewRoutesRequireAuthentication(): void
    {
        foreach ([
            'admin/pages/preview/1',
            'admin/posts/preview/1',
            'admin/position-items/preview/1',
            'admin/project-projects/preview/1',
        ] as $path) {
            $result = $this->get($path);
            $result->assertRedirect();
            $this->assertStringContainsString(
                'admin/login',
                (string) $result->response()->getHeaderLine('Location'),
                $path,
            );
        }
    }
}
