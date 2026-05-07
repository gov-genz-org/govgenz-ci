<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Accès réservé aux comptes staff avec le rôle « admin » (zone équipe / sécurité).
 */
class AdminOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('staff_role') !== 'admin') {
            return redirect()->to(site_url('admin'))->with(
                'error',
                'Accès réservé aux administrateurs.',
            );
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
