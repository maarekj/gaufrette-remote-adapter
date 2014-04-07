<?php

$app['baseDir'] = __DIR__.'/../../web/uploads';

$app['gaufrette.adapter'] = $app->share(function () use ($app) {
    return new \Gaufrette\Adapter\Local($app['baseDir'], true);
});
