<?php

namespace DLRoute\Config;

use DLRoute\Interfaces\FileInfoInterface;

/**
 * Analiza un archivo y devuelve información relativa a éste.
 * 
 * @package DLRoute\Config
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
class FileInfo implements FileInfoInterface {

    public const TYPE = [
        "image/png",
        "image/jpeg",
        "image/gif",
        "image/bmp",
        'image/tiff',
        'image/webp',
        'image/avif',
        'image/vnd.microsoft.icon',
        'image/svg\+xml',
        'image/svg+xml',
    ];

    /**
     * Comprueba si es una imagen.
     *
     * @param string $filename
     * @return boolean
     */
    public static function is_image(string $filename): bool {
        $pattern = "/^image\/(.*?)$/i";


        /**
         * Tipo de archivo.
         * 
         * @var object
         */
        $type = self::get_type($filename);

        if (!in_array($type, self::TYPE)) {
            return false;
        }

        return true;
    }

    public static function get_type(string $filename): string {
        $info = self::get_info($filename);
        return trim($info->mime ?? '');
    }

    public static function is_pdf(string $filename): bool {
        /**
         * Tipo de archivo.
         * 
         * @var string
         */
        $type = self::get_type($filename);
        $type = trim($type);

        return "application/pdf" === $type;
    }

    public static function is_text_plain(string $filename): bool {
        /**
         * Tipo de archivo.
         * 
         * @var string
         */
        $type = self::get_type($filename);
        $type = trim($type);

        return $type === 'text/plain';
    }

    public static function get_bits(string $filename): int {
        /**
         * Información del archivo.
         * 
         * @var object
         */
        $info = self::get_info($filename);

        /**
         * Bits del archivo.
         * 
         * @var int
         */
        $bits = $info->bits ?? 0;

        return $bits;
    }

    public static function get_dimensions(string $filename): object {
        /**
         * Devuelve las dimensiones del archivo.
         * 
         * @var object
         */
        $info = self::get_info($filename);

        /**
         * Dimensiones del archivo.
         * 
         * @var object
         */
        $dimensions = (object) [
            "width" => $info->width,
            "height" => $info->height
        ];

        return $dimensions;
    }

    public static function get_channels(string $filename): int {
        /**
         * Información del archivo.
         * 
         * @var object
         */
        $info = self::get_info($filename);

        /**
         * Canales del archivo.
         * 
         * @var int
         */
        $channels = $info->channels;

        return $channels;
    }

    public static function get_size(string $filename): int {
        /**
         * Información del archivo.
         * 
         * @var object
         */
        $info = self::get_info($filename);

        /**
         * Tamaño en bytes del archivo.
         * 
         * @var int
         */
        $size = $info->size ?? 0;

        return $size;
    }
    public static function get_format_size(string $filename): string {
        /**
         * Tamaño en bytes del archivo.
         * 
         * @var int
         */
        $size = self::get_size($filename);

        return self::get_format_size($size);
    }

    public static function get_info(string $filename): object {
        /**
         * Tipo de imágenes.
         * 
         * @var array|bool
         */
        $image_type = [];

        if (file_exists($filename) && !is_dir($filename)) {
            $image_type = getimagesize($filename);
        }

        /**
         * Tamaño en bytes.
         * 
         * @var int
         */
        $bytes = (int) filesize($filename) ?? 0;

        /**
         * Datos del archivo.
         * 
         * @var array|object
         */
        $data = [
            "mime" => $image_type['mime'] ?? mime_content_type($filename),
            "channels" => $image_type['channels'] ?? 0,
            "bits" => $image_type['bits'] ?? 0,
            "width" => $image_type['0'] ?? 0,
            "height" => $image_type['1'] ?? 0,
            "size" => (int) $bytes,
            "format_size" => (string) self::get_format_bytes($bytes)
        ];

        return (object) $data;
    }

    public static function get_format_bytes(int $bytes): string {
        $units = "{$bytes} B";


        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " KB";
        }

        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " MB";
        }

        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " GB";
        }

        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " TB";
        }

        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " PB";
        }

        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " EB";
        }

        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " ZB";
        }

        if ($bytes > 1024) {
            $bytes /= 1024;
            $units = number_format($bytes, 2) . " YB";
        }

        return trim($units);
    }
}
