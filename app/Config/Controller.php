<?php

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

namespace DLRoute\Config;

use DLRoute\Requests\DLOutput;
use DLRoute\Requests\DLRequest;
use DLRoute\Requests\DLUpload;
use DLRoute\Server\DLServer;
use DLRoute\Traits\Request;
use DLRoute\Validates\DLValidates;

/**
 * Controlador base
 * 
 * @package DLRoute\Config
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license AGPL-3.0 license
 */
abstract class Controller {

    use DLValidates, DLUpload, Request;

    /**
     * Procesa las peticiones del usuario.
     *
     * @var DLRequest
     */
    protected DLRequest $request;

    public function __construct() {
        $this->request = DLRequest::get_instance();
    }

    /**
     * Devuelve una dirección IP
     *
     * @return string
     */
    protected function get_ip(): string {
        return DLServer::get_ipaddress();
    }

    /**
     * Devuelve el hombre de host con puerto incluido en formato HTTP, es decir,
     * de una forma similar a esta: http://localhost:3000/
     *
     * @return string
     */
    protected function get_http_host(): string {
        return DLServer::get_http_host();
    }

    /**
     * Convierte un objeto o un array en una cadena de texto en formato JSON y la devuelve.
     *
     * Esta función toma un objeto o array y lo convierte en una cadena de texto en formato JSON.
     *
     * @param object|array $content El contenido que se va a parsear.
     * @param bool $pretty Indica si la salida en formato JSON debe tener formato legible o no.
     * @return string La cadena de texto en formato JSON resultante.
     */
    protected function get_json(array|object $data, bool $pretty = false): string {
        return DLOutput::get_json($data, $pretty);
    }
}
