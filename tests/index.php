<?php

ini_set('display_errors', 1);

use DLRoute\Requests\DLRoute;
use DLRoute\Server\DLHost;
use DLRoute\Server\DLServer;
use DLRoute\Server\PortCandidate;
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

DLRoute::get('/ciencia/{parametro1}/ciencia/{parametro2}', function (object $params) {
    return DLRoute::get_routes();
});

DLRoute::post('/file', [TestController::class, 'file']);

DLRoute::get('/ciencia', function() {
    DLServer::set_external_host('ciencia.com');

    return [
        "dir" => DLServer::get_dir(),
        "dlunire" => "Mónica [Proyecto de Software de David E Luna M]",
        "domain" => DLHost::get_domain(),
        "hostname" => DLHost::get_hostname(),
        "is_https" => DLHost::is_https(),
        "IP" => DLServer::get_ipaddress(),
        "port" => DLServer::get_port(),
        "local_port" => DLServer::get_local_port(),
        "url_base" => DLServer::get_base_url(),
        "method" => DLServer::get_method(),
        "route" => DLServer::get_route(),
        "uri" => DLServer::get_uri(),
        "proxy" => DLServer::is_likely_proxy(),
    ];
});

DLRoute::execute();
