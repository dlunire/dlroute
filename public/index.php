<?php

use DLRoute\Core\Data\RouteHandler;
use DLRoute\Core\Data\Telemetry;
use DLRoute\Core\Routing\Automaton\QueryParams\QueryParamComposer;
use DLRoute\Core\Telemetry\TelemetryRequest;
use DLRoute\Enums\Methods;

/**
 * Copyright (c) 2026 David E Luna M
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @license MIT
 */

ini_set('display_errors', 1);

use DLRoute\Requests\DLRoute;

include dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

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

DLRoute::match([Methods::GET, Methods::POST], new RouteHandler(
    uri: "/{test?}",

    controller: fn (object $params) => [
        "params" => $params,
        "telemetry" => TelemetryRequest::telemetry("Telemetría de la petición"),
    ],
    handler_filters: [
        "test" => "uuid",
    ]
));

# Lo puedes probar así, incluso, colocanso puntos suspensivos (...):
DLRoute::get(
    uri: "/",
    controller: (new QueryParamComposer())->get_query_params(...)
);

DLRoute::execute();
