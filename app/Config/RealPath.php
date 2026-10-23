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

namespace DLRoute\Config;

use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\InstanceInterface;
use DLRoute\Interfaces\RealPathInterface;

class RealPath implements RealPathInterface, InstanceInterface {

    /**
     * Raíz del proyecto: padre de la ruta real de {@see self::$entry_point}.
     * Queda fuera del directorio web.
     *
     * @var non-empty-string $document_root
     */
    private readonly string $document_root;

    /**
     * Directorio real que contiene el punto de entrada (enlaces simbólicos
     * resueltos). Es un DIRECTORIO, no un archivo, y su nombre es indiferente.
     *
     * @var non-empty-string $entry_point
     */
    private readonly string $entry_point;

    /**
     * Archivo que lanzó PHP (`get_included_files()[0]`), tal como lo entrega PHP.
     *
     * @var non-empty-string $entry_point_file
     */
    private readonly string $entry_point_file;

    /**
     * Instancia actual de la clase
     *
     * @var static|null $instance
     */
    private static ?self $instance = null;

    private function __construct() {
        $this->set_path();
    }

    public static function from(): static {
        if (!(static::$instance instanceof static)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /** {@inheritDoc} */
    public function get_document_root(): string {
        return $this->document_root;
    }

    /** {@inheritDoc} */
    public function get_entry_point(): string {
        return $this->entry_point;
    }

    /** {@inheritDoc} */
    public function get_entry_point_file(): string {
        return $this->entry_point_file;
    }

    /**
     * Resuelve el punto de entrada y la raíz del proyecto a partir del script
     * que lanzó PHP.
     *
     * Esta resolución no lee `$_SERVER`: depende solo de `get_included_files()[0]`, por lo que da
     * el mismo resultado en Apache, Nginx, `php -S` y CLI.
     *
     * Convención de DLUnire:
     * - `entry_point` es el DIRECTORIO real que contiene el script de entrada (por ejemplo `.../public`),
     *    no el archivo. Su nombre es indiferente.
     * - `document_root` es el padre de `entry_point`, es decir, un único nivel por encima. Queda fuera
     *    del directorio web.
     * - Ambos se calculan sobre la ruta REAL: si el directorio web es un enlace simbólico, `document_root` es
     *   el padre del destino, no del enlace.
     *
     * Este método NO calcula el prefijo de montaje de la URL (por ejemplo `/dir/dir`). Ese valor no se puede
     * derivar del sistema de archivos; lo resuelve el autómata sobre la URI, con `BASE_DIR` como override opcional.
     *
     * @return void
     *
     * @throws RouteException Si el directorio del punto de entrada no se puede
     *                        resolver con `realpath()`.
     */
    private function set_path(): void {
        $this->entry_point_file = get_included_files()[0];

        /**
         * Directorio real del punto de entrada (enlaces simbólicos resueltos).
         *
         * @var string|false
         */
        $entry_point = \realpath(
            path: \dirname($this->entry_point_file)
        );

        if ($entry_point === false) {
            throw new RouteException(
                message: "No se pudo resolver el directorio del punto de entrada."
            );
        }

        $this->entry_point   = $entry_point;
        $this->document_root = \dirname($entry_point);
    }
}
