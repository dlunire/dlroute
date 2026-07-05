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

namespace DLRoute\Traits;

use DLRoute\Server\DLServer;

trait Path {

    /**
     * Devuelve la ruta en función del sistema operativo de ejecucion
     *
     * @param ?string $path Ruta relativa o absoluta
     * @return string
     */
    private function get_path(?string $path): string {

        if ($path === null) {
            $path = "";
        }

        /**
         * Patrón de búsqueda de letras de unidad
         * 
         * @var string $pattern_unit
         */
        $pattern_unit = "/[a-z]+:/i";

        $path = preg_replace($pattern_unit, '', $path);

        /**
         * Patrón de búsqueda de barras diagonales
         * 
         * @var string $pattern_path
         */
        $pattern_path = "/[\/\\\]+/";

        $path = preg_replace($pattern_path, DIRECTORY_SEPARATOR, $path);

        return $path;
    }

    /**
     * Devuelve una ruta absoluta a partir de una ruta relativa.
     * **Importante:** Cualquier ruta que se pase como argumento se considerará ruta relativa.
     *
     * @param string|null $path Ruta relativa
     * @return string
     */
    public function get_absolute_path(?string $path): string {

        if ($path === null) {
            $path = "";
        }

        /**
         * Directorio raíz del sistema
         * 
         * @var string $root
         */
        $root = DLServer::get_document_root();

        /**
         * Ruta absoluta del archivo enviado al servidor
         * 
         * @var string $absolute_path
         */
        $absolute_path = "{$root}/{$path}";

        return $this->get_path($absolute_path);
    }
}
