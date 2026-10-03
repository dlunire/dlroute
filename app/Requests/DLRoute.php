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

use DLRoute\Core\Data\RouteHandler;
use DLRoute\Core\Routing\Automaton\Route\RequestRouteLexer;
use DLRoute\Core\Routing\Automaton\Route\RouteGenerator;
use DLRoute\Core\Routing\Automaton\Route\RouteIdentity;
use DLRoute\Enums\Methods;
use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\RouteInterface;
use DLRoute\Server\DLServer;

// TODO: pendiente por eliminar `self::$route = $route;` después de implementar el autómata.

/**
 * Define el sistema de enrutamiento del sistema.
 * 
 * @package DLRoute\Requests
 * 
 * @version v1.0.1
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2023 David E Luna M
 * @license AGPL-3.0 license
 */
final class DLRoute extends Route implements RouteInterface {
    private static ?self $instance = null;

    public static function query(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::QUERY);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;
            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::QUERY,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function get(string $route, callable|array|string $controller, array $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::GET);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;

            print_r("\$route: {$route}\n");

            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::GET,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function head(string $route, callable|array|string $controller, array|object $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::HEAD);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;
            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::HEAD,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function post(string $route, callable|array|string $controller, array|object $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::POST);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;
            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::POST,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function put(string $route, callable|array|string $controller, array|object $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::PUT);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;
            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::PUT,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function patch(string $route, callable|array|string $controller, array|object $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::PATCH);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;
            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::PATCH,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function delete(string $route, callable|array|string $controller, array|object $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::DELETE);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;
            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::DELETE,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function options(string $route, callable|array|string $controller, array|object $varnames = [], ?string $mimetype = null): DLParamValueType {
        /** @var RouteGenerator $routes */
        $routes = new RouteGenerator($route, Methods::OPTIONS);

        /** @var RequestRouteLexer $params */
        $params = new RequestRouteLexer(
            route_tokens: $routes->get_tokens()
        );

        $routes->load_routes(function (string $route) use ($controller, $varnames, $mimetype, $params) {
            self::$route = $route;
            self::request(
                route: $route,
                controller: $controller,
                request: $params,
                method: Methods::OPTIONS,
                vars: $varnames,
                mimetype: $mimetype
            );
        });

        return self::get_instance();
    }

    public static function match(array $methods, RouteHandler $route): void {

        if (\count($methods) < 1) {
            throw new RouteException("Debe definir, al menos, un método HTTP", 500);
        }

        /**
         * Filtros listos para ser utilizado para los métodos HTTP
         * que comparten la misma ruta.
         * 
         * @var array<string,string> $filters
         */
        $filters = $route->handler_filters;

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
    }

    /**
     * Corre el sistema de rutas.
     * 
     * @return void
     */
    public static function execute(): void {

        $params = [];

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

        /** @var non-empty-string $route_with_required_authentication */
        $route_with_required_authentication = RouteIdentity::PRIVATE->value . $route;

        /**
         * Ruta actualmente registrada con parámetros
         * 
         * @var string|null $registered_current_route
         */
        $registered_current_route = self::$is_valid_session
            ? self::$current_param[$route_with_required_authentication] ?? null
            : self::$current_param[$route] ?? null;

        if ($registered_current_route === null) {
            $registered_current_route = self::$current_param[$route] ?? null;
        }

        if ($params === null) {
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

        $instance->filter_param($current_filters, (object) $params);
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
