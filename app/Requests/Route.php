<?php

namespace DLRoute\Requests;

use DLRoute\Requests\DLOutput;
use DLRoute\Server\DLServer;

abstract class Route extends DLParamValueType{
    use RouteParams;

    /**
     * Almacenamiento de rutas
     *
     * @var array
     */
    protected static array $routes = [];

    /**
     * Variables globales para el controlador.
     *
     * @var array|object
     */
    protected static array|object $vars = [];

    protected static array $mime_types = [];
    /**
     * Procesa la solicitud del usuario
     *
     * @param string $uri Ruta a registrar.
     * @param callable|array|string $controller
     * @param string $method Método de envío HTTP.
     * @param array|object $vars Datos que pueden ser usados como parámetros del método del controlador.
     * @return void
     */
    protected static function request(string $uri, callable|array|string $controller, string $method, array|object $vars, string $mime_type = null): void {
        self::register_routes($method, $uri, $controller);
        self::$vars[$method][$uri] = $vars;
        self::$mime_types[$uri] = $mime_type;
    }

    /**
     * Devuelve el tipo `mime` personalizado.
     *
     * @param string $route
     * @return void
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


        if (is_null($controller)) {
            DLOutput::not_found();
        }

        if (is_string($controller)) {
            $data = self::string_controller($controller, $vars);
        }

        if (is_callable($controller)) {
            $data = self::callable_controller($controller, $vars);
        }

        if (is_array($controller)) {
            $data = self::array_controller($controller, $vars);
        }

        $output = DLOutput::get_instance();

        $output->set_content($data);
        $output->print_response_data($mime_type);

        exit;
    }

    /**
     * Registra nuevas rutas
     *
     * @param string $route
     * @return void
     */
    protected static function register_routes(string $method, string $route, callable|array|string $controller): void {
        self::process_params($route);
        self::$routes[$method][$route] = $controller;
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

        if (!array_key_exists($method, self::$routes)) {
            return $controller;
        }

        if (!array_key_exists($route, self::$routes[$method])) {
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

        if (is_string($content)) {
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

        if (!is_string($controller_name)) {
            self::response_code(500);

            $error = DLOutput::get_json([
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

        if (!is_string($controller_method)) {
            self::response_code(500);

            $error = DLOutput::get_json([
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

            $error = DLOutput::get_json([
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

            $error = DLOutput::get_json([
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

        if (is_string($content)) {
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
        $quantity = count($matches[0]);

        /**
         * Información de errores del sistema en formato JSON.
         * 
         * @var string
         */
        $error = "";

        if ($quantity !== 1) {
            self::response_code(500);

            $error = DLOutput::get_json([
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

        if (is_array($parts_controller)) {
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
        $index = count($parts) - 1;

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

            $error = DLOutput::get_json([
                "status" => false,
                "error" => "Caracteres Inválidos"
            ], true);

            if (self::is_production()) {
                $_SESSION['error'] = $error;

                $error = DLOutput::get_json([
                    "error" => "Error del sistema"
                ]);
            }

            echo $error;
            exit;
        }

        if (!(preg_match($pascal_case_pattern, $controller_name))) {
            self::response_code(500);

            $error = DLOutput::get_json([
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

            $error = DLOutput::get_json([
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
        if (defined('DL_PRODUCTION')) {
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
        return DLOutput::get_json([
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

        if (!array_key_exists($method, self::$vars)) {
            return $vars;
        }

        if (!array_key_exists($route, self::$vars[$method])) {
            return $vars;
        }

        $vars = self::$vars[$method][$route] ?? [];

        return $vars;
    }
}
