<?php

namespace Medico\Service\Search;

use Medico\Model\Patient as PatientModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Between as BetweenValidator;
use Phalcon\Validation\Validator\Date as DateValidator;
use Phalcon\Validation\Validator\PresenceOf;

class Patients
{
  private $queryParams = [];

    public function __construct(array $params)
    {
        $this->queryParams = $params;
    }

    public function getResultSet()
    {
        return [
            'patients' => PatientModel::find([
                'conditions' => 'user_id = :user_id:',
                'bind' => [
                    'user_id' => $this->queryParams['user_id'],
                ],
                'order' => 'name asc',
            ]),
        ];
    }

}
