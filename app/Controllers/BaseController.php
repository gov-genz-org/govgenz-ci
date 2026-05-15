<?php

namespace App\Controllers;

use App\Libraries\AdminListQuery;
use CodeIgniter\Controller;
use CodeIgniter\Model;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /** Nombre de lignes par page pour les listes d’administration. */
    protected const ADMIN_LIST_PER_PAGE = 20;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        helper(['form', 'url', 'html', 'text', 'security']);
        // $this->session = service('session');
    }

    /**
     * @param string|null $editorSelector Sélecteur CSS du textarea TinyMCE (ex. '#body_html', '#pp-body').
     */
    protected function tinymceExtraScripts(?string $editorSelector = null): string
    {
        return view('admin/partials/tinymce_init', [
            'uploadUrl'        => site_url('admin/media/upload'),
            'mediaJsonUrl'     => site_url('admin/media/json'),
            'pageUrlContact'   => site_url('contact'),
            'pageUrlPress'     => site_url('press'),
            'editorSelector'   => $editorSelector ?? '#body_html',
        ]);
    }

    /** Scripts éditeur + avertissement si fermeture avec changements non enregistrés. */
    protected function editorFormExtraScripts(): string
    {
        return $this->tinymceExtraScripts() . view('admin/partials/form_dirty_guard');
    }

    /**
     * Même bundle TinyMCE que les pages, ciblant un autre champ (ex. corps projet).
     */
    protected function editorFormExtraScriptsForSelector(string $editorSelector): string
    {
        return $this->tinymceExtraScripts($editorSelector) . view('admin/partials/form_dirty_guard');
    }

    /**
     * Locales présentes par groupe de traduction (ex. bouton « Dupliquer trad » dans les listes admin).
     *
     * @param list<array<string, mixed>> $rows
     * @return array<string, array<string, true>>
     */
    protected function translationLocalesByGroupForRows(array $rows, string $modelClass): array
    {
        $groupsOnPage = [];
        foreach ($rows as $row) {
            $g = trim((string) ($row['translation_group'] ?? ''));
            if ($g !== '') {
                $groupsOnPage[$g] = true;
            }
        }
        if ($groupsOnPage === []) {
            return [];
        }

        $out = [];
        $pairs = model($modelClass)
            ->select('translation_group, locale')
            ->whereIn('translation_group', array_keys($groupsOnPage))
            ->findAll();
        foreach ($pairs as $pair) {
            $g = trim((string) ($pair['translation_group'] ?? ''));
            if ($g === '') {
                continue;
            }
            $loc = strtolower(trim((string) ($pair['locale'] ?? '')));
            if (! in_array($loc, ['fr', 'en'], true)) {
                $loc = 'fr';
            }
            $out[$g][$loc] = true;
        }

        return $out;
    }

    /**
     * Liste admin paginée avec tri (?sort= & ?dir=).
     *
     * @param array<string, string> $allowedSorts clé URL => colonne SQL
     * @param list<string>          $pagerOnly    paramètres GET conservés dans la pagination
     * @return array{rows: list<array<string, mixed>>, pager: \CodeIgniter\Pager\Pager, sort: string, dir: string}
     */
    protected function adminPaginatedList(
        Model $model,
        array $allowedSorts,
        string $defaultSort,
        string $defaultDir = 'desc',
        array $pagerOnly = [],
        ?int $perPage = null,
        ?string $secondarySortColumn = null,
        string $secondarySortDir = 'ASC',
    ): array {
        $query = new AdminListQuery(
            $model,
            $allowedSorts,
            $defaultSort,
            $defaultDir,
            $perPage ?? static::ADMIN_LIST_PER_PAGE,
            $pagerOnly,
        );

        if ($secondarySortColumn !== null && $secondarySortColumn !== '') {
            $query->setSecondarySort($secondarySortColumn, $secondarySortDir);
        }

        return [
            'rows'  => $query->paginate(),
            'pager' => $query->pager(),
            'sort'  => $query->sortKey(),
            'dir'   => $query->sortDir(),
        ];
    }
}
