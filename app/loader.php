<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces(
    [
        'Medico\Controller' => '../app/controller',
        'Medico\Model' => '../app/model',
        'Medico\Service' => '../app/service',
        'Medico\Service\Search' => '../app/service/search',
        'Medico\Middleware' => '../app/middleware'
    ]
)->register();