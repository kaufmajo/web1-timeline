<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/:id', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/:id', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {

    // root

    $app->get('/', [App\Handler\Termin\Def\TerminCalendarHandler::class,], 'default.root');

    // app test

    $app->route('/test', [
        App\Handler\Home\Def\TestHandler::class,
    ], ['GET'], 'default.app.test');

    // app cleanup

    $app->route('/cleanup', [
        Mezzio\Authentication\AuthenticationMiddleware::class,
        Mezzio\Authorization\AuthorizationMiddleware::class,
        App\Middleware\TemplateDefaultsMiddleware::class,
        App\Handler\Home\Def\CleanupHandler::class,
    ], ['GET'], 'default.app.cleanup');

    // app media

    $app->route('/media/{p1:[0-9]+}', [App\Handler\Media\Def\MediaIndexHandler::class,], ['GET'], 'default.media.index');

    // app termin

    $app->route('/ics[/{p1:[0-9]+}]', [App\Handler\Termin\Def\TerminIcsHandler::class,], ['GET'], 'default.termin.ics');
    $app->route('/search', [App\Handler\Termin\Def\TerminSearchHandler::class,], ['GET'], 'default.termin.search');
    $app->route('/show/{p1:[0-9]+}', [App\Handler\Termin\Def\TerminShowHandler::class,], ['GET'], 'default.termin.show');

    // manage home

    $app->route('/manage/home-read', [App\Handler\Home\Mng\HomeReadHandler::class,], ['GET'], 'manage.home.read');
    $app->route('/manage/home-initconfig', [App\Handler\Home\Mng\HomeInitconfigHandler::class,], ['GET'], 'manage.home.initconfig');
    $app->route('/manage/home-phpinfo', [App\Handler\Home\Mng\HomePhpinfoHandler::class,], ['GET'], 'manage.home.phpinfo');
    $app->route('/manage/home-tabellen', [App\Handler\Home\Mng\HomeTabellenHandler::class,], ['GET'], 'manage.home.tabellen');
    $app->route('/manage/home-stats', [App\Handler\Home\Mng\HomeStatsHandler::class,], ['GET'], 'manage.home.stats');

    // manage media

    $app->route('/manage/media-read', [App\Handler\Media\Mng\MediaReadHandler::class,], ['GET'], 'manage.media.read');
    $app->route('/manage/media-version[/{p1:[0-9]+}]', [App\Handler\Media\Mng\MediaVersionHandler::class,], ['GET'], 'manage.media.version');
    $app->route('/manage/media-delete[/{p1:[0-9]+}]', [App\Handler\Media\Mng\MediaDeleteHandler::class,], ['GET', 'POST'], 'manage.media.delete');
    $app->route('/manage/media-insert[/{p1:[0-9]+}]', [App\Handler\Media\Mng\MediaInsertHandler::class,], ['GET', 'POST'], 'manage.media.insert');
    $app->route('/manage/media-update[/{p1:[0-9]+}]', [App\Handler\Media\Mng\MediaUpdateHandler::class,], ['GET', 'POST'], 'manage.media.update');

    // manage termin

    $app->route('/manage/termin-calendar', [App\Handler\Termin\Mng\TerminCalendarHandler::class,], ['GET'], 'manage.termin.calendar');
    $app->route('/manage/termin-search', [App\Handler\Termin\Mng\TerminSearchHandler::class,], ['GET'], 'manage.termin.search');
    $app->route('/manage/termin-copy[/{p1:[0-9\-]+}]', [App\Handler\Termin\Mng\TerminCopyHandler::class,], ['GET', 'POST'], 'manage.termin.copy');
    $app->route('/manage/termin-delete[/{p1:[0-9\-]+}]', [App\Handler\Termin\Mng\TerminDeleteHandler::class,], ['GET', 'POST'], 'manage.termin.delete');
    $app->route('/manage/termin-insert[/{p1:[0-9\-]+}]', [App\Handler\Termin\Mng\TerminInsertHandler::class,], ['GET', 'POST'], 'manage.termin.insert');
    $app->route('/manage/termin-update[/{p1:[0-9\-]+}]', [App\Handler\Termin\Mng\TerminUpdateHandler::class,], ['GET', 'POST'], 'manage.termin.update');

    // app auth

    $app->route('/app-login', [App\Handler\Auth\Def\LoginHandler::class,], ['GET', 'POST'], 'default.app.login');
    $app->route('/app-logout', [App\Handler\Auth\Def\LogoutHandler::class,], ['GET'], 'default.app.logout');
};
