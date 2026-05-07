<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$registerFrontWithoutCatchAll = static function (RouteCollection $routes): void {
    $routes->get('/', 'Front\\Home::index');
    $routes->get('home', 'Front\\Home::index');

    $routes->get('about', 'Front\\Page::redirectLegacyAbout');
    $routes->get('contact', 'Front\\Page::contact');

    $routes->get('press', 'Front\\Press::index');
    $routes->get('press/(:segment)', 'Front\\Press::show/$1');

    $routes->get('join', 'Front\\Join::index');
    $routes->post('join', 'Front\\Join::submit');
};

$registerFrontCatchAll = static function (RouteCollection $routes): void {
    $routes->get('(:segment)', 'Front\\Page::show/$1');
};

$registerFrontWithoutCatchAll($routes);
$routes->get('en', 'Front\\Home::index');

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

    $routes->group('', ['filter' => 'adminonly'], static function ($routes) {
        $routes->get('login-events', 'Admin\\LoginEvents::index');
        $routes->get('login-events/export', 'Admin\\LoginEvents::exportCsv');

        $routes->get('staff-users', 'Admin\\StaffUsers::index');
        $routes->get('staff-users/create', 'Admin\\StaffUsers::create');
        $routes->post('staff-users/store', 'Admin\\StaffUsers::store');
        $routes->get('staff-users/edit/(:num)', 'Admin\\StaffUsers::edit/$1');
        $routes->post('staff-users/update/(:num)', 'Admin\\StaffUsers::update/$1');
    });
});

$registerFrontCatchAll($routes);

$routes->group('en', static function ($routes) use ($registerFrontWithoutCatchAll, $registerFrontCatchAll): void {
    $registerFrontWithoutCatchAll($routes);
    $registerFrontCatchAll($routes);
});
