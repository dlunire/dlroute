<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

/**
 * Representa el tipo de un token capturado durante el análisis léxico
 * del querystring de la petición HTTP.
 *
 * Un parámetro de querystring tiene la forma «nombre=valor», por lo que
 * el autómata emite exactamente dos tipos de tokens por parámetro:
 * primero QUERY_NAME y luego QUERY_VALUE. Cuando un parámetro no tiene
 * valor asignado (e.g. «?activo»), se emite QUERY_NAME y QUERY_VALUE
 * queda con valor null.
 * 
 * @package DLRoute\Core\Routing\Automaton
 * @license MIT
 * @version v1.0.0 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 */
enum QueryStringTokenType {

    /**
     * Indica que el token capturado corresponde al nombre del parámetro.
     *
     * Ejemplo: en «nombre=David», el token «nombre» es de tipo QUERY_NAME.
     */
    case QUERY_NAME;

    /**
     * Indica que el token capturado corresponde al valor del parámetro.
     *
     * Ejemplo: en «nombre=David», el token «David» es de tipo QUERY_VALUE.
     * Cuando el parámetro no tiene valor asignado, este token se emite con
     * valor null.
     */
    case QUERY_VALUE;
}