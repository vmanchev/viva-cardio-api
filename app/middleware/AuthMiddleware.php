<?php

namespace Medico\Middleware;

use Phalcon\Mvc\Micro;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro\MiddlewareInterface;


/**
 * AuthMiddleware
 *
 * Caches pages to reduce processing
 */
class AuthMiddleware implements MiddlewareInterface
{
    /**
     * Before anything happens
     *
     * @param Event $event
     * @param Micro $app
     *
     * @returns bool
     */
    public function beforeHandleRoute(Event $event, Micro $app)
    {
        $publicUrls = [
            '/user/login',
            '/user/forgot',
            '/user'
        ];

        if (!in_array($app->request->getURI(), $publicUrls) && !$app->request->getHeader('Authorization')) {
            $app->response->setStatusCode(401);
            return false;
        }

        if ($app->request->getHeader('Authorization')) {

            $app->request->loggedUser = $app->jwt->getUserId($app->request->getHeader('Authorization'));

            if (!$app->request->loggedUser) {
                $app->response->setStatusCode(403);
                return false;
            }
        }
    }

    /**
     * Calls the middleware
     *
     * @param Micro $app
     *
     * @returns bool
     */
    public function call(Micro $app)
    {
        return true;
    }
}
