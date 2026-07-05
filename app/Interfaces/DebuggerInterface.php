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

namespace DLRoute\Interfaces;

interface DebuggerInterface {

    /**
     * Depura y limpia las rutas. Si las rutas contienen más de dos slash (/) seguidas,
     * entonces, removerá las sobrantes y dejará una por cada separación de directorios.
     *
     * @param string $route
     * @return string
     */
    public static function clear_route(string $route): string;

    /**
     * Procesa las rutas que apuntan directo al recurso.
     *
     * @param string $path Ruta que apunta directo al recurso.
     * @return string
     */
    public static function process_route(string $path): string;

    /**
     * Remueve la última o últimas diagonales en la ruta. Por ejemplo, si la ruta está definida
     * como se muestra a continuación:
     * 
     * ```
     * /ruta/con/un/slash/
     * /ruta/index.php/
     * ```
     * 
     * Devolverá lo siguiente:
     * 
     * ```
     * /ruta/con/un/slash
     * /ruta/index.php
     * ```
     *
     * @param string $path Ruta al que removerá la última o últimas diagonales
     * @return string
     */
    public static function remove_trailing_slash(string $path): string;

    /**
     * Elimina las barras diagonales, al principio y final de una cadena
     * de texto.
     *
     * @param string $path
     * @return string
     */
    public static function trim_slash(string $path): string;

    /**
     * Reemplaza los puntos por barras diagonales `/`.
     *
     * @param string $path Ruta que será procesada.
     * @return string
     */
    public static function dot_to_slash(string $path): string;

    /**
     * Codifica la URL
     *
     * @param string $path
     * @return string
     */
    public static function url_encode(string $path): string;
}