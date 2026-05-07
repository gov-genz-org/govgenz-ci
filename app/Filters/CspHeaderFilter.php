<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CSP « progressive » : compatible TinyMCE et CDN (jsDelivr) ; à durcir au fil du temps.
 */
class CspHeaderFilter implements FilterInterface
{
    private const POLICY = "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; "
        . "img-src 'self' data: blob: https:; font-src 'self' data: https://cdn.jsdelivr.net; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
        . "connect-src 'self'";

    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $enforce = ENVIRONMENT === 'production'
            ? filter_var(env('csp.enforce', 'true'), FILTER_VALIDATE_BOOLEAN)
            : filter_var(env('csp.enforce', 'false'), FILTER_VALIDATE_BOOLEAN);

        if ($enforce) {
            $response->setHeader('Content-Security-Policy', self::POLICY);
        } else {
            $response->setHeader('Content-Security-Policy-Report-Only', self::POLICY);
        }

        return $response;
    }
}
