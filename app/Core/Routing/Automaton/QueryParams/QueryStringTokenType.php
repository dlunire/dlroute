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

namespace DLRoute\Core\Routing\Automaton\QueryParams;

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
 * @license AGPL-3.0 license
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