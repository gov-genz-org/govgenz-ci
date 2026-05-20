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
        $rawPath = trim((string) $request->getUri()->getPath(), '/');
        $segments = $rawPath === '' ? [] : explode('/', $rawPath);

        $locale = 'fr';
        if (($segments[0] ?? '') === 'en') {
            $locale = 'en';
            array_shift($segments);
        }

        $firstAfterLocale = $segments[0] ?? '';
        $projectsPrefix   = SiteContext::projectsPathPrefixEnabled();
        $positionsPrefix  = SiteContext::positionsPathPrefixEnabled();
        $byProjectsHost   = SiteContext::httpHostMatchesProjectsHost($request);
        $byProjectsPath   = $projectsPrefix && $firstAfterLocale === 'projects';
        $byPositionsHost  = SiteContext::httpHostMatchesPositionsHost($request);
        $byPositionsPath  = $positionsPrefix && $firstAfterLocale === 'positions';

        if ($byProjectsHost || $byProjectsPath) {
            SiteContext::setProjects();
            if ($byProjectsPath) {
                array_shift($segments);
            }
        } elseif ($byPositionsHost || $byPositionsPath) {
            SiteContext::setPositions();
            if ($byPositionsPath) {
                array_shift($segments);
            }
        } else {
            SiteContext::setMain();
        }

        SiteContext::setLocale($locale);
        SiteContext::setPublicUriSegments($segments);

        $request->setLocale($locale === 'en' ? 'en' : 'fr');

        $reserved = ['declaration', 'counterpoint'];
        if (
            SiteContext::id() === SiteContext::SITE_MAIN
            && $firstAfterLocale !== ''
            && in_array($firstAfterLocale, $reserved, true)
        ) {
            throw PageNotFoundException::forPageNotFound('Mini-site réservé à une version ultérieure.');
        }

        if (
            SiteContext::id() === SiteContext::SITE_MAIN
            && $firstAfterLocale === 'projects'
            && ! $projectsPrefix
        ) {
            throw PageNotFoundException::forPageNotFound('Mini-site réservé à une version ultérieure.');
        }

        if (
            SiteContext::id() === SiteContext::SITE_MAIN
            && $firstAfterLocale === 'positions'
            && ! $positionsPrefix
        ) {
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
