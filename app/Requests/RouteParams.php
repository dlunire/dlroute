<?php

namespace DLRoute\Requests;

use DLRoute\Routes\RouteDebugger;
use DLRoute\Server\DLServer;

trait RouteParams {

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
     * Procesa las rutas y extrae de ellas sus parámetros.
     *
     * @param string $route Ruta a ser procesada.
     * @return void
     */
    protected static function process_params(string &$route) {
        /**
         * Ruta actual de la petición.
         * 
         * @var string
         */
        $current_route = DLServer::get_route();

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
        /**
         * Ruta actual de la peticón HTTP.
         * 
         * @var string
         */
        $current_route = DLServer::get_route();
        $current_route = RouteDebugger::trim_slash($current_route);

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
        $current_route_count = count($current_route_parts);

        /**
         * Cantidad de partes de una ruta ruta seleccinada.
         * 
         * @var int
         */
        $route_count = count($route_parts);

        
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

        /**
         * Indicador de búsqueda exitosa o no.
         * 
         * @var boolean
         */
        $found = preg_match($pattern, $route, $matches);

        if (!$found) {
            return false;
        }

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

            if (!is_float($value)) {
                $value = (int) $value;
            }

            return;
        }
    }
}
