<?php

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
use DLRoute\Server\DLHost;
use DLRoute\Server\DLServer;

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

DLRoute::get('/ruta/registrada', function() {
    DLServer::set_external_host('ciencia.com');

    return [
        "dlunire" => "Powered by David E Luna M",
        "dir" => DLServer::get_dir(),
        "route" => DLServer::get_route(),
        "uri" => DLServer::get_uri(),
        "url_base" => DLServer::get_base_url(),
        "domain" => DLHost::get_domain(),
        "hostname" => DLHost::get_hostname(),
        "is_https" => DLHost::is_https(),
        "IP" => DLServer::get_ipaddress(),
        "port" => DLServer::get_port(),
        "local_port" => DLServer::get_local_port(),
        "method" => DLServer::get_method(),
        "proxy" => DLServer::is_likely_proxy(),
    ];
});

DLRoute::execute();
