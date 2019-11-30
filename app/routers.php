<?php

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$userCollection = new MicroCollection();

$userCollection->setHandler('Medico\Controller\UserController', true);
$userCollection->post('/user', 'register');
$userCollection->post('/user/login', 'login');
$userCollection->post('/user/forgot', 'forgot');
$app->mount($userCollection);

$patientCollection = new MicroCollection();
$patientCollection->setHandler('Medico\Controller\PatientController', true);
$patientCollection->post('/patient', 'create');
$patientCollection->put('/patient/{id}', 'update');
$app->mount($patientCollection);

$shareCollection = new MicroCollection();
$shareCollection->setHandler('Medico\Controller\ShareController', true);
$shareCollection->post('/patient/{id}/share', 'create');
$shareCollection->delete('/patient/{id}/share/{code}', 'delete');
$app->mount($shareCollection);

$bpCollection = new MicroCollection();
$bpCollection->setHandler('Medico\Controller\BloodPressureController', true);
$bpCollection->post('/blood-pressure', 'create');
$bpCollection->get('/blood-pressure', 'search');
$bpCollection->get('/s/{code}', 'sharedSearch');
$bpCollection->delete('/blood-pressure/{id}', 'delete');
$app->mount($bpCollection);
