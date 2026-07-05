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

namespace DLRoute\Http;

use DLRoute\Traits\Request;

/**
 * Clase base abstracta para manejar solicitudes HTTP dentro del sistema DLRoute.
 *
 * Esta clase proporciona una estructura fundamental para la gestión de peticiones HTTP,
 * integrando el *trait* `Request`, el cual contiene la lógica principal para la configuración,
 * ejecución y manejo de respuestas de las solicitudes.
 *
 * Su propósito es servir como clase madre para implementaciones concretas que
 * extiendan las capacidades del sistema HTTP (por ejemplo, clientes personalizados,
 * integraciones API o adaptadores de transporte).
 *
 * @package DLRoute\Http
 * @version v0.0.1
 * @license AGPL-3.0 license
 * @author David E Luna M
 * @copyright Copyright (c) 2025 David E Luna M
 */
abstract class HttpRequest {
    use Request;
}
