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

use DLRoute\Core\Routing\Automaton\Route\RouteIdentity;
use DLRoute\Routes\RouteDebugger;

trait RouteParams {
    /**
     * Define la identidad de la ruta.
     *
     * Determina la identidad bajo la cual se registra y procesa la ruta. Actualmente, `DLRoute` utiliza
     * `RouteIdentity::AUTH` como identidad implementada para este contexto.
     *
     * La enumeración contempla otras identidades, como `PUBLIC`, que se mantienen como parte de la
     * estructura prevista para futuras extensiones del sistema de enrutamiento.
     *
     * @var RouteIdentity
     */
    protected static RouteIdentity $route_identity = RouteIdentity::AUTH;

    /**
     * Indica si las rutas a registrar deben marcarse como autenticadas.
     *
     * @var boolean
     */
    protected static bool $mark_routes_authenticated = false;

    /**
     * Indica si la sesión actual es válida.
     *
     * @var boolean
     */
    protected static bool $is_valid_session = false;
    /**
     * Parámetros de la petición.
     *
     * @var object|null
     */
    protected static ?object $params = null;

    /**
     * Captura la ruta con parámetro actual
     *
     * @var array
     */
    protected static array $current_param = [];

    /**
     * Ruta actual capturada de la petición. Es una ruta con identidad contextual
     *
     * @var non-empty-string $http_route
     */
    protected static string $context_current_route = "/";

    /**
     * Ruta actual capturada. No tiene contexto de autenticación.
     *
     * @var string
     */
    protected static string $public_current_route = "/";

    /**
     * Procesa las rutas y extrae de ellas sus parámetros.
     *
     * @param string $route Ruta a ser procesada.
     * @return void
     */
    protected static function process_params(string &$route): void {
        // TODO: el problema a resolver es $current_route

        // self::$current_param[static::$context_current_route] = $route;

        // print_r(self::$current_param);
        // return;

        /**
         * Ruta actual de la petición.
         * 
         * @var non-empty-string $http_route
         */
        $current_route = static::$context_current_route;

        /**
         * Ruta sin slash en los extremos.
         * 
         * @var string
         */
        $route_without_slash = RouteDebugger::trim_slash($route);

        /**
         * Partes de una ruta.
         * 
         * @var array<string>
         */
        $route_parts = explode("/", $route_without_slash);

        /**
         * Indicador de existencia de parámetros. Si los parámetros
         * existen, entonces, la ruta será dinámica en las partes
         * donde hayan llaves `{variable}`.
         * 
         * @var boolean
         */
        $param_exists = self::assign_param_value($route_parts);

        if ($param_exists) {
            self::$current_param[$current_route] = $route;
            $route = $current_route;
        }
    }

    /**
     * Asigna el valor al parámetro.
     *
     * @param array $route_parts
     * @return bool
     */
    protected static function assign_param_value(array $route_parts): bool {
        // TODO: reescribir este método utilizando un autómata.
        /**
         * Ruta actual de la peticón HTTP.
         * 
         * @var string
         */
        $current_route = static::$context_current_route;

        /**
         * Partes de una ruta actual
         * 
         * @var array<string>
         */
        $current_route_parts = explode("/", $current_route);

        /**
         * Cantidad de partes de la ruta actual de la petición.
         * 
         * @var int
         */
        $current_route_count = \count($current_route_parts);

        // print_r("current_count: {$current_route_count} → " . implode("/", $route_parts) . "\n");

        /**
         * Cantidad de partes de una ruta ruta seleccinada.
         * 
         * @var int
         */
        $route_count = \count($route_parts);

        // print_r("count: {$route_count} → " . implode("/", $route_parts) . "\n");


        if ($current_route_count !== $route_count) {
            return false;
        }

        /**
         * Patrón de búsqueda de parámetros.
         * 
         * @var string
         */
        $pattern = "/\{.*?\}/";

        /**
         * Ruta actual
         * 
         * @var string
         */
        $route = "/" . implode("/", $route_parts);

        // print_r($route_parts);

        /**
         * Indicador de búsqueda exitosa o no.
         * 
         * @var boolean
         */
        $found = preg_match($pattern, $route, $matches);

        if (!$found) {
            return false;
        }
        // return false;
        foreach ($route_parts as $key => $part) {
            $value_part = $current_route_parts[$key];
            $value_part = trim($value_part);

            if (!preg_match($pattern, $part, $matches) && $value_part !== $part) {
                return false;
            }
        }

        /**
         * Parámetros capturados.
         * 
         * @var array
         */
        $params = [];

        foreach ($route_parts as $key => $part) {
            $part = trim($part);
            $found = preg_match($pattern, $part);

            if (!$found) {
                continue;
            }

            /**
             * Valor del parámetro.
             * 
             * @var string|float|int|boolean
             */
            $value = $current_route_parts[$key] ?? '';

            self::remove_keys($part);
            self::process_value($value);

            $params[$part] = $value;
        }

        self::$params = (object) $params;

        return true;
    }

    /**
     * Remueve las llaves de los parámetros.
     *
     * @param string $input Texto con llaves a ser procesada.
     * @return void
     */
    private static function remove_keys(string &$input): void {
        $input = str_replace("{", '', $input);
        $input = str_replace("}", '', $input);
        $input = trim($input);
    }

    /**
     * Procesa una entrada y determina su tipo.
     *
     * @param mixed $value Valor a ser procesado.
     * @return void
     */
    private static function process_value(mixed &$value): void {
        $value = trim($value);

        if (strtolower($value) === "true") {
            $value = true;
        }

        if (strtolower($value) === "false") {
            $value = false;
        }

        if (is_numeric($value)) {

            $is_float = preg_match("/\./", $value);

            if ($is_float) {
                $value = (float) $value;
            }

            if (!\is_float($value)) {
                $value = (int) $value;
            }

            return;
        }
    }
}
