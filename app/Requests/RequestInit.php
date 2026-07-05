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

namespace DLRoute\Requests;

/**
 * Opciones de la petición
 * 
 * @package dlunire/dlroute
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2026 David E Luna M
 * @license AGPL-3.0 
 */
final class RequestInit {

    /**
     * Nombre del método HTTP
     *
     * @var string
     */
    public string $method;

    /**
     * Cabeceras HTTP
     *
     * @var HeadersInit
     */
    public HeadersInit $headers;

    /**
     * Cuerpo o datos de la petición
     *
     * @var array
     */
    public array $body;

    /**
     * Establece el nombre del método de la petición
     *
     * @param string $method Nombre del métodode la petición
     * @return void
     */
    public function set_method(string $method): void {
        $this->method = trim(
            strtoupper($method)
        );
    }

    /**
     * Establece las cabeceras HTTPS
     *
     * @param HeadersInit $headers Cabeceras HTTP
     * @return void
     */
    public function set_headers(HeadersInit $headers): void {
        $this->headers = $headers;
    }

    /**
     * Establece el cuerpo o datos de la petición
     *
     * @param array $body Cuerpo o datos de la petición
     * @return void
     */
    public function set_body(array $body): void {
        $this->body = $body;
    }
}
