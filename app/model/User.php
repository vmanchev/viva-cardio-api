<?php

namespace Medico\Model;

use \Phalcon\Mvc\Model;
use \Phalcon\Validation;
use \Phalcon\Validation\Validator\Email;
use \Phalcon\Validation\Validator\StringLength;
use \Phalcon\Validation\Validator\Uniqueness;


class User extends Model
{
    public $id;
    public $email;
    public $password;

    public function initialize() {

        $this->hasMany(
            'id',
            'Medico\Model\Patient',
            'user_id',
            [
                'alias' => 'patients'
            ]
        );

    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add('email', new Email([
            'message' => 'ERROR.EMAIL.INVALID'
        ]));

        $validator->add('email', new Uniqueness([
            'message' => 'ERROR.EMAIL.DUPLICATE'
        ]));

        $validator->add('password', new StringLength([
            'min' => 6,
            'message' => 'ERROR.PASSWORD.LENGTH'
        ]));

        return $this->validate($validator);
    }
}
