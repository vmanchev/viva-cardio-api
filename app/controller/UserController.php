<?php

namespace Medico\Controller;

use Phalcon\Security\Random;
use Medico\Model\User as UserModel;
use Medico\Controller\BaseController;

class UserController extends BaseController
{
    public function register()
    {
        $data = json_decode($this->request->getRawBody());
        $data->password = $this->security->hash($data->password);

        $user = new UserModel();

        try {
            $user->save((array) $data);
            $this->response->setStatusCode(201);
            return $this->login();
        } catch (\Exception $e) {
            return $this->sendErrorResponse();
        }
    }

    public function login()
    {
        $data = json_decode($this->request->getRawBody());
        $user = UserModel::findFirstByEmail($data->email);

        if (!$user) {
            $this->response->setStatusCode(404);
            return $this->response;
        }

        if (!$this->security->checkHash($data->password, $user->password)) {
            // @todo invalid password
            return $this->sendErrorResponse();
        }

        $this->response->setJsonContent([
            'token' => $this->jwt::encode($user->id, $this->config['hash'])
        ]);

        return $this->response;
    }

    public function forgot()
    {

        $data = json_decode($this->request->getRawBody());
        $user = UserModel::findFirstByEmail($data->email);

        if (!$user) {
            $this->response->setStatusCode(404);
            return $this->response;
        }

        $password = (new Random())->base64Safe(4);

        $user->password = $this->security->hash($password);

        try {
            if ($user->save()) {
                $this->emailer->send(
                    $user->email,
                    'New password',
                    'This is your new password: ' . $password
                );
            }
        } catch (\Exception $e) {
            return $this->sendErrorResponse();
        }
    }
}
