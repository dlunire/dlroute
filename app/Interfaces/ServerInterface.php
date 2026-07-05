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

declare(strict_types=1);

namespace DLRoute\Interfaces;

/**
 * Procesa los datos de `$_SERVER`
 * 
 * @package DLRoute\Interfaces;
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2023 David E Luna M
 * @license AGPL-3.0 license
 */
interface ServerInterface {

    /**
     * Devuelve la URI de la aplicación. Es decir, la URI más la ruta registrada
     * en el caso de que se esté navegando por ella.
     *
     * @return string
     */
    public static function get_uri(): string;

    /**
     * Devuelve el nombre de host, bien sea, el que se ha determinado o el que se 
     * impuso por el desarrollador en el entorno de ejecución.
     *
     * @return string
     */
    public static function get_hostname(): string;

    /**
     * Devuelve el método HTTP.
     *
     * @return string
     */
    public static function get_method(): string;

    /**
     * Devuelve la dirección IP del cliente, siempre que sea posible
     *
     * @return string
     */
    public static function get_ipaddress(): string;

    /**
     * Devuelve el nombre del script que se está ejecutando.
     *
     * @return string
     */
    public static function get_script_filename(): string;

    /**
     * Devuelve el agente de usuario del cliente de la petición.
     *
     * @return string
     */
    public static function get_user_agent(): string;

    /**
     * Devuelve el documento raíz de ejecución de la aplicación
     *
     * @return string
     */
    public static function get_document_root(): string;

    /**
     * Devuelve el hombre de host con puerto incluido en formato HTTP, es decir,
     * de una forma similar a esta: `http://localhost:3000/` o la que impone el 
     * proxy inverso o similar.
     *
     * @return string
     */
    public static function get_http_host(): string;

    /**
     * Determina si el método de envío HTTP es GET.
     *
     * @return boolean
     */
    public static function is_get(): bool;
    /**
     * Indica si el método HTTP es POST
     *
     * @return boolean
     */
    public static function is_post(): bool;

    /**
     * Determina si el método de envío HTTP es PUT.
     *
     * @return boolean
     */
    public static function is_put(): bool;

    /**
     * Determina si el método de envío HTTP es PATCH.
     *
     * @return boolean
     */
    public static function is_patch(): bool;

    /**
     * Determina si el método de envío HTTP es DELETE.
     *
     * @return boolean
     */
    public static function is_delete(): bool;

    /**
     * Devuelve la ruta lógica registrada por el desarrollador. No la que devuelve el
     * sistema por medio de `$_SERVER` de forma directa.
     *
     * @return string
     */
    public static function get_route(): string;

    /**
     * Devuelve el script actual de ejecución.
     *
     * @return string
     */
    public static function get_script_name(): string;
}