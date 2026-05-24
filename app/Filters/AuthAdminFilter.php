<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('staff_user_id')) {
            return null;
        }

        $path = trim((string) service('request')->getUri()->getPath(), '/');
        if ($path !== '' && $path !== 'admin/login' && str_starts_with($path, 'admin')) {
            // Session normale (pas flashdata) : le flash CI est purgé avant le POST login après la page GET.
            session()->set('admin_intended', $path);
        }

        return redirect()->to(site_url('admin/login'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
