<?php

/**
 * Copyright (c) 2025 David E Luna M
 * Licensed under the MIT License. See LICENSE file for details.
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
 * @license MIT
 * @author David E Luna M
 * @copyright Copyright (c) 2025 David E Luna M
 */
abstract class HttpRequest {
    use Request;
}
