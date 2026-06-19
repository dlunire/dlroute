<?php

/**
 * MIT License
 *
 * Copyright (c) 2026 David E Luna M — DLUnire
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author    David E Luna M <dlunireframework@gmail.com>
 * @copyright Copyright (c) 2026 David E Luna M
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/dlunire/dlroute
 */

declare(strict_types=1);

namespace DLRoute\Http;

use DLRoute\Enums\Methods;

/**
 * Gestiona y expone el método HTTP de la petición actual.
 *
 * Detecta automáticamente el método HTTP enviado por el cliente mediante
 * `$_SERVER['REQUEST_METHOD']`. Si el método no puede determinarse (ejecución
 * desde CLI, método inválido o ausente), utiliza `Methods::GET` como valor
 * por defecto, lo que permite su uso en pruebas automatizadas sin servidor HTTP.
 */
final class Request {

    /**
     * Método HTTP detectado en la petición actual. No se inicializa hasta
     * que se invoca `get_method()` o cualquier método `is_*()` por primera vez.
     * A partir de ese momento queda en caché para el resto de la petición.
     *
     * @var Methods
     */
    private static Methods $method_name;

    /**
     * Determina el método HTTP de la petición actual leyendo
     * `$_SERVER['REQUEST_METHOD']` y lo convierte al caso correspondiente
     * del enum `Methods`. El resultado se almacena en caché en `$method_name`
     * tras la primera llamada — las llamadas subsiguientes devuelven el valor
     * almacenado sin volver a leer `$_SERVER`. En entornos CLI asigna
     * `Methods::GET` directamente. Si el método está ausente o no coincide
     * con ningún caso del enum, asigna `Methods::GET` como valor por defecto.
     *
     * @return Methods
     */
    private static function determine_method(): Methods {

        if (!isset(self::$method_name)) {
            self::$method_name = (self::is_cli())
                ? Methods::GET
                : Methods::tryFrom(
                    \strtoupper($_SERVER['REQUEST_METHOD'] ?? '')
                ) ?? Methods::GET;
        }

        return self::$method_name;
    }

    /**
     * Devuelve el método HTTP de la petición actual como caso del enum `Methods`.
     * Delega en `determine_method()`, que gestiona tanto la detección inicial
     * como el caché para llamadas subsiguientes.
     *
     * @return Methods
     */
    public static function get_method(): Methods {
        return self::determine_method();
    }

    /**
     * Devuelve `true` si el método HTTP de la petición actual es `GET`.
     *
     * @return bool
     */
    public static function is_get(): bool {
        return self::get_method() === Methods::GET;
    }

    /**
     * Devuelve `true` si el método HTTP de la petición actual es `HEAD`.
     *
     * @return bool
     */
    public static function is_head(): bool {
        return self::get_method() === Methods::HEAD;
    }

    /**
     * Devuelve `true` si el método HTTP de la petición actual es `POST`.
     *
     * @return bool
     */
    public static function is_post(): bool {
        return self::get_method() === Methods::POST;
    }

    /**
     * Devuelve `true` si el método HTTP de la petición actual es `PUT`.
     *
     * @return bool
     */
    public static function is_put(): bool {
        return self::get_method() === Methods::PUT;
    }

    /**
     * Devuelve `true` si el método HTTP de la petición actual es `PATCH`.
     *
     * @return bool
     */
    public static function is_patch(): bool {
        return self::get_method() === Methods::PATCH;
    }

    /**
     * Devuelve `true` si el método HTTP de la petición actual es `DELETE`.
     *
     * @return bool
     */
    public static function is_delete(): bool {
        return self::get_method() === Methods::DELETE;
    }

    /**
     * Devuelve `true` si el método HTTP de la petición actual es `OPTIONS`.
     *
     * @return bool
     */
    public static function is_options(): bool {
        return self::get_method() === Methods::OPTIONS;
    }

    /**
     * Determina si la ejecución actual proviene de una interfaz de línea
     * de comandos (CLI). Útil para pruebas automatizadas y scripts que
     * no tienen contexto HTTP.
     *
     * @return bool
     */
    public static function is_cli(): bool {
        return PHP_SAPI === 'cli';
    }

    /**
     * Devuelve `true` si la petición fue enviada mediante Ajax, verificando
     * la presencia del encabezado `X-Requested-With: XMLHttpRequest` que
     * los clientes Ajax envían por convención.
     *
     * @return bool
     */
    public static function is_ajax(): bool {
        return (
            ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')
            === 'XMLHttpRequest'
        );
    }
}
