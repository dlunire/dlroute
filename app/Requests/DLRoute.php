<?php

namespace DLRoute\Requests;

use DLRoute\Interfaces\RouteInterface;
use DLRoute\Server\DLServer;

/**
 * Define el sistema de enrutamiento del sistema.
 * 
 * @package DLRoute
 * 
 * @version 1.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 * 
 */
class DLRoute extends Route implements RouteInterface {
    private static ?self $instance = null;

    public static function get(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        self::$route = $uri;

        if (!DLServer::is_get()) {
            return self::get_instance();
        }

        self::request($uri, $controller, 'GET', $data, $mime_type);

        return self::get_instance();
    }

    public static function post(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        self::$route = $uri;

        if (!DLServer::is_post()) {
            return self::get_instance();
        }

        self::request($uri, $controller, 'POST', $data, $mime_type);

        return self::get_instance();
    }

    public static function put(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        self::$route = $uri;

        if (!DLServer::is_put()) {
            return self::get_instance();
        }

        self::request($uri, $controller, 'PUT', $data, $mime_type);

        return self::get_instance();
    }

    public static function patch(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        self::$route = $uri;

        if (!DLServer::is_patch()) {
            return self::get_instance();
        }

        self::request($uri, $controller, 'PATCH', $data, $mime_type);

        return self::get_instance();
    }

    public static function delete(string $uri, callable|array|string $controller, array|object $data = [], ?string $mime_type = null): DLParamValueType {
        self::$route = $uri;

        if (!DLServer::is_delete()) {
            return self::get_instance();
        }

        self::request($uri, $controller, 'DELETE', $data, $mime_type);

        return self::get_instance();
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

        if (is_null($instance)) {
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

        if (is_null(self::$params)) {
            self::run();
        }

        if (is_null($registered_current_route)) {
            self::run();
        }

        if (!array_key_exists($method, $filters)) {
            self::run();
        }

        if (!array_key_exists($registered_current_route, $filters[$method])) {
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
