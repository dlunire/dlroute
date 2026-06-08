<?php

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
 * @license MIT
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