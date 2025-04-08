<?php

ini_set('display_errors', 1);

use DLRoute\Requests\DLRoute;
use DLRoute\Test\TestController;

include dirname(__DIR__) . "/vendor/autoload.php";

/**
 * Este archivo se incorpora como ejemplo de uso del sistema de rutas. Sea el controlador
 * o las funciones que se pasen como argumento deben devolver datos.
 * 
 * Los datos devueltos por la función serán analizados de forma automática para determinar
 * su tipo y devolver al cliente una respuesta con su tipo MIME correspondiente a la 
 * salida.
 * 
 * Lo que sigue más abajo son rutas de ejemplos recién creadas.
 */

DLRoute::post('/regex/{parametro}', [TestController::class, 'index'])->filter_by_type([
    "parametro" => '/^[0-9]+$/'
]);

DLRoute::post('/test/{parametro}', function (object $params) {
    return $params;
})->filter_by_type([
    "parametro" => "numeric"
]);

DLRoute::get('/test/{file}', [TestController::class, 'index']);

DLRoute::get('/server', [TestController::class, 'server']);

DLRoute::post('/ciencia/{parametro1}/ciencia/{parametro2}', function (object $params) {
    return DLRoute::get_routes();
});

DLRoute::post('/file', [TestController::class, 'file']);

DLRoute::execute();
