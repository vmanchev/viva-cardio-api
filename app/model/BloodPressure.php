<?php

namespace Medico\Model;

use \Phalcon\Mvc\Model;
use \Phalcon\Validation;
use \Phalcon\Validation\Validator\Callback;
use \Phalcon\Validation\Validator\PresenceOf;

use \Medico\Model\Patient as PatientModel;

class BloodPressure extends Model {
    public $id;
    public $patient_id;
    public $sys;
    public $dia;
    public $pulse;
    public $created_at;

    public function initialize() {

        $this->belongsTo(
            'patient_id',
            'Medico\Model\Patient',
            'id'
        );

    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add('patient_id', new PresenceOf([
            'message' => 'ERROR.REQUIRED'
        ]));

        $validator->add('patient_id', new Callback([
            'callback' => function($data) {
                return !!PatientModel::findFirst($data->patient_id);
            },
            'message' => 'ERROR.PATIENT_ID.INVALID'
        ]));

        $validator->add('sys', new PresenceOf([
            'message' => 'ERROR.REQUIRED'
        ]));

        $validator->add('dia', new PresenceOf([
            'message' => 'ERROR.REQUIRED'
        ]));

        $validator->add('pulse', new PresenceOf([
            'message' => 'ERROR.REQUIRED'
        ]));

        $validator->add('sys', new Callback([
            'callback' => function($data) {
                return $data->sys > 0 && $data->sys > $data->dia;
            },
            'message' => 'ERROR.SYS.INVALID'
        ]));

        $validator->add('dia', new Callback([
            'callback' => function($data) {
                return $data->dia > 0 && $data->sys > $data->dia;
            },
            'message' => 'ERROR.DIA.INVALID'
        ]));

        $validator->add('pulse', new Callback([
            'callback' => function($data) {
                return $data->pulse > 0;
            },
            'message' => 'ERROR.PULSE.INVALID'
        ]));

        return $this->validate($validator);
    }
}