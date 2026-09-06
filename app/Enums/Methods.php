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
 * Métodos de solicitud soportados por el enrutador (router).
 *
 * Define el conjunto de métodos que DLRoute reconoce como contextos
 * de solicitud para el registro y despacho de rutas.
 *
 * Los casos correspondientes a métodos HTTP representan los verbos
 * utilizados por las solicitudes HTTP. El caso `QUERY` corresponde al
 * método HTTP QUERY, mientras que su tratamiento forma parte de la
 * infraestructura de enrutamiento de DLRoute.
 *
 * Cada caso expone mediante `value` la representación textual utilizada
 * internamente por el enrutador.
 *
 * Uso:
 * ```php
 * Methods::GET->value;    // "GET"
 * Methods::POST->value;   // "POST"
 * Methods::QUERY->value;  // "QUERY"
 * ```
 *
 * @package DLRoute\Enums
 *
 * @version v1.0.6 (release)
 * @author David E Luna M <info@dlunire.dev>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0-or-later
 */
enum Methods: string {

    /**
     * Realiza una consulta sobre un recurso sin modificar su estado.
     *
     * `QUERY` es reconocido por `DLRoute` como un método de solicitud independiente dentro
     * de su infraestructura de enrutamiento.
     * 
     * @var string
     */
    case QUERY = "QUERY";

    /**
     * Solicita la representación de un recurso.
     * 
     * @var string
     */
    case GET = "GET";

    /**
     * Solicita los mismos metadatos de respuesta que `GET`, pero sin transferir el contenido de
     * la representación en el cuerpo de la respuesta.
     * 
     * @var string
     */
    case HEAD = "HEAD";

    /**
     * Solicita las opciones de comunicación disponibles para el recurso destino. Es utilizado,
     * entre otros escenarios, para solicitudes de preflight de CORS.
     * 
     * @var string
     */
    case OPTIONS = "OPTIONS";

    /**
     * Envía una representación al recurso destino para su procesamiento.
     * 
     * @var string
     */
    case POST = "POST";

    /**
     * Reemplaza la representación actual del recurso destino.
     * 
     * @var string
     */
    case PUT = "PUT";

    /**
     * Aplica modificaciones parciales sobre la representación del recurso destino.
     * 
     * @var string
     */
    case PATCH = "PATCH";

    /**
     * Solicita la eliminación del recurso destino.
     * 
     * @var string
     */
    case DELETE = "DELETE";
}
