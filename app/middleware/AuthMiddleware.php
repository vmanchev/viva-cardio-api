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
        if (!$this->allowFreeAccess($app) && !$app->request->getHeader('Authorization')) {
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

    private function allowFreeAccess(Micro $app): bool
    {
        $publicUrls = [
            '/^\/user\/login$/',
            '/^\/user\/forgot$/',
            '/^\/user$/',
            '/^\/s\/[0-9A-F]{8}-[0-9A-F]{4}-[4][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i'
        ];


        $matchedUrls = array_map(function ($pattern) use ($app) {
            return !!preg_match($pattern, $app->request->getURI());
        }, $publicUrls);

        return !!count(array_filter($matchedUrls));
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
