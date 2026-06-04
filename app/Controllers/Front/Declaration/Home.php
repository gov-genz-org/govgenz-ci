<?php

declare(strict_types=1);

namespace App\Controllers\Front\Declaration;

use App\Controllers\BaseController;
use App\Controllers\Front\Traits\ProgramListFrontTrait;
use App\Libraries\CmsBodyBlocksRenderer;
use App\Libraries\CmsProgramListHero;
use App\Libraries\DeclarationProgramPage;
use App\Libraries\FrontPageAssets;
use App\Libraries\ProgramListDeclarationStats;
use App\Libraries\ProjectShareQrGenerator;
use App\Libraries\SiteContext;
use App\Models\CmsPageModel;
use App\Models\DeclarationItemModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    use ProgramListFrontTrait;

    public function index()
    {
        helper(['cms', 'language', 'locale', 'declaration']);

        $listPage = model(CmsPageModel::class)->getPublishedBySlug(cms_declaration_list_page_slug());
        if ($listPage === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $hero = CmsProgramListHero::resolve(
            $listPage,
            lang('Declaration.default_list_title'),
            lang('Declaration.default_layout_title'),
        );
        if ($hero['heroOverline'] === '') {
            $hero['heroOverline'] = lang('Declaration.default_overline');
        }
        if ($hero['heroLead'] === '') {
            $hero['heroLead'] = lang('Declaration.default_lead');
        }

        $items     = model(DeclarationItemModel::class)->listPublishedForProgram();
        $split     = declaration_split_items_by_section($items);
        $stats     = ProgramListDeclarationStats::fromItems($items);
        $partition = DeclarationProgramPage::bodyFromPage($listPage);
        $staticSplit = DeclarationProgramPage::splitStaticAroundPartnerships($partition['body']);
        $staticBefore = CmsBodyBlocksRenderer::render($staticSplit['before']);
        $staticAfter  = CmsBodyBlocksRenderer::render($staticSplit['after']);

        return view('front/layout', [
            'title'           => $hero['layoutTitle'],
            'metaDescription' => $hero['layoutMeta'],
            'extraHead'       => FrontPageAssets::declarationProgramList(),
            'main'            => view('front/declaration/home', [
                'heroOverline'     => $hero['heroOverline'],
                'heroTitle'        => $hero['heroTitle'],
                'heroLead'         => $hero['heroLead'],
                'stats'            => $stats,
                'declarationItems'  => $split['declarations'],
                'partnershipItems'  => $split['partnerships'],
                'staticBodyBefore'  => $staticBefore,
                'staticBodyAfter'   => $staticAfter,
            ]),
            'navActive'      => 'declaration',
            'mainExtraClass' => $this->programListMainExtraClass($listPage),
        ]);
    }

    public function show(string $slug)
    {
        helper(['cms', 'language', 'locale', 'declaration']);

        $locale = SiteContext::locale();
        $slug   = strtolower(trim($slug, '/'));
        if ($slug === '' || preg_match('/^[a-z0-9\-]+$/', $slug) !== 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $item = model(DeclarationItemModel::class)->findPublishedBySlug($slug, $locale);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderShow($item, $slug);
    }

    public function tail(string $path)
    {
        helper(['locale', 'language', 'declaration']);
        $locale = SiteContext::locale();

        $path     = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (count($segments) === 1) {
            $slug = $segments[0];
            $item = model(DeclarationItemModel::class)->findPublishedBySlug($slug, $locale);
            if ($item !== null) {
                return $this->renderShow($item, $slug);
            }
            if (preg_match('/^[a-z0-9\-]+$/', $slug) === 1) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        throw PageNotFoundException::forPageNotFound();
    }

    public function shareQrImage(string $slug): ResponseInterface
    {
        helper('declaration');
        $slug   = strtolower(trim($slug, '/'));
        $locale = SiteContext::locale();
        $item   = model(DeclarationItemModel::class)->findPublishedBySlug($slug, $locale);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $targetUrl = declaration_public_absolute_url($slug);

        try {
            $png = ProjectShareQrGenerator::generate($targetUrl, 512);
        } catch (\Throwable $e) {
            log_message('error', 'declaration shareQrImage [{slug}]: {msg}', [
                'slug' => $slug,
                'msg'  => $e->getMessage(),
            ]);
            throw PageNotFoundException::forPageNotFound();
        }

        $cacheMaxAge = ENVIRONMENT === 'development' ? 60 : 86400;

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'public, max-age=' . $cacheMaxAge)
            ->setBody($png);
    }

    public function shareQrPage(string $slug): string
    {
        helper(['language', 'declaration', 'locale']);
        $slug   = strtolower(trim($slug, '/'));
        $locale = SiteContext::locale();
        $item   = model(DeclarationItemModel::class)->findPublishedBySlug($slug, $locale);
        if ($item === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $title      = (string) ($item['title'] ?? '');
        $qrImageUrl = declaration_share_qr_image_url($slug);
        $pageTitle  = lang('Projects.share_qr_page_title', ['title' => $title]);
        $ogDesc     = lang('Projects.share_qr_page_description', ['title' => $title]);

        $extraHead = '<meta property="og:type" content="website">'
            . '<meta property="og:title" content="' . esc($pageTitle, 'attr') . '">'
            . '<meta property="og:description" content="' . esc($ogDesc, 'attr') . '">'
            . '<meta property="og:image" content="' . esc($qrImageUrl, 'attr') . '">'
            . '<meta property="og:image:type" content="image/png">'
            . '<meta property="og:url" content="' . esc(declaration_share_qr_page_url($slug), 'attr') . '">'
            . '<meta name="twitter:card" content="summary_large_image">'
            . '<meta name="twitter:image" content="' . esc($qrImageUrl, 'attr') . '">'
            . '<link rel="stylesheet" href="' . esc(public_asset_url('assets/css/projects-program-show.css'), 'attr') . '">';

        return view('front/layout', [
            'title'           => $pageTitle,
            'metaDescription' => $ogDesc,
            'extraHead'       => $extraHead,
            'main'            => view('front/declaration/share_qr', [
                'item'             => $item,
                'title'            => $title,
                'qrImageUrl'       => $qrImageUrl,
                'declarationUrl'   => declaration_public_absolute_url($slug),
                'declarationHref'  => declaration_public_url($slug),
            ]),
            'navActive'      => 'declaration',
            'mainExtraClass' => 'ggz-layout-full',
        ]);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function renderShow(array $item, string $slug): string
    {
        $locale = SiteContext::locale();

        $title = trim((string) ($item['title'] ?? ''));
        $meta  = trim((string) ($item['summary'] ?? ''));
        if (mb_strlen($meta) > 160) {
            $meta = mb_substr($meta, 0, 157) . '…';
        }

        $itemId = (int) ($item['id'] ?? 0);
        $relatedDeclarations = $itemId > 0
            ? model(DeclarationItemModel::class)->listRelatedPublished(
                $itemId,
                $locale,
                (string) ($item['list_section'] ?? DeclarationItemModel::SECTION_DECLARATIONS),
                4,
            )
            : [];

        $shareUrl        = declaration_public_absolute_url($slug);
        $shareQrImageUrl = declaration_share_qr_image_url($slug);
        $shareQrPageUrl  = declaration_share_qr_page_url($slug);

        $declarationAssets = FrontPageAssets::declarationProgramShow();

        return view('front/layout', [
            'title'           => $title !== '' ? $title : lang('Declaration.default_list_title'),
            'metaDescription' => $meta,
            'extraHead'       => $declarationAssets['head'],
            'extraScripts'    => $declarationAssets['scripts'],
            'main'            => view('front/declaration/show', [
                'item'                => $item,
                'slug'                => $slug,
                'declarationListUrl'  => declaration_list_url(),
                'shareUrl'            => $shareUrl,
                'shareQrImageUrl'     => $shareQrImageUrl,
                'shareQrPageUrl'      => $shareQrPageUrl,
                'relatedDeclarations' => $relatedDeclarations,
                'actionCtas'          => declaration_show_action_ctas($item, $title),
                'bodyHtml'            => declaration_body_html($item, $locale),
            ]),
            'navActive'      => 'declaration',
            'mainExtraClass' => 'ggz-layout-full',
        ]);
    }
}
