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

/**
 * Representa un par «nombre → valor» compuesto a partir de los tokens capturados durante el análisis
 * léxico del querystring.
 *
 * Cada instancia es inmutable: sus propiedades se asignan una sola vez en el constructor y no pueden
 * modificarse posteriormente.
 *
 * Además del par «nombre → valor», expone metadatos de posición que permiten localizar cada parámetro
 * dentro de la cadena original del querystring sin necesidad de releerla. Todas las posiciones y
 * longitudes se miden en bytes sobre la cadena original, sin decodificar, mientras que «name» y «value»
 * se entregan decodificados.
 *
 * Reglas de análisis:
 *  - «null» → el valor está vacío («?campo=») o falta el operador «=» («?campo»). El autómata no distingue ambos
 *    casos.
 *  - Si un parámetro se repite («a=1&a=2»), prevalece el último.
 *  - «+» se interpreta como espacio y las secuencias «%XX» válidas se decodifican. Una secuencia inválida
 *    («%zz» o un «%» suelto» se
 *    conserva literal.
 *  - Solo el primer «=» separa nombre y valor: en «a=1=2», el valor es «1=2».
 *
 * @package DLRoute\Core\Data
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2026 DLUnire
 * @license AGPL-3.0 license
 */
final class QueryParamValue {

    /**
     * @param string $name Nombre del parámetro tal como fue capturado en el
     *        querystring, con decodificación URL aplicada.
     *
     * @param int $offset Posición inicial en bytes del nombre del parámetro
     *        dentro de la cadena del querystring.
     *        Permite localizar el nombre programáticamente usando «substr()»:
     *        ```php
     *        $name = substr($query_string, $param->offset, strlen($param->name));
     *        ```
     * @param string|null $value Valor del parámetro, o «null» si no fue asignado
     *        o si su valor era una cadena vacía o en blanco.
     * @param int $offset_value Posición inicial en bytes del valor del parámetro
     *        dentro de la cadena del querystring. Vale 0 cuando «$value» es «null»
     *        — el parámetro no tiene valor asignado y por tanto no tiene posición.
     * @param int $length Longitud en bytes del valor capturado. Vale 0 cuando
     *        «$value» es «null».
     */
    public function __construct(
        public readonly string $name,
        public readonly int $offset,
        public readonly ?string $value,
        public readonly int $offset_value,
        public readonly int $length
    ) {
    }
}
