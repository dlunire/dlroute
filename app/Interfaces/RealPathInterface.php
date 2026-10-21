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

interface RealPathInterface {

    /**
     * Devuelve la raíz real del proyecto.
     *
     * Es el directorio padre de la ruta real de {@see self::get_entry_point()},
     * es decir, un único nivel por encima del directorio web. Queda fuera del
     * directorio web. Si el directorio web es un enlace simbólico, es el padre
     * del destino y no del enlace.
     *
     * @return string Ruta absoluta, sin barra final.
     */
    public function get_document_root(): string;

    /**
     * Devuelve el directorio real que contiene el punto de entrada de la
     * aplicación (enlaces simbólicos resueltos).
     *
     * Es un DIRECTORIO, no un archivo, y su nombre es indiferente
     * (`public`, `web`, etc.). Para obtener el archivo, use
     * {@see self::get_entry_point_file()}.
     *
     * @return string Ruta absoluta, sin barra final.
     */
    public function get_entry_point(): string;

    /**
     * Devuelve el archivo que lanzó PHP, es decir, el punto de entrada real
     * (`get_included_files()[0]`).
     *
     * No depende de `$_SERVER`, por lo que su valor es el mismo en Apache,
     * Nginx, `php -S` y CLI.
     *
     * @return string Ruta absoluta del archivo.
     */
    public function get_entry_point_file(): string;
}
