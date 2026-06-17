<?php

namespace DLRoute\Requests;

use DLRoute\Core\Data\RouteHandler;
use DLRoute\Core\Routing\Automaton\Route\RouteGenerator;
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


    public static function match(array $methods, RouteHandler $route): RouteHandler {

        if (\count($methods) < 1) {
            throw new RouteException("Debe definir, al menos, un método HTTP", 500);
        }

        /**
         * Devuelve los filtros listos para ser utilizado para los métodos HTTP
         * que comparten la misma ruta.
         * 
         * @var array<string,string> $filters
         */
        $filters = $route->get_filters();

        /**
         * Devuelve la cantidad de tipos definidos en `RouteHandler::filter_by_type(...)`
         * 
         * @var int $quantity
         */
        $quantity = $route->get_quantity();

        foreach ($methods as $method) {
            if (!($method instanceof Methods)) {
                /** @var non-empty-string $fragment */
                $fragment = print_r($method, true);
                throw new RouteException("DLRoute::match: Se esperaba «DLRoute\Enums\Methods» como elemento de «\$methods». En su lugar se recibió «{$fragment}»");
            }

            /** @var non-empty-string $method_name */
            $method_name = \strtolower($method->value);

            if ($quantity > 0) {
                self::{$method_name}($route->uri, $route->controller, $route->data, $route->mime_type)
                    ->filter_by_type($filters);
                continue;
            }

            self::{$method_name}($route->uri, $route->controller, $route->data, $route->mime_type);
        }

        return $route;
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
