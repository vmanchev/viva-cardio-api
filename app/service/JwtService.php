<?php

namespace Medico\Service;

use Phalcon\Security\Random;
use Phalcon\Di;
use Phalcon\DiInterface;
use \Firebase\JWT\JWT;

class JwtService
{

    protected $di;

    public function __construct($di)
    {
        $this->di = $di;
    }

    public function encode($id, $key)
    {
        return \Firebase\JWT\JWT::encode(
            [
                'iss' => $_SERVER['REMOTE_ADDR'],
                'sub' => $id,
                'exp' => (new \DateTime())->add(new \DateInterval('PT10H'))->getTimestamp(),
                'iat' => (new \DateTime())->format('r')
            ],
            $key
        );
    }

    public function getUserId($token)
    {

        try {
            $payload = JWT::decode(
                str_replace('Bearer ', '', $token),
                $this->di->get('config')['hash'],
                ['HS256']
            );

            return $payload->sub;
        } catch (ExpiredException $e) {
            return false;
        }
    }
}
