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

    /**
     * Determina si el método HTTP actual es considerado seguro (safe)
     * según la semántica definida por HTTP. Los métodos seguros no
     * están destinados a modificar el estado del recurso y pueden ser
     * utilizados para operaciones de consulta o descubrimiento.
     *
     * Los métodos considerados seguros son:
     * - GET
     * - HEAD
     * - OPTIONS
     *
     * @return bool
     */
    public static function is_safe(): bool {
        return match (self::get_method()) {
            Methods::GET,
            Methods::HEAD,
            Methods::OPTIONS => true,
            default => false
        };
    }

    /**
     * Determina si el método HTTP actual es idempotente. Un método
     * idempotente produce el mismo estado final en el recurso aunque
     * la misma petición se ejecute varias veces consecutivas.
     *
     * Esta propiedad resulta útil para mecanismos de reintento,
     * proxies intermedios y sistemas distribuidos.
     *
     * Los métodos considerados idempotentes son:
     * - GET
     * - HEAD
     * - PUT
     * - DELETE
     * - OPTIONS
     *
     * El método PATCH no se considera idempotente de forma general,
     * ya que su comportamiento depende de la operación aplicada sobre
     * el recurso.
     *
     * @return bool
     */
    public static function is_idempotent(): bool {
        return match (self::get_method()) {
            Methods::GET,
            Methods::HEAD,
            Methods::PUT,
            Methods::DELETE,
            Methods::OPTIONS => true,
            default => false
        };
    }

    /**
     * Determina si el método HTTP actual puede ser almacenado en caché
     * de acuerdo con las reglas generales del protocolo HTTP. Esta
     * propiedad permite a clientes y proxies reutilizar respuestas
     * previamente obtenidas, reduciendo el tráfico y la carga sobre
     * el servidor.
     *
     * En términos generales, los métodos GET y HEAD son cacheables.
     * Otros métodos pueden serlo bajo condiciones específicas definidas
     * explícitamente por las cabeceras de respuesta.
     *
     * @return bool
     */
    public static function is_cacheable(): bool {
        return match (self::get_method()) {
            Methods::GET,
            Methods::HEAD => true,
            default => false
        };
    }

    /**
     * Devuelve el nombre del método HTTP actual como cadena de texto.
     *
     * Equivale al valor del caso correspondiente del enum `Methods`,
     * por ejemplo:
     *
     * - "GET"
     * - "POST"
     * - "PUT"
     * - "PATCH"
     *
     * Resulta útil cuando se requiere interoperar con componentes o
     * bibliotecas que esperan una representación textual del método
     * HTTP en lugar del enum `Methods`.
     *
     * @return string
     */
    public static function get_method_name(): string {
        return self::get_method()->value;
    }
}
