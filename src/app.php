<?php

/* @var $app \Silex\Application */

use Jma\GaufretteRemoteAdapter\Controller\MainController;
use Symfony\Component\HttpFoundation\Request;

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

require_once __DIR__ . '/../src/security.php';

$app['controllers.main'] = $app->share(function () use ($app) {
    $adapter = $app['security']->getToken()->getUser()->getGaufretteAdapter();
    return new MainController($adapter);
});

$app->get('/keys', 'controllers.main:keysAction');
$app->get('/read/{key}', 'controllers.main:readAction')->assert('key', '.*');
$app->get('/meta/{key}', 'controllers.main:metaAction')->assert('key', '.*');
$app->get('/exists/{key}', 'controllers.main:existsAction')->assert('key', '.*');


$app->get('/download/{key}', function ($key, Request $request) use ($app) {
    return $app['controllers.main']->downloadAction($key, $request->get('force', false));
})
    ->assert('key', '.*');

$app->post('/write', function (Request $request) use ($app) {
    return $app['controllers.main']->writeAction($request->get('key'), $request->get('content'));
});

$app->post('/rename', function (Request $request) use ($app) {
    return $app['controllers.main']->renameAction($request->get('sourceKey'), $request->get('targetKey'));
});

$app->post('/delete', function (Request $request) use ($app) {
    return $app['controllers.main']->deleteAction($request->get('key'));
});
