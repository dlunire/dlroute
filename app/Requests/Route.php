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
use DLRoute\Core\Data\RouteData\RouteContext;
use DLRoute\Core\Data\RouteData\RouteMimeType;
use DLRoute\Core\Routing\Automaton\Route\RouteIdentity;
use DLRoute\Enums\Methods;
use DLRoute\Errors\UnauthorizedException;
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
     * Almacenamiento de rutas
     *
     * @var array $routes
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
        /** @var RouteIdentity $route_identity */
        $route_identity = self::$route_identity;

        $route = self::$mark_routes_authenticated
            ? "{$route_identity->value}{$uri}"
            : $uri;

        self::register_routes($method->value, $route, $controller);
        self::$vars[$method->value][$route] = $vars;
        self::$mime_types[$route] = $mime_type;
    }

    /**
     * Devuelve los tipos MIME asociados a una ruta.
     *
     * Obtiene de forma independiente el tipo MIME registrado para los contextos
     * privado y público de la ruta, utilizando la identidad de ruta actualmente
     * configurada para resolver el registro correspondiente.
     *
     * Cuando no existe un tipo MIME registrado para alguno de los contextos,
     * se devuelve `null` en la propiedad correspondiente de `RouteMimeType`.
     *
     * @param string $route URI de la ruta cuyo tipo MIME se desea obtener.
     * @return RouteMimeType Estructura con los tipos MIME asociados a los contextos
     * público y privado de la ruta.
     */
    protected static function get_mime_type(string $route): RouteMimeType {
        return new RouteMimeType(
            private_mimetype: self::$mime_types[self::$route_identity->value . $route] ?? null,
            public_mimetype: self::$mime_types[$route] ?? null
        );
    }

    /**
     * Consulta las rutas y ejecuta el controlador en función de la ruta encontrada
     *
     * @return never
     */
    public static function run(): never {
        // TODO: Preparar las rutas para identificar la autenticación o no.

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
         * Ruta actual de la solicitud HTTP.
         * 
         * @var string
         */
        $route = DLServer::get_route();

        /**
         * Tipos MIME definidos explícitamente durante el registro de la ruta.
         *
         * Contiene los tipos MIME proporcionados mediante el parámetro `$mime_type` al registrar la ruta para
         * sus respectivos contextos.
         *
         * La inferencia automática del tipo MIME cuando `$mime_type` no es definido corresponde al procesamiento
         * interno del motor de enrutamiento y no a esta estructura.
         *
         * @var RouteMimeType $mime_type
         */
        $mime_type = self::get_mime_type($route);

        /**
         * Controlador asociado a la ruta y método de la petición.
         * 
         * @var mixed
         */
        $controller = self::get_validated_controller_context(
            controller_context: self::get_controller($route),
            route: $route
        );

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
        $output->print_response_data($mime_type->public_mimetype);

        exit;
    }

    /**
     * Obtiene el controlador correspondiente al contexto de autenticación de la ruta, validando previamente
     * las condiciones de acceso de la solicitud.
     *
     * Cuando la sesión es válida, devuelve el controlador privado asociado a la ruta. Cuando la sesión no es
     * válida, devuelve el controlador público asociado a la ruta.
     *
     * Si la ruta solamente dispone de un controlador privado y la solicitud no cuenta con una sesión válida, se
     * lanza una excepción de autorización.
     *
     * Si no existe un controlador correspondiente al contexto de autenticación actual, devuelve null.
     *
     * @param RouteContext $controller_context Contexto de controladores de la ruta.
     * @param string $route Ruta actual de la solicitud.
     * @return mixed Controlador correspondiente al contexto de autenticación actual o null si no existe.
     *
     * @throws UnauthorizedException Si la ruta requiere autenticación y no existe
     * una sesión válida.
     */
    private static function get_validated_controller_context(RouteContext $controller_context, string $route): mixed {

        /** @var mixed $public_controller */
        $public_controller = $controller_context->public_controller;

        /** @var mixed $private_controller */
        $private_controller = $controller_context->private_controller;

        /**
         * Determina si la ruta solamente dispone de un controlador privado
         * mientras la solicitud carece de una sesión válida.
         *
         * @var bool $context_auth
         */
        $context_auth = !self::$is_session_valid
            && ($private_controller !== null && $public_controller === null);

        if ($context_auth) {
            throw new UnauthorizedException(
                "Autenticación requerida en la ruta '{$route}'"
            );
        }

        return self::$is_session_valid && $private_controller !== null
            ? $private_controller
            : $public_controller;
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
     * Devuelve los controladores asociados a la ruta seleccionada.
     *
     * La resolución se realiza utilizando el método HTTP actual y la identidad de la ruta, permitiendo
     * obtener de forma independiente los controladores correspondientes a los contextos privado y público.
     *
     * Si el método HTTP o la ruta no se encuentran registrados, devuelve un `RouteContext` sin
     * controladores asociados.
     *
     * @param string $route Ruta seleccionada para la resolución.
     * @return RouteContext Contexto con los controladores público y privado asociados a la ruta.
     */
    protected static function get_controller(string $route): RouteContext {
        /**
         * Método HTTP actual.
         * 
         * @var non-empty-string $method
         */
        $method = DLServer::get_method();

        /**
         * Rutas asociadas al método de la petición
         * 
         * @var non-empty-array|null $routes
         */
        $routes = self::$routes[$method] ?? null;

        if (!\is_array($routes)) {
            return new RouteContext(
                private_controller: null,
                public_controller: null
            );
        }

        return new RouteContext(
            private_controller: $routes[self::$route_identity->value . $route] ?? null,
            public_controller: $routes[$route] ?? null
        );
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
     * Analiza la cadena del controlador en formato 'Clase@metodo' y devuelve la salida del método
     * al que apunta.
     *
     * Realiza un recorrido byte a byte sobre la cadena para localizar el separador '@', validando
     * que exista exactamente uno. Si no se encuentra ningún separador, o se encuentra más de uno,
     * responde con un error `500` detallando la causa (falta de separador o separador adicional,
     * respectivamente) y termina la ejecución.
     *
     * @param string $controller Cadena del controlador en formato 'Clase@metodo'.
     * @param array|object $data Datos que serán usados como un parámetro en el controlador.
     * @return mixed Salida del método del controlador.
     */
    protected static function string_controller(string $controller, array|object $data): mixed {
        $controller = \trim($controller);

        /** @var int $offset */
        $offset = 0;

        /** @var int $size */
        $size = \strlen($controller);

        /**
         * Cantidad de arrobas (@) encontradas.
         * 
         * @var int $quantity
         */
        $quantity = 0;

        /** @var int $offset_start */
        $offset_start = 0;

        /** @var integer $lexeme_length */
        $lexeme_length = 0;

        /** 
         * Posición del segundo separador '@' encontrado, usada para reportar el fragmento 
         * sobrante cuando hay más de un separador.
         * 
         * @var integer $error_offset_start
         */
        $error_offset_start = 0;

        while ($offset < $size) {
            /** @var non-empty-string $byte */
            $byte = $controller[$offset];

            if (self::AT_SIGN === $byte) {
                $lexeme_length = $offset;
                $quantity++;

                if ($quantity === 2) {
                    $error_offset_start = $offset;
                }
            }

            $offset++;
        }

        /** @var string $controller_name */
        $controller_name = \substr($controller, $offset_start, $lexeme_length);

        /** @var string $method */
        $method = \substr(
            string: $controller,
            offset: $lexeme_length + 1,
            length: $size - $lexeme_length
        );

        /** 
         * Fragmento del controlador a partir del segundo separador '@', usado únicamente en el
         * mensaje de error cuando hay más de un separador.
         * 
         * @var string $string_error
         */
        $string_error = \substr(
            string: $controller,
            offset: $error_offset_start,
            length: $size - $error_offset_start
        );

        /** @var non-empty-string $http_method */
        $http_method = \strtolower(DLServer::get_method());

        /**
         * Error capturado durante el análisis léxico
         * 
         * @var array{status: boolean, message: string} $error
         */
        $error = [];

        if ($quantity !== 1) {
            self::response_code(500);

            $error = ($quantity > 0) ? [
                "status" => false,
                "message" => "DLRoute::{$http_method}: El controlador '{$controller}' es inválido: se encontró un separador '@' adicional a partir de '{$string_error}' (posición '{$error_offset_start}'); solo se permite un '@' entre la clase y el método"
            ] : [
                "status" => false,
                "message" => "DLRoute::{$http_method}: No se definió el método a invocar para el controlador '{$controller}': falta el separador '@' seguido del nombre del método; el formato esperado es 'Clase@metodo'"
            ];

            if (self::is_production()) {
                self::set_error(
                    error: DLOutput::to_json($error, true)
                );

                /** Este error es genérico en producción. Revisar los archivos logs */
                $error = self::get_generic_error("Error en el controlador.");
            }

            echo DLOutput::to_json(
                content: $error,
                pretty: true
            );

            exit;
        }

        return self::array_controller(
            controller: [$controller_name, $method],
            data: $data
        );
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
    private static function get_generic_error(string $message = "Error del sistema"): string {
        return DLOutput::to_json([
            "status" => false,
            "message" => $message
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
