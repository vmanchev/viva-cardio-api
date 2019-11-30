<?php

namespace Medico\Controller;

use Medico\Controller\BaseController;
use Medico\Model\Share as ShareModel;
use Phalcon\Security\Random;

class ShareController extends BaseController
{

    public function create($patient_id)
    {
        $data = json_decode($this->request->getRawBody());

        if (!$this->hasAccessToPatient($patient_id)) {
            return $this->sendErrorResponse();
        }

        $shareModel = new ShareModel();

        try {
            // generate a UUID v4 code
            $code = (new Random())->uuid();

            $shareModel->save([
                'id' => $code,
                'patient_id' => $patient_id
            ]);

            $this->response->setStatusCode(201);
            $this->response->setJsonContent([
                'code' => $code
            ]);

            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }

    public function delete($patient_id, $code)
    {
        if (!$this->hasAccessToPatient($patient_id)) {
            return $this->sendErrorResponse();
        }

        $share = ShareModel::findFirst([
            'conditions' => 'id = :id: AND patient_id = :patient_id:',
            'bind' => [
                'patient_id' => $patient_id,
                'id' => $code
            ]
        ]);

        if (!$share) {
            return $this->sendErrorResponse();
        }

        try {
            $share->delete();
            $this->response->setStatusCode(200);

            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }
}
