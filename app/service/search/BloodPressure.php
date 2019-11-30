<?php

namespace Medico\Service\Search;

use Medico\Model\BloodPressure as BloodPressureModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Date as DateValidator;
use Phalcon\Validation\Validator\Between as BetweenValidator;

class BloodPressure
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

    // Search query as received in Request object
    private $queryParams;

    // Flat key/value pairs representation of search params
    private $flattenParams = [];

    private $errorMessages = [];

    private $order = [
        'by' => 'created_at',
        'direction' => 'desc'
    ];

    function __construct(array $params)
    {
        $this->queryParams = $params;
        $this->setSearchParams()
            ->flattenParamsForValidation()
            ->validate();
    }

    public function isValid(): bool
    {
        return empty($this->errorMessages);
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function getResultSet()
    {
        return [
            'items' => BloodPressureModel::find([
                'conditions' => $this->getSearchConditions(),
                'bind' => $this->getBindings(),
                'order' => $this->getOrder()
            ])
        ];
    }

    private function getSearchConditions(): string
    {
        return implode(' AND ', array_map(function ($searchParameter) {
            return $searchParameter['criteria'];
        }, $this->searchParameters));
    }

    private function getBindings()
    {
        if (isset($this->flattenParams['date'])) {
            $this->flattenParams['date'] .= '%';
        }

        return $this->flattenParams;
    }

    private function getOrder()
    {
        if (isset($this->flattenParams['orderBy']) && !empty($this->flattenParams['orderBy'])) {
            $this->order['by'] = $this->flattenParams['orderBy'];
        }

        if (isset($this->flattenParams['orderDirection']) && !empty($this->flattenParams['orderDirection'])) {
            $this->order['direction'] = $this->flattenParams['orderDirection'];
        }

        return implode(' ', $this->order);
    }

    private function setSearchParams(): \Medico\Service\Search\BloodPressure
    {
        // transform param names from camelCase to snake_case
        $this->searchParameters = array_map(function ($searchParameter) {
            $queryParamName = lcfirst(str_replace('_', '', ucwords($searchParameter['name'], '_')));
            $searchParameter['value'] = isset($this->queryParams[$queryParamName]) ? $this->queryParams[$queryParamName] : null;
            return $searchParameter;
        }, $this->searchParameters);

        // remove empty search parameters
        $this->searchParameters = array_filter($this->searchParameters, function ($searchParameter) {
            return !empty($searchParameter['value']);
        });

        return $this;
    }

    private function flattenParamsForValidation(): \Medico\Service\Search\BloodPressure
    {
        foreach ($this->searchParameters as $param) {
            $this->flattenParams[$param['name']] = $param['value'];
        }

        return $this;
    }

    private function validate()
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

        foreach ($this->flattenParams as $key => $value) {
            if (isset($possibleValidators[$key])) {
                $validation->add($key, $possibleValidators[$key]);
            }
        }

        $validationResult = $validation->validate($this->flattenParams);

        if ($validationResult->count() > 0) {
            $this->setErrorMessages($validationResult);
            return false;
        }

        return true;
    }

    private function setErrorMessages($validationResult)
    {
        foreach ($validationResult as $message) {
            $this->errorMessages[] = [
                'field' => $message->getField(),
                'error' => $message->getMessage()
            ];
        }
    }
}
