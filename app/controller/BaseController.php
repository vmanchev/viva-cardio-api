<?php

namespace Medico\Controller;

use Medico\Model\Patient as PatientModel;
use Phalcon\Mvc\Controller;

class BaseController extends Controller
{
    /**
     * Does the authenticated user has access to this patient?
     */
    protected function hasAccessToPatient($patientId) {
        $hasAccess = !!PatientModel::findFirst([
            'conditions' => 'id = ?1 AND user_id = ?2',
            'bind' => [
                1 => $patientId,
                2 => $this->request->loggedUser
            ]
        ]);

        return !!$hasAccess;
    }

    protected function sendErrorResponse() {
        $this->response->setStatusCode(409);
        return $this->response;
    }
}
