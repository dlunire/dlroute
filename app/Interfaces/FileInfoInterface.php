<?php

namespace DLRoute\Interfaces;

/**
 * Analiza un archivo y devuelve información relativa a éste.
 * 
 * @package DLRoute\Interface
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
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
