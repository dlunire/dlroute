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
use DLRoute\Core\Data\RouteData\RouteController;
use DLRoute\Core\Routing\Automaton\Route\RouteMatcher;
use DLRoute\Core\Routing\Automaton\Route\RouteIdentity;
use DLRoute\Enums\Methods;
use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\RouteInterface;
use DLRoute\Interfaces\Routing\LexerSymbolInterface;
use DLRoute\Server\Server;

abstract class AbstractRoute extends DLParamValueType implements RouteInterface, LexerSymbolInterface {
    use RouteParams;

    /**
     * Registra una ruta y sus metadatos asociados en el autómata de enrutamiento.
     *
     * Cuando el contexto de registro requiere autenticación, la identidad interna de la ruta se construye
     * anteponiendo el identificador definido por {@see RouteType::AUTH}. Este identificador permite distinguir
     * una ruta autenticada de una ruta pública con la misma URI.
     *
     * La identidad generada es exclusivamente interna y no modifica la URI expuesta al cliente.
     *
     * @param string $route URI de la ruta a registrar.
     * @param callable|array|string $controller Controlador asociado a la ruta.
     * @param Methods $method Método HTTP asociado a la ruta.
     * @param non-empty-string|null $mimetype Tipo MIME de la respuesta,
     * opcionalmente especificado para la ruta.
     *
     * @return void
     */
    protected static function request(string $route, callable|array|string $controller, RouteMatcher $request,  Methods $method, ?string $mimetype = null): void {
        /** @var RouteContext $route_context */
        $route_context = $request->get_context();

        if ($route_context->registered_route !== null) {
            self::$route_context = $route_context;
        }

        /**
         * Indica si la ruta requiere ser autenticada.
         * 
         * @var boolean $require_auth
         */
        $require_auth = self::$mark_routes_authenticated;

        self::$route_identity = $require_auth
            ? RouteIdentity::PRIVATE
            : RouteIdentity::PUBLIC;

        /**
         * Ruta final de registro
         * 
         * @var non-empty-string $route_to_register
         */
        $route_to_register = self::$route_identity->value . "{$method->value}|" . $route;

        # Esto que está aquí es nuevo:
        $route_controller = new RouteController(
            controller: $controller,
            required_auth: static::$mark_routes_authenticated,
            params: $route_context,
            mimetype: $mimetype
        );

        // TODO: La clave de registro es: <Identity>|<Method>|<Route> y el controlador
        // contiene toda la metadata. Por lo tanto, quito este TODO en cuanto haya terminado
        // de escribir el despachador de la ruta.
        self::$routes[$route_to_register] = $route_controller;
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
        static::$is_valid_session = $session->is_valid_session;
        static::$mark_routes_authenticated = $requires_authentication;
    }

    /**
     * Despachador de ruta que hacen MATCH con la identidad
     *
     * @return void
     */
    public static function dispatch(): void {
        if (!isset(self::$route_context)) {
            DLOutput::not_found();
        }

        /** @var RouteController|null $route_controller */
        $route_controller = self::$routes[(self::$is_valid_session
            ? RouteIdentity::PRIVATE->value
            : RouteIdentity::PUBLIC->value)
            . Server::get_method() . "|"
            . self::$route_context->registered_route] ?? null;

        if (!($route_controller instanceof RouteController)) {
            DLOutput::not_found();
        }

        // TODO: Esto que sigue por acá es una solución provisional que será modificada las próximas horas
        // para uso definitivo con las identidades de las rutas.

        $controller = $route_controller->controller;

        self::determine_controller_type($route_controller);

        if (!\is_array($controller)) {
            DLOutput::not_found();
        }

        // TODO: Reescribir esta parte, pero sin eliminar toda la lógica, sino agregar algunos filtros necesarios
        // para el funcionamiento pleno de los controladores.

        $classname = $controller[0] ?? null;
        $method_name = $controller[1] ?? null;

        /** @var string|null $mimetype */
        $mimetype = $route_controller->mimetype;

        if (\class_exists($classname) && \method_exists($classname, $method_name)) {
            
            $value = (new $classname)?->{$method_name}(self::$route_context->params) ?? null;

            header(
                header: "Content-type: " . ($mimetype !== null ? $mimetype : "application/json") . "; charset=utf-8",
                replace: true,
                response_code: 200
            );

            echo DLOutput::to_json(
                content: $value,
                pretty: true
            );

            exit;
        } else {
            throw new RouteException("El controlador no está definido");
        }
    }

    private static function determine_controller_type(RouteController $controller): void {
    }
}
