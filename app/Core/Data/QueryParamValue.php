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
 *  - «null» → el valor está vacío («?campo=»), queda en blanco tras decodificar («?campo=%20»,
 *    «?campo=%20%20», «?campo=%09») o falta el operador «=» («?campo»). El autómata no distingue
 *    estos casos.
 *  - Si un parámetro se repite («a=1&a=2»), prevalece el último.
 *  - En el valor, «+» se interpreta como espacio.
 *  - En el nombre, el espacio («+», «%20») y el tabulador («%09») se convierten en «_»
 *    («a+b» → «a_b», «page%0910» → «page_10»). Nombres que difieren solo en esos caracteres producen
 *    la misma clave, y prevalece el último.
 *  - El resto de caracteres del nombre se conserva tal cual, incluidos «.» y «[]» («a.b» y «a[]» no se
 *    transforman, y «a[]» no se agrupa en un arreglo) y los caracteres de control como el salto de línea
 *    («%0A») y el retorno de carro («%0D»).
 *  - Las secuencias «%XX» válidas se decodifican. Una secuencia inválida («%zz» o un «%» suelto) se
 *    conserva literal.
 *  - Solo el primer «=» separa nombre y valor: en «a=1=2», el valor es «1=2».
 *  - Los parámetros sin nombre («=v») se descartan, igual que los segmentos vacíos entre «&» («a=1&&b=2»).
 *
 * Advertencia: «name» no se sanea. Quien lo escriba en registros, cabeceras HTTP o cualquier salida
 * sensible a saltos de línea debe escaparlo antes.
 *
 * @package DLRoute\Core\Data
 *
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2026 DLUnire
 * @license AGPL-3.0 license
 */
final class QueryParamValue {

    /**
     * @param string $name Nombre del parámetro, decodificado y normalizado (el espacio y el
     *        tabulador se convierten en «_»). Puede diferir del texto original, por lo que su
     *        longitud no sirve para recortarlo de la cadena original del querystring.
     *
     * @param int $offset Posición inicial en bytes del nombre del parámetro dentro de la cadena
     *        original del querystring (sin decodificar).
     *
     * @param string|null $value Valor decodificado del parámetro, o «null» si está vacío, queda en
     *        blanco tras decodificar o no tiene operador «=».
     *
     * @param int $offset_value Posición inicial en bytes del valor dentro de la cadena original del
     *        querystring. Vale 0 cuando «$value» es «null»: es un marcador intencional de «sin
     *        valor», porque un valor real nunca empieza en 0 (siempre lo precede «nombre=»).
     *
     * @param int $length Longitud en bytes del valor tal como aparece en la cadena original,
     *        antes de decodificarlo. Vale 0 cuando «$value» es «null», aunque el original tuviera
     *        bytes (por ejemplo «%20»).
     *
     *        El valor en bruto puede recuperarse así:
     *        ```php
     *        $raw = substr($query_string, $param->offset_value, $param->length);
     *        ```
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
