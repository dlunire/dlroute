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
 * Conjunto de cabeceras HTTPS
 * 
 * @package dlunire/dlroute
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2026 David E Luna M
 * @license AGPL-3.0 license
 */
final class HeadersInit {

    /**
     * Valores 
     *
     * @var array $headers
     */
    private array $headers = [];

    /**
     * Establece el valor de la cabecera
     *
     * @param string $name Nombre de la cabecera
     * @param string $value Valor de la cabecera
     * @return void
     */
    public function set(string $name, string $value): void {
        $this->headers[$name] = trim($value);
    }

    /**
     * Devuelve el valor de la cabecera
     *
     * @param string $name Nombre de la cabecera
     * @return string|null
     */
    public function get(string $name): ?string {
        return $this->headers[$name] ?? null;
    }

    /**
     * Establece de forma dinámica el valor de la cabeera
     *
     * @param string $name Nombre de la cabecera
     * @param string $value Valor de la cabecera
     * @return void
     */
    public function __set(string $name, string $value): void {
        $this->set($name, $value);
    }

    /**
     * Devuelve el valor de la cabecera de forma dinámica
     *
     * @param string $name Nombre de la cabecera
     * @return string|null
     */
    public function __get(string $name): ?string {
        return $this->get($name);
    }

    /**
     * Devuelve todas las cabeceras que se han definido
     *
     * @return array
     */
    public function get_headers(): array {

        /**
         * Cabeceras HTTP
         * 
         * @var array<int, string> $headers
         */
        $headers = [];

        foreach ($this->headers as $key => $value) {

            if (!is_string($key) || !is_string($value)) {
                continue;
            }

            $key = trim($key);
            $value = trim($value);

            /**
             * Cabecera actualmente capturada
             * 
             * @var string $header
             */
            $header = "{$key}: {$value}";

            array_push($headers, $header);
        }

        return $headers;
    }
}
