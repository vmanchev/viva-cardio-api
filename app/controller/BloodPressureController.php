<?php

namespace Medico\Controller;

use Medico\Model\BloodPressure;
use Medico\Model\Patient as PatientModel;
use Medico\Controller\BaseController;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Date as DateValidator;
use Phalcon\Validation\Validator\Between as BetweenValidator;

class BloodPressureController extends BaseController
{
    private $searchParameters = [
        ["name" => "patient_id", "criteria" => "patient_id = :patient_id:"],
        ["name" => "date", "criteria" => "created_at LIKE :date:"],
        ["name" => "date_from", "criteria" => "created_at >= :date_from:"],
        ["name" => "date_to", "criteria" => "created_at <= :date_to:"],
        ["name" => "sys_above", "criteria" => "sys >= :sys_above:"],
        ["name" => "sys_below", "criteria" => "sys <= :sys_below:"],
        ["name" => "dia_above", "criteria" => "dia >= :dia_above:"],
        ["name" => "dia_below", "criteria" => "dia <= :dia_below:"],
        ["name" => "pulse_above", "criteria" => "pulse >= :pulse_above:"],
        ["name" => "pulse_below", "criteria" => "pulse <= :pulse_below:"]
    ];

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

    /**
     * Query:
     * =========
     * date         | Exact date
     * date_from     | From a selected date until now
     * date_to       | Everything until the selected date
     * sys_above     | All results when systolic was above this value
     * sys_below     | All results when systolic was below this value
     * dia_above     | All results when diastolic was above this value
     * dia_below     | All results when diastolic was below this value
     * pulse_above   | All results when pulse was above this value
     * pulse_below   | All results when pulse was above this value
     * 
     * Note: When *date* is provided, date_from and date_to are ignored. Appart
     * from that rule, all other combinations can be expected.
     * 
     * 
     * Order by
     * =========
     * date
     * sys
     * dia
     * pulse
     * 
     * Order direction
     * ===============
     * asc
     * desc
     * 
     */
    public function search()
    {
        $this->setSearchParams();

        $flattenParams = $this->flattenParamsForValidation($this->searchParameters);

        $validationResult = $this->searchParamsValidation($flattenParams);
        if ($validationResult->count() > 0) {
            $errorMessages = [];

            foreach($validationResult as $message) {
                $errorMessages[] = [
                    'field' => $message->getField(),
                    'error' => $message->getMessage()
                ];
            }

            $this->response->setJsonContent([
                'errors' => $errorMessages
            ]);

            return $this->response;
        }

        if (!$this->hasAccessToPatient(($flattenParams['patient_id']))) {
            return $this->sendErrorResponse();
        }

        if (isset($flattenParams['date'])) {
            $flattenParams['date'] .= '%';
        }

        $this->response->setJsonContent([
            'records' => BloodPressure::find([
                'conditions' => $this->getSearchConditions(),
                'bind' => $flattenParams,
                'order' => $this->getOrder()
            ])
        ]);

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

    private function setSearchParams() {
        $params = $this->request->getQuery();

        $this->searchParameters = array_map(function ($searchParameter) use ($params) {

            $queryParamName = lcfirst(str_replace('_', '', ucwords($searchParameter['name'], '_')));
            $searchParameter['value'] = isset($params[$queryParamName]) ? $params[$queryParamName] : null;

            return $searchParameter;
        }, $this->searchParameters);

        $this->searchParameters = array_filter($this->searchParameters, function ($searchParameter) {
            return !empty($searchParameter['value']);
        });
    }

    private function flattenParamsForValidation(array $params): array {
        
        $flatParams = [];

        foreach($params as $param) {
            $flatParams[$param['name']] = $param['value'];
        }

        return $flatParams;
    }

    private function searchParamsValidation(array $params)
    {
        $validation = new Validation();
        // patient id is always required!
        $validation->add('patient_id', new PresenceOf(['message' => 'ERROR.PATIENT_ID.REQUIRED']));

        // The following validators will be applied only when values are provided
        $possibleValidators = [];
        $possibleValidators['date'] = new DateValidator(['format' => 'Y-m-d', 'message' => 'ERROR.DATE.INVALID']);
        $possibleValidators['date_from'] = new DateValidator(['format' => 'Y-m-d', 'message' => 'ERROR.DATE_FROM.INVALID']);
        $possibleValidators['date_to'] = new DateValidator(['format' => 'Y-m-d', 'message' => 'ERROR.DATE_TO.INVALID']);
        $possibleValidators['sys_above'] = new BetweenValidator(['minimum' => 0, 'maximum' => 300, 'message' => 'ERROR.SYS_ABOVE.INVALID']);
        $possibleValidators['sys_below'] = new BetweenValidator(['minimum' => 0, 'maximum' => 300, 'message' => 'ERROR.SYS_BELOW.INVALID']);
        $possibleValidators['dia_above'] = new BetweenValidator(['minimum' => 0, 'maximum' => 300, 'message' => 'ERROR.DIA_ABOVE.INVALID']);
        $possibleValidators['dia_below'] = new BetweenValidator(['minimum' => 0, 'maximum' => 300, 'message' => 'ERROR.DIA_BELOW.INVALID']);
        $possibleValidators['pulse_above'] = new BetweenValidator(['minimum' => 0, 'maximum' => 300, 'message' => 'ERROR.PULSE_ABOVE.INVALID']);
        $possibleValidators['pulse_below'] = new BetweenValidator(['minimum' => 0, 'maximum' => 300, 'message' => 'ERROR.PULSE_BELOW.INVALID']);

        foreach($params as $key => $value) {
            if (isset($possibleValidators[$key])) {
                $validation->add($key, $possibleValidators[$key]);
            }
        }

        return $validation->validate($params);

    }

    private function getSearchConditions(): string {
        return implode(' AND ', array_map(function($searchParameter) {
            return $searchParameter['criteria'];
        }, $this->searchParameters));
    }

    private function getOrder() {
        $params = $this->request->getQuery();

        $order = [
            'by' => 'created_at',
            'direction' => 'desc'
        ];

        if (isset($params['orderBy']) && !empty($params['orderBy'])) {
            $order['by'] = $params['orderBy'];
        }

        if (isset($params['orderDirection']) && !empty($params['orderDirection'])) {
            $order['direction'] = $params['orderDirection'];
        }

        return implode(' ', $order);
    }
}
