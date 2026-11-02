<?php

use DLRoute\Core\Auth\AuthApps;
use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;

/**
 * DLUnire
 * Copyright (C) 2026 David E Luna M
 *
 * Operando bajo el establecimiento de comercio "DLUnire",
 * NIT 700551569-1, matrícula mercantil Nº 10007069
 * (matrícula mercantil personal Nº 10007068).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

session_start();

ini_set('display_errors', 1);

use DLRoute\Requests\DLRoute;
use DLRoute\Server\DLServer;
use DLRoute\Test\AuthController;

error_log('ENTRY: ' . __FILE__);
error_log('REALPATH: ' . (realpath(__FILE__) ?: 'false'));
error_log('SCRIPT_FILENAME: ' . ($_SERVER['SCRIPT_FILENAME'] ?? ''));
error_log('REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? ''));

include dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . "vendor"
    . DIRECTORY_SEPARATOR
    . "autoload.php";


/** @var AuthApps $auth */
$auth = new AuthApps();

// DLRoute::post('/algo', function() {
//     return ["status" => "Ok"];
// });

// DLRoute::get('/profile/{a?}/{b?}/{c?}', fn () => ["status" => "Ok"]);
// DLRoute::get('/profile/{a?}/{b?}/{c?}/{d?}', fn () => ["status" => "Ok"]);

DLRoute::get('/////////f////dd', fn() => "Test");

// DLRoute::get('/profile/{a?}/{b?}/{c?}', fn () => ["status" => "Ok"]);

// # Definición de rutas de pruebas para el controlador
// DLRoute::get('/profile/{algo?}', [AuthController::class, 'profile'])->filter_by_type([
//     "algo" => "integer"
// ]);

// $auth->require_auth(function () {
//     DLRoute::get('/profile/{auth}', [AuthController::class, 'profile_with_auth'])->filter_by_type([
//         "auth" => "integer"
//     ]);
// });

// # Inicio de sesión de prueba:
DLRoute::post('/login', [AuthController::class, 'auth']);

// # Cerrar una sesión de prueba:
// DLRoute::delete("/logout", [AuthController::class, 'logout']);

$value = [
    "base_url" => DLServer::get_base_url(),
    "dir" => DLServer::get_dir()
];

DLServer::get_dir();

$test = RequestURILexer::from();

// print_r("dir: " . $test->get_dir() . "\n");
// print_r("route: " . $test->get_route() . "\n");
// print_r("uri: " . $test->get_uri() . "\n");
// print_r("processed_uri: " . $test->get_processed_uri() . "\n");
// print_r("processed_uri: " . $test->get_query_string() . "\n");

print_r($test->get_route_resolution());

DLRoute::execute();
