<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Security;
use Medico\Service\JwtService;

$di = new FactoryDefault();

$di->set(
    'jwt',
    function () use ($di) {
        return new JwtService($di);
    },
    true
);

$di->set(
    'db',
    function () use ($config) {
        return new PdoMysql((array) $config->db);
    },
    true
);

$di->set(
    'security',
    function () {
        $security = new Security();

        // Set the password hashing factor to 12 rounds
        $security->setWorkFactor(12);

        return $security;
    },
    true
);

$di->set(
    'config',
    function () use ($config) {
        return [
            'hash' => $config->security->hash,
            'apiUrl' => $config->env->apiUrl,
            'title' => $config->env->title,
        ];
    },
    true
);
