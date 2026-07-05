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

namespace DLRoute\Core\Data;

use DLRoute\Core\Routing\Automaton\QueryParams\QueryStringTokenType;

/**
 * Representa un token capturado durante el análisis léxico del querystring.
 *
 * Cada instancia es inmutable — sus propiedades se asignan una sola vez
 * en el constructor y no pueden modificarse posteriormente. Esto garantiza
 * que los datos de la petición no sean alterados después del análisis.
 *
 * Un parámetro del querystring produce una o dos instancias de QueryParam:
 *  - Sin «=» → una instancia de tipo QUERY_NAME (value implícito null)
 *  - Con «=» → dos instancias: QUERY_NAME y QUERY_VALUE
 * 
 * @package DLRoute\Core\Data;
 * 
 * @version v1.0.0 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 * @license AGPL-3.0 license
 */
final class QueryParam {

    /**
     * @param string $lexeme Secuencia de bytes que conforman el token capturado.
     * @param int $offset Posición inicial del token dentro de la cadena del querystring.
     * @param QueryStringTokenType $type Tipo del token capturado: QUERY_NAME o QUERY_VALUE.
     * @param int $length Longitud en bytes del lexema capturado.
     */
    public function __construct(
        public readonly string $lexeme,
        public readonly int $offset,
        public readonly QueryStringTokenType $type,
        public readonly int $length,
    ) {}
}