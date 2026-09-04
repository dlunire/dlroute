<?php

use DLRoute\Core\Auth\AuthApps;
use DLRoute\Core\Telemetry\TelemetryRequest;

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

DLRoute::get(
    uri: "/login",
    controller: [AuthController::class, 'auth'],
    data: [],
    mime_type: "application/json"
);

DLRoute::get('/check', [AuthController::class, 'check']);

DLRoute::get('/logout', [AuthController::class, 'logout']);

$auth = new AuthApps();

$auth->authenticated(function () {
    DLRoute::get('/testing', fn() => ["status" => "Autenticado"]);
});

$auth->require_auth(function () {
    DLRoute::get('auth', fn() => [
        "status" => true,
        "success" => "Si ves esto, estás autenticado"
    ]);
});

DLRoute::get('/profile/{test?}', function (object $params) {
    return [
        "scope" => "PUBLIC",
        "param" => $params
    ];
});

$auth->require_auth(function () use ($auth): void {
    DLRoute::get('/profile', function (object $params) use ($auth): array {
        return [
            "scope" => "AUTHENTICATED",
            "params" => $params,
            "session" => $auth->get_session_data()
        ];
    });
});

DLRoute::get('/telemetry', function() {
    return TelemetryRequest::telemetry("Algo de Telemetría para ChatGPT");
});

// print_r(DLRoute::get_routes());

DLRoute::execute();
