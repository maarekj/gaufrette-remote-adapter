<?php

/* @var $app \Silex\Application */

use Silex\Provider\SecurityServiceProvider;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;

$app->register(new SecurityServiceProvider(), array(
    'security.encoder.digest' => $app->share(function () {
            return new PlaintextPasswordEncoder();
        }),
    'security.firewalls' => array(
        'secured' => array(
            'pattern' => '^.*$',
            'http' => true,
            'users' => $app->share(function ($app) {
                    return $app['security.userprovider'];
                })
        )
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array('ROLE_ALLOWED_TO_SWITCH'),
        'ROLE_USER' => array()
    ),
    'security.access_rules' => array(
        array('^/.*', 'ROLE_USER'),
    )
));