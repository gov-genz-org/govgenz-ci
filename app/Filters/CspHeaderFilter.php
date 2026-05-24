<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CSP « progressive » : TinyMCE / Bootstrap / jsDelivr ; Google Fonts (layout public + guide CMS).
 * script-src-elem / style-src-elem : requis par certains agents pour <script src> / <link rel=stylesheet> tiers
 * (ex. intl-tel-input, multi-select-dropdown-js) même si script-src / style-src sont déjà définis.
 */
class CspHeaderFilter implements FilterInterface
{
    private const POLICY = "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; "
        . "img-src 'self' data: blob: https:; "
        . "font-src 'self' data: https://cdn.jsdelivr.net https://*.jsdelivr.net https://fonts.gstatic.com; "
        . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://*.jsdelivr.net https://fonts.googleapis.com; "
        . "style-src-elem 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://*.jsdelivr.net https://fonts.googleapis.com; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://*.jsdelivr.net https://www.googletagmanager.com; "
        . "script-src-elem 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://*.jsdelivr.net https://www.googletagmanager.com; "
        . "connect-src 'self' https://cdn.jsdelivr.net https://*.jsdelivr.net https://www.googletagmanager.com https://www.google-analytics.com https://analytics.google.com https://*.google-analytics.com https://*.analytics.google.com;";

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
