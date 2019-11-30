<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces(
    [
        'Medico\Controller' => '../app/controller',
        'Medico\Model' => '../app/model',
        'Medico\Service' => '../app/service',
        'Medico\Middleware' => '../app/middleware'
    ]
)->register();