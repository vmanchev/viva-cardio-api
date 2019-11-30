<?php

namespace Medico\Model;

use \Phalcon\Mvc\Model;
use \Phalcon\Validation;
use \Phalcon\Validation\Validator\Callback;
use \Phalcon\Validation\Validator\PresenceOf;
use \Medico\Model\Patient as PatientModel;


class Share extends Model
{
    public $id;
    public $patient_id;

    public function initialize()
    {

        $this->hasMany(
            'id',
            'Medico\Model\BloodPressure',
            'patient_id'
        );

        $this->belongsTo(
            'patient_id',
            'Medico\Model\Patient',
            'id'
        );
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add('id', new PresenceOf([
            'message' => 'ERROR.ID.REQUIRED'
        ]));

        $validator->add('patient_id', new Callback([
            'callback' => function ($data) {
                return !!PatientModel::findFirst($data->patient_id)->id;
            },
            'message' => 'ERROR.PATIENT_ID.INVALID'
        ]));

        return $this->validate($validator);
    }
}
