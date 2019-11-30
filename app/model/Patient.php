<?php

namespace Medico\Model;

use \Phalcon\Mvc\Model;
use \Phalcon\Validation;
use \Phalcon\Validation\Validator\Callback;
use \Phalcon\Validation\Validator\PresenceOf;
use \Medico\Model\User as UserModel;


class Patient extends Model
{
    public $id;
    public $user_id;
    public $name;

    public function initialize() {

        $this->hasMany(
            'id',
            'Medico\Model\BloodPressure',
            'patient_id'
        );

        $this->hasMany(
            'id',
            'Medico\Model\Share',
            'patient_id'
        );

        $this->belongsTo(
            'user_id',
            'Medico\Model\User',
            'id'
        );

    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add('name', new PresenceOf([
            'message' => 'ERROR.REQUIRED'
        ]));

        $validator->add('user_id', new PresenceOf([
            'message' => 'ERROR.REQUIRED'
        ]));

        $validator->add('user_id', new Callback([
            'callback' => function($data) {
                return !!UserModel::findFirst($data->user_id)->id;
            },
            'message' => 'ERROR.USER_ID.INVALID'
        ]));

        return $this->validate($validator);
    }
}
