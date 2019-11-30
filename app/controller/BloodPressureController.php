<?php

namespace Medico\Controller;

use Medico\Controller\BaseController;
use Medico\Service\Search\BloodPressure as BloodPressureSearch;

class BloodPressureController extends BaseController
{
    public function create()
    {
        $data = json_decode($this->request->getRawBody());

        if (!$this->hasAccessToPatient($data->patient_id)) {
            return $this->sendErrorResponse();
        }

        $bpModel = new BloodPressure();
        try {
            if ($bpModel->create((array) $data)) {
                $this->response->setStatusCode(201);
            } else {
                foreach ($bpModel->getMessages() as $message) {
                    echo $message;
                }
                exit;
            }
            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }

    public function search()
    {
        $bloodPressureSearch = new BloodPressureSearch($this->request->getQuery());

        if (!$bloodPressureSearch->isValid()) {
            $this->response->setJsonContent([
                'errors' => $bloodPressureSearch->getErrorMessages()
            ]);
            return $this->sendErrorResponse();
        }

        if (!$this->hasAccessToPatient($this->request->getQuery('patientId'))) {
            return $this->sendErrorResponse();
        }

        $this->response->setJsonContent($bloodPressureSearch->getResultSet());

        return $this->response;
    }

    public function delete($id)
    {
        $bpModel = BloodPressure::findFirst($id);

        if (!$bpModel) {
            $this->response->setStatusCode(404);
            return $this->response;
        }

        if (!$this->hasAccessToPatient($bpModel->patient_id)) {
            return $this->sendErrorResponse();
        }

        $bpModel->delete();

        $this->response->setStatusCode(200);
        return $this->response;
    }
}
