<?php

namespace DLRoute\Traits;

use DLRoute\Config\FileInfo;
use DLRoute\Requests\DLOutput;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;


trait ImageTrait {

    use Path;

    /**
     * Anchura predeterminada
     *
     * @var integer $preview_width
     */
    public int $preview_width = 500;

    /**
     * Verifica si el archivo es formato SVG
     *
     * @param string $mime_type Tipo MIME del gráfico vectorial.
     * @return boolean
     */
    public function is_svg(string $mime_type): bool {
        return $mime_type === 'image/svg+xml';
    }

    /**
     * Verifica si el archivo es una foto JPEG.
     *
     * @param string $mime_type Tipo MIME de la foto.
     * @return boolean
     */
    public function is_jpeg(string $mime_type): bool {
        return $mime_type === "image/jpeg";
    }

    /**
     * Verifica si el archivo es una imagen PNG
     *
     * @param string $mime_type Tipo MIME de la imagen PNG.
     * @return boolean
     */
    public function is_png(string $mime_type): bool {
        return $mime_type === "image/png";
    }

    /**
     * Verifica si el archivo es una imagen GIF
     *
     * @param string $mime_type Tipo MIME del archivo
     * @return boolean
     */
    public function is_gif(string $mime_type): bool {
        return $mime_type === "image/gif";
    }

    /**
     * Verifica si el archivo es una imagen `BMP`.
     *
     * @param string $mime_type
     * @return boolean
     */
    public function is_bitmap(string $mime_type): bool {
        return $mime_type === "image/bmp" || $mime_type === "image/x-ms-bmp";
    }

    /**
     * Determina si la imagen enviada es un formato Webp
     *
     * @param string $mime_type Tipo MIME de archivo.
     * @return boolean
     */
    public function is_webp(string $mime_type): bool {

        $mime_type = trim($mime_type);
        $mime_type = strtolower($mime_type);

        /**
         * Tipos mimes disponibles en WebP
         * 
         * @var string[] $mime_types
         */
        $mime_types = [
            'image/webp',
            'image/x-webp',
            'image/vnd.google.webp',
            'image/webp-image',
            'image/x-webp-image',
            'image/x-google-webp',
            'image/google-webp',
            'image/vnd.webp'
        ];

        return in_array($mime_type, $mime_types);
    }

    /**
     * Indica si se trata de una imagen a partir del tipo enviado como 
     * argumento
     *
     * @param string $type Tipo MIME
     * @return boolean
     */
    public function is_bitmap_image(string $type) {

        return $this->is_png($type) ||
            $this->is_jpeg($type) ||
            $this->is_bitmap($type) ||
            $this->is_gif($type) ||
            $this->is_webp($type);
    }

    /**
     * Convierte cualquier imagena formato WebP
     *
     * @param string $filename Archivo a ser procesado
     * @param integer|null $preview_width Vista previa del archivo
     * @return string|null
     */
    public function to_webp(string $filename, int $preview_width = null): ?string {

        $filename = $this->get_path($filename);

        /**
         * Patrón de búsqueda del directorio de la imagen
         * 
         * @var string $dir_pattern
         */
        $dir_pattern = "(.*)\/[0-9]{4}\/[0-9]{2}\/+";

        /**
         * Patrón de directorio para Windows
         * 
         * @var string $dir_pattern_win
         */
        $dir_pattern_win = "(.*)\\\[0-9]{4}\\\[0-9]{2}\\\+";

        /**
         * Directorio de la vista previa
         * 
         * @var string $thumbnail
         */
        $thumbnail = "";

        /**
         * Capturar el directorio base
         * 
         * @var string $basedir
         */
        $basedir = "";

        if (!is_null($filename)) {

            /**
             * Indica si se ha encontrado un patrón o no
             * 
             * @var int|false $found
             */
            $found = preg_match("/{$dir_pattern}|{$dir_pattern_win}/", $filename, $matches);

            if ($found) {
                $basedir = $matches[0];
                $thumbnail = "{$basedir}thumbnail/";
            }
        }

        /**
         * Nueva ruta en el caso de que aplique
         * 
         * @var string $new_filename
         */
        $new_filename = $filename;

        if (!file_exists($filename)) {
            $new_filename = $this->get_absolute_path($filename);
        }

        $new_filename = $this->get_path($new_filename);

        if (!(file_exists($new_filename)) || !($this->is_image($new_filename))) {
            return null;
        }

        /**
         * Devuelve las dimensiones de la imagen
         * 
         * @var array|false $size
         */
        $size = getimagesize($new_filename);

        if (!((bool) $size)) {
            header("content-type: application/json; charset=utf-8", 400);

            DLOutput::get_json([
                "status" => false,
                "error" => "formato de imagen inválido"
            ]);

            exit;
        }

        /**
         * Anchura de la imagen
         * 
         * @var integer $width
         */
        $width = (int) ($size[0] ?? 0);

        /**
         * Altura de la imagen
         * 
         * @var integer $height
         */
        $height = (int) ($size[1] ?? 0);

        /**
         * Relación de aspecto
         * 
         * @var double $aspect_radio
         */
        $aspect_radio = $height / $width;

        if ($width > 1920) {
            $width = 1920;
        }

        /**
         * Nueva anchura
         * 
         * @var integer $new_width
         */
        $new_width = is_null($preview_width) ? $width : $preview_width;

        /**
         * @var integer|float $new_height
         */
        $new_height = $new_width * $aspect_radio;

        /**
         * Encuentra la extensión del archivo
         * 
         * @var string $pattern
         */
        $pattern = "/\.[^.]+$/";

        /**
         * Ruta donde se desea guardar la imagen convertida en WebP
         * 
         * @var string $output
         */
        $output = trim($new_filename);
        $output = preg_replace($pattern, '', $output);
        $output = "{$output}.webp";
        $output_relative = preg_replace($pattern, '', $filename);
        $output_relative = "{$output_relative}.webp";

        if (!file_exists($output)) {
            header("content-type: application/json; charset=utf-8", 404);

            echo DLOutput::get_json([
                "status" => false,
                "error" => "El archivo no existe {$output}"
            ]);

            exit;
        }

        /**
         * @var Imagine $imagine
         */
        $imagine = new Imagine();

        $image = $imagine->open($new_filename);

        $image->resize(new Box($new_width, $new_height));

        if (!is_null($preview_width)) {
            $output = str_replace($basedir, $thumbnail, $output);
            $output_relative = str_replace($basedir, $thumbnail, $output_relative);
        }

        $image->save($output, [
            'format' => 'webp',
            'quality' => 100,
        ]);


        return $output_relative;
    }

    /**
     * Establece la anchura de la imagen que se usará de vista previa
     *
     * @param integer $width
     * @return void
     */
    public function set_preview_width(int $width): void {
        $this->preview_width = $width;
    }

    /**
     * Verifica si el archivo es una imagen
     *
     * @param string $filename Archivo a ser analizado
     * @return boolean
     */
    public function is_image(string $filename): bool {
        if (!file_exists($filename)) {
            return false;
        }

        return FileInfo::is_image($filename);
    }
}
