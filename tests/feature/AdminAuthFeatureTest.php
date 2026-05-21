<?php

declare(strict_types=1);

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;

/**
 * @internal
 */
final class AdminAuthFeatureTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testLoginFormIsReachable(): void
    {
        $result = $this->get('/admin/login');
        $result->assertOK();
        $result->assertSee('Connexion rédaction');
    }

    public function testLogoutViaGetRedirectsToAdminArea(): void
    {
        $result = $this->get('/admin/logout');
        $result->assertRedirectTo(site_url('admin'));
    }

    public function testLogoutViaPostRedirectsToLoginWithBanner(): void
    {
        $login = $this->get('/admin/login');
        $login->assertOK();

        $cookie = $login->response()->getCookie('csrf_cookie_name');
        $this->assertNotNull($cookie);
        $_COOKIE['csrf_cookie_name'] = $cookie->getValue();

        if (
            preg_match(
                '/name="csrf_test_name" value="([^"]+)"/',
                $login->getBody(),
                $matches,
            ) !== 1
        ) {
            $this->fail('Missing csrf_test_name field in login HTML.');
        }

        $result = $this->post('/admin/logout', ['csrf_test_name' => $matches[1]]);
        $result->assertRedirectTo(site_url('admin/login?out=1'));
    }

    public function testLoginIsThrottledAfterRepeatedFailures(): void
    {
        $this->markTestSkipped('Nécessite le schéma staff_users en base de test (migrations) — à réactiver avec les tests d’intégration.');

        for ($i = 0; $i < 12; $i++) {
            $this->postAdminLogin(['email' => 'nobody@example.com', 'password' => 'wrongpass12'])->assertRedirect();
        }

        $blocked = $this->postAdminLogin(['email' => 'nobody@example.com', 'password' => 'wrongpass12']);
        $blocked->assertRedirect();
        $flash = (string) session()->getFlashdata('error');
        $this->assertStringStartsWith('Trop de tentatives de connexion.', $flash);
    }

    /**
     * @param array<string, string> $fields
     */
    private function postAdminLogin(array $fields): TestResponse
    {
        $login = $this->get('/admin/login');
        $cookie = $login->response()->getCookie('csrf_cookie_name');
        $this->assertNotNull($cookie);
        $_COOKIE['csrf_cookie_name'] = $cookie->getValue();

        if (
            preg_match(
                '/name="csrf_test_name" value="([^"]+)"/',
                $login->getBody(),
                $matches,
            ) !== 1
        ) {
            $this->fail('Missing csrf_test_name field in login HTML.');
        }

        $defaults = [
            'csrf_test_name' => $matches[1],
            'email'          => 'nobody@example.com',
            'password'       => 'wrongpass12',
        ];

        return $this->post('/admin/login', array_merge($defaults, $fields));
    }
}
