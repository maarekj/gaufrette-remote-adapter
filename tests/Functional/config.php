<?php

use Jma\GaufretteRemoteAdapter\Security\UserProvider;

$app['gaufrette.adapter'] = $app->share(function ($app) {
    return new \Gaufrette\Adapter\InMemory([
        'file1' => 'file1',
        'file2' => 'file2',
        'file3' => 'file3',
    ]);
});

$app['security.userprovider'] = $app->share(function ($app) {
    return new UserProvider([
        'test' => [
            'password' => 'test',
            'roles' => ['ROLE_USER'],
            'adapter' => function () use ($app) {
                    return $app['gaufrette.adapter'];
                }
        ]
    ]);
});

$app['debug'] = true;