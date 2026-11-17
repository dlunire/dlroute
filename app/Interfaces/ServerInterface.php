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

use DLRoute\Errors\RouteException;

/**
 * Procesa los datos capturados en la variable superglobal `$_SERVER`
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
     * Obtiene y valida el método de la solicitud actual.
     *
     * Recupera el método HTTP desde `$_SERVER['REQUEST_METHOD']`. Cuando no existe un
     * método HTTP disponible en el entorno de ejecución, utiliza `CLI` como valor
     * predeterminado.
     *
     * Comprueba que el método no esté vacío y que corresponda a uno de los métodos
     * definidos en el enumerador {@see Methods}. La validación respeta estrictamente
     * la capitalización original, sin aplicar conversiones ni normalizaciones.
     *
     * DLRoute requiere que los métodos se expresen en mayúsculas, tanto para los métodos
     * estandarizados como para los métodos personalizados admitidos por el enrutador.
     *
     * @return non-empty-string Método de solicitud validado.
     *
     * @throws RouteException Si no hay un método definido (400) o si el método no está
     *                        permitido (405).
     */
    public static function get_method(): string;

    /**
     * Devuelve la dirección IP del cliente, siempre que sea posible
     *
     * @return string
     */
    public static function get_ipaddress(): string;

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
    public static function get_base_url(): string;

    /**
     * Determina si el método del protocolo HTTP es `QUERY`. Este 
     * método es relativamente nuevo.
     *
     * @return boolean
     */
    public static function is_query(): bool;

    /**
     * Determina si el método de envío HTTP es GET.
     *
     * @return boolean
     */
    public static function is_get(): bool;

    /**
     * Valida si el método del protoolo HTTP es `HEAD`.
     *
     * @return boolean
     */
    public static function is_head(): bool;

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
     * Valida si el método del protocolo HTTP es `OPTIONS`.
     *
     * @return boolean
     */
    public static function is_options(): bool;

    /**
     * Determina si el método de envío HTTP es DELETE.
     *
     * @return boolean
     */
    public static function is_delete(): bool;

    /**
     * Devuelve la ruta lógica de la petición actual, enviada por el cliente HTTP o por la terminal
     * mediante el parámetro `-r` o `--route`.
     *
     * Delega en {@see RequestURILexer::get_route()}. El analizador léxico separa la URI en dos partes:
     * el directorio base (los segmentos iniciales que existen como directorios físicos desde el punto
     * de entrada) y la ruta lógica, que es la que devuelve este método.
     *
     * La ruta devuelta:
     *
     * - Comienza siempre con `/`.
     * - No incluye el directorio base ni la cadena de consulta.
     * - No está decodificada: `%20` se conserva tal como llegó. Para la forma decodificada, use
     *   {@see RequestURILexer::get_decoded_uri()}.
     *
     * Usa la instancia única de {@see RequestURILexer::from()}, por lo que la URI se analiza una sola vez
     * durante la ejecución.
     *
     * @return non-empty-string Ruta lógica de la petición actual.
     *
     * @throws RouteException Con código 403, si la URI contiene un byte no permitido o un segmento `.` o
     *                        `..`. Se lanza en la primera llamada, cuando se construye el analizador.
     */
    public static function get_route(): string;

    /**
     * Devuelve el directorio de ejecución o punto de entrada de la aplicación.
     *
     * @return string
     */
    public static function get_dir(): string;

    /**
     * Valor que identifica la ejecución desde la interfaz de línea de comandos (CLI).
     *
     * Coincide con el valor que toma `PHP_SAPI` cuando PHP se ejecuta desde la terminal. Se usa como
     * valor por defecto en los contextos donde no existe un dato de petición HTTP.
     *
     * @var non-empty-string
     */
    public const CLI = 'cli';
}
