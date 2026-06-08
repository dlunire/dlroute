<?php

namespace DLRoute\Requests;

use DLRoute\Core\Routing\Automaton\RouteGenerator;
use DLRoute\Enums\Methods;
use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\RouteInterface;
use DLRoute\Server\DLServer;

/**
 * Define el sistema de enrutamiento del sistema.
 * 
 * @package DLRoute
 * 
 * @version v1.0.1
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
class DLRoute extends Route implements RouteInterface {
    private static ?self $instance = null;

    public static function get(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        $routes = new RouteGenerator($uri);

        $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type) {
            self::$route = $uri;

            if (!DLServer::is_get()) {
                return self::get_instance();
            }

            self::request($uri, $controller, Methods::GET, $data, $mime_type);
        });

        return self::get_instance();
    }

    /**
     * Define una ruta para manejar solicitudes `HTTP HEAD`.
     * 
     * Permite definir una ruta para manejar solicitudes `HTTP HEAD`. El callback o controlador
     * proporcionado se ejecutará cuando la URI definida sea accedida utilizando
     * el método `HTTP HEAD`.
     * 
     * @param string $uri Patrón URI que se comparará con las solicitudes entrantes
     * @param callable|array|string $controller `callback` o controlador encargado de manejar la solicitud
     * @param array|object $data Permite implementar datos adicionales al controlador.
     * @param mixed $mime_type Permite establecer el tipo MIME de respuesta al cliente.
     * @return DLParamValueType
     */
    public static function head(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        $routes = new RouteGenerator($uri);

        $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type) {
            self::$route = $uri;

            if (!DLServer::is_head()) {
                return self::get_instance();
            }

            self::request($uri, $controller, Methods::HEAD, $data, $mime_type);
        });
        return self::get_instance();
    }

    /**
     * Define una ruta para manejar solicitudes `HTTP OPTIONS`.
     * 
     * Permite definir una ruta para manejar solicitudes `HTTP OPTIONS`. El callback o controlador
     * proporcionado se ejecutará cuando la URI definida sea accedida utilizando
     * el método `HTTP OPTIONS`.
     * 
     * @param string $uri Patrón URI que se comparará con las solicitudes entrantes
     * @param callable|array|string $controller `callback` o controlador encargado de manejar la solicitud
     * @param array|object $data Permite implementar datos adicionales al controlador.
     * @param mixed $mime_type Permite establecer el tipo MIME de respuesta al cliente.
     * @return DLParamValueType
     */
    public static function options(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        $routes = new RouteGenerator($uri);

        $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type) {
            self::$route = $uri;

            if (!DLServer::is_options()) {
                return self::get_instance();
            }

            self::request($uri, $controller, Methods::OPTIONS, $data, $mime_type);
        });
        return self::get_instance();
    }

    public static function post(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        $routes = new RouteGenerator($uri);

        $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type) {
            self::$route = $uri;

            if (!DLServer::is_post()) {
                return self::get_instance();
            }

            self::request($uri, $controller, Methods::POST, $data, $mime_type);
        });
        return self::get_instance();
    }

    public static function put(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        $routes = new RouteGenerator($uri);

        $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type) {
            self::$route = $uri;

            if (!DLServer::is_put()) {
                return self::get_instance();
            }

            self::request($uri, $controller, Methods::PUT, $data, $mime_type);
        });
        return self::get_instance();
    }

    public static function patch(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        $routes = new RouteGenerator($uri);

        $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type) {
            self::$route = $uri;

            if (!DLServer::is_patch()) {
                return self::get_instance();
            }

            self::request($uri, $controller, Methods::PATCH, $data, $mime_type);
        });
        return self::get_instance();
    }

    public static function delete(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        $routes = new RouteGenerator($uri);

        $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type) {
            self::$route = $uri;

            if (!DLServer::is_delete()) {
                return self::get_instance();
            }

            self::request($uri, $controller, Methods::DELETE, $data, $mime_type);
        });
        return self::get_instance();
    }

    /**
     * Resumen de métodos HTTP que comparten la misma ruta
     * @param array<Methods> $methods Métodos HTTP a la ruta asignada.
     * @param string $uri Ruta a ser registrada con los métodos soportados por el enrutador.
     * @param callable|array|string $controller Callback o controlador encargado de manejar la solicitud
     * @param array|object $data Permite implementar datos adicionales al controlador
     * @param non-empty-string|null $mime_type Opcional. Permite establecer el tipo MIME de respueta al cliente.
     * @return void
     * 
     * @throws RouteException Es lanzada si el método ingresado por el usuario no está soportado y/o es inválido.
     */
    public static function match(array $methods, string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): void {
        self::$route = $uri;
        
        if (\count($methods) < 1) {
            return;
        }

        foreach ($methods as $method) {

            if (!($method instanceof Methods)) {
                /** @var string $value */
                $value = \preg_replace("/\s+/", ' ', print_r($method, true));

                /** @var string $type */
                $type = \gettype($method);

                throw new RouteException(
                    "El método «{$value}» no está soportado. Además, se esperaba un ENUM, pero en su lugar, devolvió «{$type}»"
                );
            }
            $routes = new RouteGenerator($uri);

            $routes->load_routes(function (string $uri) use ($controller, $data, $mime_type, $method) {
                self::request($uri, $controller, $method, $data, $mime_type);
            });
        }
    }

    /**
     * Corre el sistema de rutas.
     * 
     * @return void
     */
    public static function execute(): void {
        /**
         * Instancia de esta clase.
         * 
         * @var self
         */
        $instance = self::$instance;

        if ($instance === null) {
            self::run();
        }

        /**
         * Filtros creados por el usuario desarrollador.
         * 
         * @var array
         */
        $filters = $instance->get_filters();

        /**
         * Método HTTP actual de ejecución
         * 
         * @var string
         */
        $method = DLServer::get_method();

        /**
         * Ruta HTTP actual de ejecución.
         * 
         * @var string
         */
        $route = DLServer::get_route();

        /**
         * Ruta actual registrada.
         * 
         * @var string
         */
        $registered_current_route = self::$current_param[$route] ?? null;

        if (self::$params === null) {
            self::run();
        }

        if ($registered_current_route === null) {
            self::run();
        }

        if (!\array_key_exists($method, $filters)) {
            self::run();
        }

        if (!\array_key_exists($registered_current_route, $filters[$method])) {
            self::run();
        }

        /**
         * Filtros actuales
         * 
         * @var array
         */
        $current_filters = $filters[$method][$registered_current_route];

        $instance->filter_param($current_filters, self::$params);

        self::run();
    }

    private static function get_instance(): self {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Devuelve las rutas registrada del método actual enviado por el cliente HTTP
     *
     * @return array
     */
    public static function get_routes(): array {
        return self::$routes;
    }
}
