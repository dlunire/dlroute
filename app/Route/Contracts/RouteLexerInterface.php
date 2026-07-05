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

namespace DLRoute\Route\Contracts;

interface RouteLexerInterface {

    /**
     * Barra diagonal que separa la ruta en sus componentes
     * 
     * @var non-empty-string
     */
    public const SEPARATOR = "\x2f";

    /**
     * Marca de parámetro opcional en la definición de una ruta.
     * Se utiliza dentro de segmentos de ruta con la sintaxis `{param?}`
     * para indicar que el parámetro precedente puede estar ausente.
     *
     * @var non-empty-string
     */
    public const OPTIONAL_MARK = "\x3f";

    /**
     * Separador de query string en una URI.
     * Todo lo que sigue a este carácter forma parte de los parámetros
     * de consulta y debe ser excluido del análisis de segmentos de ruta.
     *
     * @var non-empty-string
     */
    public const QUERY_SEPARATOR = "\x3f";

    /**
     * Espacio en blanco a ser ignorado en el analizador léxico
     * 
     * @var non-empty-string
     */
    public const WHITE_SPACE = "\x20";

    /**
     * Subguión que será utilizado para reemplazar el espacio en blanco por él
     * durante el análisis léxio.
     * 
     * @var non-empty-string
     */ 
    public const UNDESCORE = "\x5f";

    public const OPEN_PARAM = "\x7b";

    public const CLOSE_PARAM = "\x7d";

    public const COLON_PARAM = "\x3a";
}
