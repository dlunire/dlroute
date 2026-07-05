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

declare(strict_types=1);

namespace DLRoute\Enums;

/**
 * Métodos HTTP soportados por el enrutador (router).
 *
 * Define el conjunto de verbos HTTP que el sistema de enrutamiento
 * reconoce y despacha. Cada caso representa un método estándar
 * definido en RFC 7231 y RFC 5789.
 *
 * Uso:
 * ```php
 * Methods::GET->value;   // "GET"
 * Methods::HEAD->value;  // "HEAD"
 * ```
 *
 * @package DLRoute\Enums
 *
 * @version v1.0.6 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
enum Methods: string {

    /** Solicita la representación de un recurso sin efectos secundarios. */
    case GET = "GET";

    /** Idéntico a GET pero sin body en la respuesta. Útil para scrapers y verificación de recursos. */
    case HEAD = "HEAD";

    /** Describe las opciones de comunicación disponibles para el recurso destino (CORS preflight). */
    case OPTIONS = "OPTIONS";

    /** Envía datos al servidor para crear o procesar un recurso. */
    case POST = "POST";

    /** Reemplaza completamente la representación del recurso destino. */
    case PUT = "PUT";

    /** Aplica modificaciones parciales a un recurso existente. */
    case PATCH = "PATCH";

    /** Elimina el recurso especificado. */
    case DELETE = "DELETE";
}