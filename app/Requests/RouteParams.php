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

use DLRoute\Core\Data\RouteData\RouteContext;
use DLRoute\Core\Data\RouteData\RouteController;
use DLRoute\Core\Routing\Automaton\Route\RouteIdentity;
use DLRoute\Routes\RouteDebugger;

trait RouteParams {
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
    protected static RouteIdentity $route_identity = RouteIdentity::PRIVATE;

    /**
     * Indica si las rutas a registrar deben marcarse como autenticadas.
     *
     * @var boolean
     */
    protected static bool $mark_routes_authenticated = false;

    /**
     * Indica si la sesión actual es válida.
     *
     * @var boolean
     */
    protected static bool $is_valid_session = false;

    /**
     * Rutas registradas por el usuario o desarrollador. Incluye la identidad de la ruta.
     *
     * @var array<string, RouteController> $routes
     */
    protected static array $routes = [];

    /**
     * Contexto de la ruta lista para ser utilizada por el despachador.
     *
     * @var RouteContext
     */
    protected static RouteContext $route_context;
}