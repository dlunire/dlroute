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
use DLRoute\Interfaces\RouteInterface;
use DLRoute\Interfaces\Routing\LexerSymbolInterface;

abstract class Route extends DLParamValueType implements RouteInterface, LexerSymbolInterface {
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
        self::$route_context = $request->get_context();

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

        // print_r("ROUTE - " . $request->get_context()->registered_route . "\n");
        // print_r("ROUTE - " . "{$route_to_register} → " . ($context->registered_route ?? 'NULL') . "\n");

        // if ($request->get_context()->registered_route !== null) {
        //     print_r($request->get_context());
        // }

        # Esto que está aquí es nuevo:
        $route_controller = new RouteController(
            controller: $controller,
            fn: null,
            required_auth: static::$mark_routes_authenticated,
            params: self::$route_context,
            mimetype: $mimetype
        );

        if (self::$route_context->registered_route !== null) {
            print_r($route_controller);
        }
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

    
}
