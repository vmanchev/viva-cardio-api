<?php

use Phalcon\Mvc\Micro;
use Phalcon\Http\Response;
use Phalcon\Events\Manager;
use Medico\Middleware\AuthMiddleware;

require('../vendor/autoload.php');
require('../app/loader.php');
require('../app/config.php');
require('../app/services.php');

$app = new Micro($di);
$eventsManager = new Manager();

$app->setEventsManager($eventsManager);

$eventsManager->attach('micro', new AuthMiddleware());
$app->before(new AuthMiddleware());

require('../app/routers.php');
// Define the routes here
$app->get(
    '/',
    function () {
        $response = new Response();
        return $response->setJsonContent(['msg' => 'Hello, world!']);
    }
);

$app->handle();
