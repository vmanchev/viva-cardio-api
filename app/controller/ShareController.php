<?php

namespace Medico\Controller;

use Medico\Controller\BaseController;
use Medico\Model\Share as ShareModel;
use Phalcon\Security\Random;
use CodeItNow\BarcodeBundle\Utils\QrCode;

class ShareController extends BaseController
{

    public function create($patient_id, $qr = false)
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

            $body = [
                'code' => $code
            ];

            if ($qr) {
                $body['qr'] = $this->generateQrCode($code);
            }

            $this->response->setJsonContent($body);

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

    public function getQrCode($patient_id, $code)
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
            $this->response->setJsonContent([
                'code' => $code,
                'qr' => $this->generateQrCode($code)
            ]);
            $this->response->setStatusCode(200);

            return $this->response;
        } catch (\Exception $e) {
            $this->response->setStatusCode(409, $e->getMessage());
            return $this->response;
        }
    }

    private function generateQrCode(string $code): string
    {
        $qrCode = new QrCode();
        $qrCode
            ->setText($this->config['apiUrl'] . '/s/' . $code)
            ->setSize(200)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel($this->config['title'])
            ->setLabelFontSize(16)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        return 'data:'.$qrCode->getContentType().';base64,'.$qrCode->generate();
    }
}
