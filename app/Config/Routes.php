<?php

use App\Libraries\SiteContext;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$registerFrontWithoutCatchAll = static function (RouteCollection $routes): void {
    $pathPrefix      = SiteContext::projectsPathPrefixEnabled();
    $onProjectsVhost = ! $pathPrefix && trim((string) env('app.projectsHost', '')) !== '' && SiteContext::httpHostMatchesProjectsHost();
    $frontHomeIndex  = $onProjectsVhost ? 'Front\\Projects\\Home::index' : 'Front\\Home::index';

    $routes->get('/', $frontHomeIndex);
    $routes->get('home', $frontHomeIndex);

    $routes->get('about', 'Front\\Page::redirectLegacyAbout');
    $routes->get('contact', 'Front\\Page::contact');

    $routes->get('press', 'Front\\Press::index');
    $routes->get('press/(:segment)', 'Front\\Press::show/$1');

    $routes->get('secteurs', 'Front\\Sectors::index');
    $routes->get('sectors', 'Front\\Sectors::index');

    $routes->get('join', 'Front\\Join::index');
    $routes->post('join', 'Front\\Join::submit');

    if (SiteContext::projectsPathPrefixEnabled()) {
        $routes->get('projects', 'Front\\Projects\\Home::index');
        $routes->get('projects/(.+)', 'Front\\Projects\\Home::tail/$1');
        $routes->post('projects/filter', 'Front\\Projects\\Home::filterPost');
    }
    if ($onProjectsVhost) {
        $routes->post('filter', 'Front\\Projects\\Home::filterPost');
    }
};

$registerFrontCatchAll = static function (RouteCollection $routes): void {
    $routes->get('(:segment)', 'Front\\Page::show/$1');
};

$registerFrontWithoutCatchAll($routes);

// /en seul : accueil anglais du site courant (principal vs vhost projets — ne pas forcer Front\Home sur projects.*).
$pathPrefixForEnRoute     = SiteContext::projectsPathPrefixEnabled();
$onProjectsVhostForEnRoute = ! $pathPrefixForEnRoute
    && trim((string) env('app.projectsHost', '')) !== ''
    && SiteContext::httpHostMatchesProjectsHost();
$englishRootController = $onProjectsVhostForEnRoute ? 'Front\\Projects\\Home::index' : 'Front\\Home::index';
$routes->get('en', $englishRootController);

$routes->get('admin/login', 'Admin\\Auth::loginForm');
$routes->post('admin/login', 'Admin\\Auth::loginAttempt');
$routes->get('admin/logout', 'Admin\\Auth::logout');
$routes->post('admin/logout', 'Admin\\Auth::logout');

$routes->group('admin', ['filter' => 'authadmin'], static function ($routes) {
    $routes->get('/', 'Admin\\Dashboard::index');

    $routes->get('pages/preview/(:num)', 'Admin\\Preview::page/$1');
    $routes->post('pages/preview-draft/(:num)', 'Admin\\Preview::pageDraft/$1');
    $routes->get('posts/preview/(:num)', 'Admin\\Preview::post/$1');

    $routes->get('cms-guide', 'Admin\\CmsComponentsGuide::index');

    $routes->get('pages', 'Admin\\Pages::index');
    $routes->get('pages/create', 'Admin\\Pages::create');
    $routes->post('pages/store', 'Admin\\Pages::store');
    $routes->get('pages/edit/(:num)', 'Admin\\Pages::edit/$1');
    $routes->post('pages/update/(:num)', 'Admin\\Pages::update/$1');
    $routes->post('pages/duplicate/(:num)', 'Admin\\Pages::duplicate/$1');
    $routes->post('pages/delete/(:num)', 'Admin\\Pages::delete/$1');

    $routes->get('site-menu', 'Admin\\SiteMenu::index');
    $routes->get('site-menu/create', 'Admin\\SiteMenu::create');
    $routes->post('site-menu/store', 'Admin\\SiteMenu::store');
    $routes->get('site-menu/edit/(:num)', 'Admin\\SiteMenu::edit/$1');
    $routes->post('site-menu/update/(:num)', 'Admin\\SiteMenu::update/$1');
    $routes->post('site-menu/delete/(:num)', 'Admin\\SiteMenu::delete/$1');

    $routes->get('posts', 'Admin\\Posts::index');
    $routes->get('posts/create', 'Admin\\Posts::create');
    $routes->post('posts/store', 'Admin\\Posts::store');
    $routes->get('posts/edit/(:num)', 'Admin\\Posts::edit/$1');
    $routes->post('posts/update/(:num)', 'Admin\\Posts::update/$1');
    $routes->post('posts/duplicate/(:num)', 'Admin\\Posts::duplicate/$1');
    $routes->post('posts/delete/(:num)', 'Admin\\Posts::delete/$1');

    $routes->get('media', 'Admin\\Media::index');
    $routes->get('media/json', 'Admin\\Media::jsonList');
    $routes->post('media/upload', 'Admin\\Media::upload');
    $routes->post('media/delete/(:num)', 'Admin\\Media::delete/$1');

    $routes->get('volunteers', 'Admin\\Volunteers::index');
    $routes->post('volunteers/status/(:num)', 'Admin\\Volunteers::setStatus/$1');

    $routes->get('sectors', 'Admin\\Sectors::index');
    $routes->get('sectors/create', 'Admin\\Sectors::create');
    $routes->post('sectors/store', 'Admin\\Sectors::store');
    $routes->get('sectors/edit/(:num)', 'Admin\\Sectors::edit/$1');
    $routes->post('sectors/update/(:num)', 'Admin\\Sectors::update/$1');
    $routes->post('sectors/delete/(:num)', 'Admin\\Sectors::delete/$1');

    $routes->get('project-projects', 'Admin\\ProjectProjects::index');
    $routes->get('project-projects/create', 'Admin\\ProjectProjects::create');
    $routes->post('project-projects/store', 'Admin\\ProjectProjects::store');
    $routes->get('project-projects/edit/(:num)', 'Admin\\ProjectProjects::edit/$1');
    $routes->post('project-projects/update/(:num)', 'Admin\\ProjectProjects::update/$1');
    $routes->post('project-projects/duplicate/(:num)', 'Admin\\ProjectProjects::duplicate/$1');
    $routes->post('project-projects/delete/(:num)', 'Admin\\ProjectProjects::delete/$1');

    $routes->get('project-exchange-rates', 'Admin\\ProjectExchangeRates::edit');
    $routes->post('project-exchange-rates/update', 'Admin\\ProjectExchangeRates::update');

    $routes->get('geo/regions', 'Admin\\GeoCatalog::regions');
    $routes->get('geo/districts', 'Admin\\GeoCatalog::districts');
    $routes->get('geo/communes', 'Admin\\GeoCatalog::communes');
    $routes->get('geo/fokontany', 'Admin\\GeoCatalog::fokontany');

    $routes->group('', ['filter' => 'adminonly'], static function ($routes) {
        $routes->post('volunteers/clear-table', 'Admin\\Volunteers::clearTable');

        $routes->get('login-events', 'Admin\\LoginEvents::index');
        $routes->get('login-events/export', 'Admin\\LoginEvents::exportCsv');
        $routes->post('login-events/clear-table', 'Admin\\LoginEvents::clearTable');

        $routes->get('staff-users', 'Admin\\StaffUsers::index');
        $routes->get('staff-users/create', 'Admin\\StaffUsers::create');
        $routes->post('staff-users/store', 'Admin\\StaffUsers::store');
        $routes->get('staff-users/edit/(:num)', 'Admin\\StaffUsers::edit/$1');
        $routes->post('staff-users/update/(:num)', 'Admin\\StaffUsers::update/$1');
        $routes->post('staff-users/delete/(:num)', 'Admin\\StaffUsers::delete/$1');
        $routes->post('staff-users/clear-table', 'Admin\\StaffUsers::clearTable');
    });
});

$registerFrontCatchAll($routes);

$routes->group('en', static function ($routes) use ($registerFrontWithoutCatchAll, $registerFrontCatchAll): void {
    $registerFrontWithoutCatchAll($routes);
    $registerFrontCatchAll($routes);
});
