<?php

namespace Medico\Controller;

use Medico\Controller\BaseController;
use Medico\Model\Patient as PatientModel;

class PatientController extends BaseController
{

    public function create()
    {
        $data = json_decode($this->request->getRawBody());
        $data->user_id = $this->request->loggedUser;

        $patient = new PatientModel();

        try {
            $patient->save((array) $data);
            $this->response->setStatusCode(201);
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }

    public function update($id)
    {
        $data = json_decode($this->request->getRawBody());

        if (!$this->hasAccessToPatient($id)) {
            return $this->sendErrorResponse();
        }

        $patient = PatientModel::findFirst($id);
        $patient->name = $data->name;

        try {
            $patient->update();
            $this->response->setStatusCode(200);
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }
}
