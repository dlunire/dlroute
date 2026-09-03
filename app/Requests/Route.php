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

use DLAuth\Data\SessionData;
use DLRoute\Core\Auth\AuthApps;
use DLRoute\Core\Data\Auth\RoutesAuth;
use DLRoute\Core\Routing\Automaton\Route\RouteType;
use DLRoute\Enums\Methods;
use DLRoute\Interfaces\RouteInterface;
use DLRoute\Interfaces\Routing\RouteLexerInterface;
use DLRoute\Requests\DLOutput;
use DLRoute\Server\DLServer;

abstract class Route extends DLParamValueType implements RouteInterface, RouteLexerInterface {
    use RouteParams;

    /**
     * Indica si las rutas a registrar deben marcarse como autenticadas.
     *
     * @var boolean
     */
    private static bool $mark_routes_authenticated = false;

    /**
     * Indica si la sesión actual es válida.
     *
     * @var boolean
     */
    protected static bool $is_session_valid = false;

    /**
     * Almacenamiento de rutas
     *
     * @var array
     */
    protected static array $routes = [];

    /**
     * Permite seleccionar múltiples métodos HTTP para registrar rutas
     * 
     * @var array<non-empty-string> $matches
     */
    protected static array $matches = [];

    /**
     * Variables globales para el controlador.
     *
     * @var array|object
     */
    protected static array|object $vars = [];

    /**
     * Almacena los tipos MIME asociados a las rutas registradas.
     *
     * Las claves corresponden a la identidad interna de cada ruta y los valores
     * representan el tipo MIME que debe utilizarse al generar la respuesta.
     *
     * @var array<string, string|null>
     */
    protected static array $mime_types = [];

    /**
     * Registra una ruta y sus metadatos asociados en el autómata de enrutamiento.
     *
     * Cuando el contexto de registro requiere autenticación, la identidad interna de la ruta se construye
     * anteponiendo el identificador definido por {@see RouteType::AUTH}. Este identificador permite distinguir
     * una ruta autenticada de una ruta pública con la misma URI.
     *
     * La identidad generada es exclusivamente interna y no modifica la URI expuesta al cliente.
     *
     * @param string $uri URI de la ruta a registrar.
     * @param callable|array|string $controller Controlador asociado a la ruta.
     * @param Methods $method Método HTTP asociado a la ruta.
     * @param array|object $vars Datos disponibles como parámetros del controlador.
     * @param non-empty-string|null $mime_type Tipo MIME de la respuesta,
     * opcionalmente especificado para la ruta.
     *
     * @return void
     */
    protected static function request(string $uri, callable|array|string $controller, Methods $method, array|object $vars, ?string $mime_type = null): void {
        /** @var RouteType $route_type */
        $route_type = RouteType::AUTH;

        $route = self::$mark_routes_authenticated
            ? "{$route_type->value}{$uri}"
            : $uri;

        self::register_routes($method->value, $route, $controller);
        self::$vars[$method->value][$route] = $vars;
        self::$mime_types[$route] = $mime_type;
    }

    /**
     * Devuelve el tipo `mime` personalizado.
     *
     * @param string $route
     * @return string|null
     */
    protected static function get_mime_type(string $route): string | null {
        return self::$mime_types[$route] ?? null;
    }

    /**
     * Consulta las rutas y ejecuta el controlador en función de la ruta encontrada
     *
     * @return void
     */
    public static function run(): void {
        /**
         * Variables
         * 
         * @var array|object
         */
        $vars = self::get_vars();

        /**
         * Salida del controlador.
         * 
         * @var mixed
         */
        $data = null;

        // TODO: Rutas autenticasas, establecer las claves correspondientes.
        /**
         * Ruta de la petición.
         * 
         * @var string
         */
        $route = DLServer::get_route();

        /**
         * Tipo personalizado.
         * 
         * @var string|null
         */
        $mime_type = self::get_mime_type($route);

        /**
         * Controlador asociado a la ruta y método de la petición.
         * 
         * @var callable|array|string|null
         */
        $controller = self::get_controller($route);


        if ($controller === null) {
            DLOutput::not_found();
        }

        if (\is_string($controller)) {
            $data = self::string_controller($controller, $vars);
        }

        if (is_callable($controller)) {
            $data = self::callable_controller($controller, $vars);
        }

        if (\is_array($controller)) {
            $data = self::array_controller($controller, $vars);
        }

        $output = DLOutput::get_instance();

        $output->set_content($data);
        $output->print_response_data($mime_type);

        exit;
    }

    /**
     * Establece el contexto de autenticación de la ruta.
     *
     * @param SessionData $session Estado de la sesión actual
     * @param boolean $requires_authentication Define si la ruta requiere autenticación
     *
     * @return void
     */
    public static function set_authentication_context(SessionData $session, bool $requires_authentication): void {
        static::$is_session_valid = $session->is_valid_session;
        static::$mark_routes_authenticated = $requires_authentication;
    }

    /**
     * Registra nuevas rutas
     *
     * @param string $route
     * @return void
     */
    protected static function register_routes(string $method, string $route, callable|array|string $controller): void {
        if (isset(self::$routes[$method][$route])) return;

        self::process_params($route);
        self::$routes[$method][$route] = $controller;
    }

    /**
     * Cuenta la cantidad de barras diagonales (slashes) en una URI.
     *
     * Este método realiza un recorrido lineal sobre la cadena de entrada 
     * utilizando un puntero de desplazamiento para identificar el carácter 
     * definido como separador de ruta (self::SLASH).
     *
     * @param string $input La URI depurada a analizar.
     * @param int    $quantity Variable pasada por referencia que almacena el 
     * conteo acumulado. Este valor es incrementado por cada
     * incidencia encontrada.
     * 
     * @return void
     */
    public static function count_slash(string $input, int &$quantity): void {
        /** @var int $offset Puntero de posición actual en la cadena */
        $offset = 0;

        /** @var int $length Longitud total de la cadena de entrada */
        $length = \strlen($input);

        while ($offset < $length) {
            $byte = $input[$offset];

            if ($byte === self::SLASH) {
                $quantity++;
            }

            $offset++;
        }
    }

    /**
     * Devuelve el controlador a ejecutar en función de la ruta seleccionada por el usuario.
     *
     * @param string $route
     * @return callable|array|string|null
     */
    protected static function get_controller(string $route): callable|array|string|null {
        /**
         * Método HTTP actual.
         * 
         * @var string
         */
        $method = DLServer::get_method();

        /**
         * Controlador que será devuelto.
         * 
         * @var callable|array|string|null
         */
        $controller = null;

        if (!\array_key_exists($method, self::$routes)) {
            return $controller;
        }

        if (!\array_key_exists($route, self::$routes[$method])) {
            return $controller;
        }

        $controller = self::$routes[$method][$route] ?? null;

        return $controller;
    }

    /**
     * Ejecuta la función que se pase como argumento y devuelve su salida.
     *
     * @param callable $callback Función a ejecutar como controlador.
     * @param array|object $data Datos que serán usados como un parámetro en el controlador.
     * @return mixed
     */
    protected static function callable_controller(callable $callback, array|object $data): mixed {
        /**
         * Parámetros de la petición.
         * 
         * @var object
         */
        $params = (object) (self::$params ?? []);

        /**
         * Salida del controlador.
         * 
         * @var mixed
         */
        $content = $callback($params, $data);

        if (\is_string($content)) {
            $content = trim($content);
        }

        return $content;
    }

    /**
     * Devuelve la salida del método a ejecutar del controlador al que se apunta.
     *
     * @param array $controller Controlador al que se apunta.
     * @param array|object $data Datos que serán usados como un parámetro en el controlador.
     * @return mixed
     */
    protected static function array_controller(array $controller, array|object $data): mixed {
        /**
         * Contenido del método del controlador.
         * 
         * @var mixed
         */
        $content = null;

        $controller_name = $controller[0] ?? null;
        $controller_method = $controller[1] ?? null;

        /**
         * Información de errores del sistema en formato JSON.
         * 
         * @var string
         */
        $error = "";

        if (!\is_string($controller_name)) {
            self::response_code(500);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => 'Controlador inválido'
            ]);

            if (self::is_production()) {
                self::set_error($error);
                $error = self::get_generic_error();
            }

            echo $error;
            exit;
        }

        if (!\is_string($controller_method)) {
            self::response_code(500);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => "Método del controlador inválido"
            ]);

            if (self::is_production()) {
                self::set_error($error);
                $error = self::get_generic_error();
            }

            echo $error;
            exit;
        }

        self::validate_classname($controller_name);

        if (!class_exists($controller_name)) {
            self::response_code(404);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => "El controlador «{$controller_name}» no está definido."
            ], true);

            if (self::is_production()) {
                self::set_error($error);
                $error = self::get_generic_error();
            }

            echo $error;
            exit;
        }

        self::validate_method($controller_method);

        if (!method_exists($controller_name, $controller_method)) {
            self::response_code(404);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => "El método «{$controller_method}» del controlador «{$controller_name}» no está definido"
            ], true);

            if (self::is_production()) {
                self::set_error($error);
                $error = self::get_generic_error();
            }

            echo $error;
            exit;
        }

        /**
         * Instancia del controlador.
         */
        $instance = new $controller_name;

        /**
         * Parámetros de la petición en una ruta amigable.
         * 
         * @var object
         */
        $params = (object) (self::$params ?? []);

        /**
         * Salida del controlador.
         * 
         * @var mixed
         */
        $content = $instance->{$controller_method}($params, $data);

        if (\is_string($content)) {
            $content = trim($content);
        }

        return $content;
    }

    /**
     * Devuelve la salida del método del controlador al que se apunta.
     *
     * @param string $controller Controlador al que se apunta.
     * @param array|object $data Datos que serán usados como un parámetro en el controlador.
     * @return mixed
     */
    protected static function string_controller(string $controller, array|object $data): mixed {
        $pattern = "/@/";

        preg_match_all($pattern, $controller, $matches);

        /**
         * Cantidad de arrobas (@) encontradas.
         * 
         * @var int
         */
        $quantity = \count($matches[0]);

        /**
         * Información de errores del sistema en formato JSON.
         * 
         * @var string
         */
        $error = "";

        if ($quantity !== 1) {
            self::response_code(500);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => 'Fomato de nombre de controlador inválido'
            ], true);

            if (self::is_production()) {
                self::set_error($error);
                $error = self::get_generic_error();
            }

            exit;
        }

        $parts_controller = explode('@', $controller);

        /**
         * Salida del controlador.
         * 
         * @var mixed
         */
        $content = null;

        if (\is_array($parts_controller)) {
            $content = self::array_controller($parts_controller, $data);
        }

        return $content;
    }

    /**
     * Establece el código de respuesta en y establece la cabecera a formato JSON.
     *
     * @param integer $code
     * @return void
     */
    private static function response_code(int $code): void {
        header("Content-Type: application/json; charset=utf-8", true, $code);
    }

    /**
     * Valida si el nombre de la clase es correcto.
     *
     * @param string $classname
     * @return void
     */
    private static function validate_classname(string $classname): void {
        /**
         * Patrón de nombre en formato PascalCase
         * 
         * @var string
         */
        $pascal_case_pattern = "/^[A-Z][a-zA-Z]+/";

        /**
         * Patrón de nombre de clase.
         * 
         * @var string
         */
        $classname_pattern = "/^[a-z_][a-z0-9_]+$/i";

        /**
         * Partes de un nombre de clase.
         * 
         * @var array
         */
        $parts = preg_split('/\\\+/', $classname);

        /**
         * Índice indicadora del nombre de clase.
         * 
         * @var int
         */
        $index = \count($parts) - 1;

        /**
         * Nombre del controlador.
         * 
         * @var string
         */
        $controller_name = $parts[$index] ?? '';

        /**
         * Mensaje de error.
         * 
         * @var string
         */
        $error = "";

        if (!(preg_match($classname_pattern, $controller_name))) {
            self::response_code(500);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => "Caracteres Inválidos"
            ], true);

            if (self::is_production()) {
                $_SESSION['error'] = $error;

                $error = DLOutput::to_json([
                    "error" => "Error del sistema"
                ]);
            }

            echo $error;
            exit;
        }

        if (!(preg_match($pascal_case_pattern, $controller_name))) {
            self::response_code(500);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => "El nombre de clase debe tener el formato PascalCase"
            ]);

            if (self::is_production()) {
                self::set_error($error);
                $error = self::get_generic_error();
            }

            echo $error;
            exit;
        }
    }

    /**
     * Valida si se ha escrito correctamente el nombre del método del controlador.
     *
     * @param string $method_name Nombre del método del controlador.
     * @return void
     */
    private static function validate_method(string $method_name): void {
        $found =  preg_match('/^[a-z_][a-z0-9_]+$/i', $method_name);

        /**
         * Mensaje de error del sistema.
         * 
         * @var string
         */
        $error = "";

        if (!$found) {
            self::response_code(500);

            $error = DLOutput::to_json([
                "status" => false,
                "error" => "El nombre del método «{$method_name}» es inválido"
            ]);

            if (self::is_production()) {
                self::set_error($error);
                $error = self::get_generic_error();
            }

            echo $error;
            exit;
        }
    }

    /**
     * Indica si el sistema está en modo producción o no.
     *
     * @return boolean
     */
    public static function is_production(): bool {
        if (\defined('DL_PRODUCTION')) {
            return constant('DL_PRODUCTION');
        }

        return false;
    }

    /**
     * Almacena información de error del sistema en una variable de sessión
     *
     * @param string $error
     * @return void
     */
    private static function set_error(string $error): void {
        $_SESSION['error'] = trim($error);
    }

    /**
     * Devuelve errores genéricos.
     *
     * @return string
     */
    private static function get_generic_error(): string {
        return DLOutput::to_json([
            "status" => false,
            "error" => "Error del sistema"
        ]);
    }

    /**
     * Devuelve las variables asociadas al método HTTP y su ruta.
     *
     * @return array
     */
    private static function get_vars(): array|object {
        /**
         * Ruta HTTP
         * 
         * @var string
         */
        $route = DLServer::get_route();

        /**
         * Método HTTP de la petición
         * 
         * @var string
         */
        $method = DLServer::get_method();

        /**
         * Variables
         * 
         * @var array
         */
        $vars = [];

        if (!\array_key_exists($method, self::$vars)) {
            return $vars;
        }

        if (!\array_key_exists($route, self::$vars[$method])) {
            return $vars;
        }

        $vars = self::$vars[$method][$route] ?? [];

        return $vars;
    }
}
