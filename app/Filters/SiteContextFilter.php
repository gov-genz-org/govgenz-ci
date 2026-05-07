<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\PublicNav;
use App\Libraries\SiteContext;
use App\Models\SiteNavItemModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SiteContextFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        SiteContext::setMain();

        $rawPath = trim((string) $request->getUri()->getPath(), '/');
        $segments = $rawPath === '' ? [] : explode('/', $rawPath);

        $locale = 'fr';
        if (($segments[0] ?? '') === 'en') {
            $locale = 'en';
            array_shift($segments);
        }

        SiteContext::setLocale($locale);
        SiteContext::setPublicUriSegments($segments);

        $request->setLocale($locale === 'en' ? 'en' : 'fr');

        $firstPublicSeg = $segments[0] ?? '';
        $reserved       = ['declaration', 'counterpoint', 'projects'];
        if ($firstPublicSeg !== '' && in_array($firstPublicSeg, $reserved, true)) {
            throw PageNotFoundException::forPageNotFound('Mini-site réservé à une version ultérieure.');
        }

        if ($rawPath !== '' && str_starts_with($rawPath, 'admin')) {
            SiteContext::setNavMainLinks([]);
        } else {
            helper(['url', 'locale']);
            $navModel = model(SiteNavItemModel::class);
            $rows     = $navModel->listActiveOrdered($locale);
            $links    = [];
            foreach ($rows as $r) {
                $links[] = [
                    'href'      => PublicNav::hrefFromRow($r),
                    'label'     => (string) ($r['label'] ?? ''),
                    'match_key' => (string) ($r['match_key'] ?? ''),
                    'css_class' => trim((string) ($r['css_class'] ?? '')),
                ];
            }
            SiteContext::setNavMainLinks($links);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
