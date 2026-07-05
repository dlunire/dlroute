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

/**
 * Analiza un archivo y devuelve información relativa a éste.
 * 
 * @package DLRoute\Interface
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2026 David E Luna M
 * @license AGPL-3.0 license
 */
interface FileInfoInterface {
    /**
     * Obtener el tipo MIME del archivo.
     *
     * @param string $filename Archivo a analizar.
     * @return string
     */
    public static function get_type(string $filename): string;

    /**
     * Devuelve el número de canales del archivo.
     *
     * @param string $filename Archivo a ser analizado.
     * @return integer
     */
    public static function get_channels(string $filename): int;

    /**
     * Devuelve el número de bits
     *
     * @param string $filename Archivo a ser analizado.
     * @return integer
     */
    public static function get_bits(string $filename): int;

    /**
     * Devuelve las dimensiones del archivo si es una imagen.
     *
     * @param string $filename
     * @return object
     */
    public static function get_dimensions(string $filename): object;

    /**
     * Devuelve el tamaño en bytes del archivo.
     *
     * @param string $filename Archivo a ser analizado.
     * @return integer
     */
    public static function get_size(string $filename): int;

    /**
     * Devuelve el tamaño del archivo en un formato legible.
     *
     * @param string $filename Archivo a ser analizado.
     * @return string
     */
    public static function get_format_size(string $filename): string;

    /**
     * Devuelve información del archivo.
     *
     * @param string $filename Archivo a ser analizado.
     * @return object
     */
    public static function get_info(string $filename): object;

    /**
     * Indica si el archivo es una imagen.
     *
     * @param string $filename Archivo a ser analizado.
     * @return boolean
     */
    public static function is_image(string $filename): bool;

    /**
     * Indica si el archivo es un PDF.
     *
     * @param string $filename Archivo a ser analizado.
     * @return boolean
     */
    public static function is_pdf(string $filename): bool;

    /**
     * Indica si el archivo es texto plano.
     *
     * @param string $filename Archivo a ser analizado.
     * @return boolean
     */
    public static function is_text_plain(string $filename): bool;
}
