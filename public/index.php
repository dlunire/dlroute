<?php

use DLRoute\Core\Auth\AuthApps;
use DLRoute\Core\Data\RouteHandler;
use DLRoute\Core\Telemetry\TelemetryRequest;
use DLRoute\Enums\Methods;

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
use DLRoute\Test\AuthController;

include dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

DLRoute::post(
    uri: "/login",
    controller: [AuthController::class, 'auth'],
    data: [],
    mime_type: "application/json"
);

DLRoute::get('/check', [AuthController::class, 'check']);


$auth = new AuthApps();

$auth->authenticated(function () {
    DLRoute::get('/testing', fn() => ["status" => "Autenticado"]);
});

DLRoute::get('/profile/{test?}', function (object $params) {
    return [
        "scope" => "PUBLIC",
        "param" => $params
    ];
});


DLRoute::delete('/logout', [AuthController::class, 'logout']);
$auth->require_auth(function (): void {
    DLRoute::delete('AUTH-', fn() => ["Status" => "Ok"]);
    DLRoute::get('/profile/{test?}', [AuthController::class, 'check']);
});

DLRoute::get('/telemetry', function () {
    return TelemetryRequest::telemetry("Algo de Telemetría para ChatGPT");
});

// print_r(DLRoute::get_routes());

$auth->require_auth(function () {

    DLRoute::match(
        methods: [Methods::GET, Methods::POST],
        route: new RouteHandler(
            uri: "/products/{uuid?}",
            controller: fn() => "Esta es una prueba",
            data: [], // Esto es opcional.
            mime_type: "text/plain", // Esto también es opcional,
            handler_filters: [
                "uuid" => "uuid"
            ]
        )
    );
});

$test = AuthController::class;

DLRoute::get(
    uri: "/string",
    controller: "{$test}@method_name"
);

DLRoute::execute();
