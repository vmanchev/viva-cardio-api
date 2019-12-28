<?php

namespace Medico\Controller;

use Medico\Controller\BaseController;
use Medico\Model\Patient as PatientModel;
use Medico\Service\Search\Patients as PatientService;

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
            $this->response->setJsonContent($patient->toArray());
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
            $this->response->setJsonContent($patient->toArray());
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }

    public function delete($id)
    {
        if (!$this->hasAccessToPatient($id)) {
            return $this->sendErrorResponse();
        }

        $patient = PatientModel::findFirst($id);
        
        try {
            $patient->delete();
            $this->response->setStatusCode(200);
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }

    public function search($id = null)
    {
        if ($id > 0) {
            return $this->getOne($id);
        }

        return $this->getOwnPatients();
    }

    private function getOne(int $id)
    {
        if (!$this->hasAccessToPatient($id)) {
            return $this->sendErrorResponse();
        }

        $this->response->setJsonContent([
            'patients' => [PatientModel::findFirst($id)],
        ]);

        $this->response->setStatusCode(200);
        return $this->response;
    }

    private function getOwnPatients()
    {
        $patientService = new PatientService([
            'user_id' => $this->request->loggedUser,
        ]);

        $this->response->setJsonContent($patientService->getResultSet());

        $this->response->setStatusCode(200);
        return $this->response;
    }
}
