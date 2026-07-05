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

namespace DLRoute\Requests;

use DLRoute\Traits\ImageTrait;
use DLRoute\Traits\Path;

/** 
 * Permite procesar la subida de archivos al servidor.
 * 
 * @package DLRoute\Requests
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2024 David E Luna M
 * @license AGPL-3.0 license
 */

final class Filename {

    use ImageTrait, Path;

    /**
     * Nombre del archivo
     *
     * @var string $name
     */
    public readonly string $name;

    /**
     * Archivo de destino
     *
     * @var string $target_file
     */
    public string $target_file;

    /**
     * Nombre temporal del archivo al momento de subir
     *
     * @var string $tmp_name
     */
    public readonly string $tmp_name;

    /**
     * Ruta completa del archivo proporcionada por $_SERVER
     *
     * @var string $full_path;
     */
    public readonly string $full_path;

    /**
     * Tipo MIME del archivo enviado al servidor
     *
     * @var string $type
     */
    public readonly string $type;

    /**
     * Formato de archivo
     *
     * @var string $file_format
     */
    public readonly string $file_format;

    /**
     * Tamaño en bytes del archivo
     *
     * @var int $size;
     */
    public readonly int $size;

    /**
     * Tamaño del archivo en formato legible
     *
     * @var string $readable_size
     */
    public readonly string $readable_size;

    /**
     * Indica si se produjo un error en el archivo durante la subida
     *
     * @var integer $error
     */
    public readonly int $error;

    /**
     * Directorio base del archivo
     *
     * @var string $basedir
     */
    public readonly string $basedir;

    /**
     * Ruta absoluta del directorio base
     *
     * @var string $absolute_basedir
     */
    public readonly string $absolute_basedir;

    /**
     * Ruta relativa del directorio donde se enviará el archivo
     *
     * @var string $relative_path
     */
    public readonly string $relative_path;

    /**
     * Ruta absoluta del directorio de archivo
     *
     * @var string $absolute_path
     */
    public readonly string $absolute_path;

    /**
     * Ruta relativa del directorio de vista previa de imagen (en el caso de que aplique).
     *
     * @var string $relative_path_thumbnail
     */
    public readonly string $relative_path_thumbnail;

    /**
     * Vista previa de la imagen
     *
     * @var string|null $thumbnail
     */
    public readonly ?string $thumbnail;

    /**
     * Ruta absoluta de la vista previa
     *
     * @var string $absolute_path_thumbnail
     */
    public readonly string $absolute_path_thumbnail;

    public function __construct(array $attributes = []) {
        $this->name = $attributes['name'] ?? '';
        $this->tmp_name = $attributes['tmp_name'] ?? '';
        $this->full_path = $this->get_path($attributes['full_path'] ?? '');
        $this->type = $attributes['type'] ?? '';
        $this->file_format = $attributes['file_format'] ?? '';
        $this->size = $attributes['size'] ?? 0;
        $this->readable_size = $attributes['readable_size'] ?? '';
        $this->error = $attributes['error'] ?? 0;
        $this->basedir = $this->get_path($attributes['basedir'] ?? '');
        $this->relative_path = $this->get_path($attributes['relative_path'] ?? '');
        $this->relative_path_thumbnail = $this->get_path($attributes['relative_path_thumbnail'] ?? '');

        ## ÁREA DE ARCHIVOS DE DESTINO
        $this->target_file = $this->get_path("{$this->relative_path}/{$this->name}");

        ## ÁREA DE RUTAS ABSOLUTAS
        $this->absolute_path = $this->get_absolute_path($this->relative_path);
        $this->absolute_path_thumbnail = $this->get_absolute_path($this->relative_path_thumbnail);
        $this->absolute_basedir = $this->get_absolute_path($this->basedir);

        $this->set_routes();
    }

    /**
     * Este método es un solo uso. Establece la vista previa a cargar
     *
     * @param string|null $path
     * @return void
     */
    public function set_thumbnail(?string $path): void {
        $this->thumbnail = $path;
    }

    private function set_routes(): void {
        if (!file_exists($this->absolute_path_thumbnail)) {
            mkdir($this->absolute_path_thumbnail, 0775, true);
        }
    }
}
