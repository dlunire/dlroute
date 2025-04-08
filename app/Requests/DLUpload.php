<?php

namespace DLRoute\Requests;

use DLRoute\Config\FileInfo;
use DLRoute\Routes\RouteDebugger;
use DLRoute\Server\DLServer;
use DLRoute\Traits\ImageTrait;
use finfo;
use GdImage;

/**
 * MIT License
 * 
 * Copyright (c) 2023 David E Luna M
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * 
 * Permite procesar la subida de archivos al servidor.
 * 
 * @package DLRoute\Requests
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
trait DLUpload {

    use ImageTrait;

    /**
     * Nombres de archivos
     *
     * @var array
     */
    private array $filenames = [];

    /**
     * Directorio base a establecer para la subida de archivos.
     *
     * @var string
     */
    private string $basedir = "";

    /**
     * Anchura predeterminada del thumbnail.
     *
     * @var integer
     */
    private int $thumbnail_width = 300;

    /**
     * Sube los archivos al servidor.
     *
     * @param string $field Campo del formulario.
     * @param string $type Indica el tipo de archivo a permitir en el servidor
     * @return array<Filename>
     */
    public function upload_file(string $field, string $type = "*/*"): array {
        $field = trim($field);

        $this->load_filenames($field);

        /**
         * Archivos cargados por el usuario.
         * 
         * @var array
         */
        $filenames = $this->get_filenames();

        $filenames = $this->filter_by_type($filenames, $type);

        $this->move_uploaded($filenames);

        return $filenames;
    }

    /**
     * Devuelve los archivos enviados por el usuario
     *
     * @return array
     */
    public function get_filenames(): array {
        return $this->filenames;
    }

    /**
     * Permite establecer un directorio base dónde guardar los archivos.
     *
     * @param string $basedir Directorio base a establecer.
     * @return void
     */
    public function set_basedir(string $basedir): void {
        $this->basedir = RouteDebugger::clear_route($basedir);
    }

    /**
     * Permite establecer una anchura personalizada a los
     * `thumbnails` que se generarán.
     *
     * @param integer $width Anchura de los `thumbnails`.
     * @return void
     */
    public function set_thumbnail_width(int $width): void {
        $this->thumbnail_width = $width;
    }

    /**
     * Filtra los archivos por tipo.
     *
     * @param array $filenames
     * @param string $mime_type
     * @return array
     */
    private function filter_by_type(array $filenames, string $mime_type): array {
        /**
         * Partes del tipo de archivos en un array.
         * 
         * @var array
         */
        $parts = explode('/', $mime_type);

        /**
         * Categoría de tipo de archivos.
         * 
         * @var string
         */
        $category = "";

        /**
         * Subcategoría de tipo de arhcivos.
         * 
         * @var string
         */
        $subcategory = "";

        if (array_key_exists(0, $parts)) {
            $category = trim($parts[0]);
        }

        if (array_key_exists(1, $parts)) {
            $subcategory = trim($parts[1]);
        }

        if ($category === "*") {
            $category = "(.*?)";
        }

        if ($subcategory === "*") {
            $subcategory = "(.*?)";
        }

        /**
         * Patrón de búsqueda de tipos de archivos.
         * 
         * @var string
         */
        $pattern = "/^{$category}\/{$subcategory}$/i";

        /**
         * Archivos filtrados por tipo.
         * 
         * @var array
         */
        $filtered_filenames = array_filter($filenames, function (Filename $filename) use ($pattern) {
            /**
             * Tipo de archivo.
             * 
             * @var string
             */
            $type = $filename->type;

            return preg_match($pattern, $type);
        });

        return array_values($filtered_filenames);
    }

    /**
     * Carga los archivos del usuario en un array de arrays asociativos o de ojetos.
     *
     * @return void
     */
    private function load_filenames(string $field_name): void {

        if (!array_key_exists($field_name, $_FILES)) {
            header("Content-Type: application/json; charset=utf-8", true, 400);

            echo DLOutput::get_json([
                "status" => false,
                "error" => "Revise el nombre de campo del formulario de archivo o tamaño de archivo"
            ], true);

            exit;
        }

        /**
         * Archivos enviados por el usuario.
         * 
         * @var array
         */
        $files = $_FILES[$field_name];

        /**
         * Nombre de archivo o archivos.
         * 
         * @var boolean
         */
        $is_multiple = is_array($files['name']);

        /**
         * Archivos del usuarios cargados en esta variable, pero como array
         * de objetos.
         * 
         * @var array
         */
        $filenames = $this->extract_filenames($files, $is_multiple);

        $this->filenames = $filenames;
    }

    /**
     * Extrae los nombres de archivos si `$_FILES['field']['name']` contienen uno o múltimples archivos.
     *
     * @param array $files Archivos a ser analizado y procesado.
     * @param boolean $is_multiple Indicar si es múltiple.
     * @return array<Filename>
     */
    private function extract_filenames(array $files, bool $is_multiple = true): array {

        /**
         * Directorio base de archivos.
         * 
         * @var string
         */
        $basedir = $this->get_basedir();

        /**
         * Nombres de archivos.
         * 
         * @var array
         */
        $filenames = [];

        if ($is_multiple) {
            foreach ($files['name'] as $key => $filename) {

                if (!is_string($filename)) {
                    continue;
                }

                /**
                 * Ruta completa del archivo.
                 * 
                 * @var string
                 */
                $full_path = (string) $files['full_path'][$key];

                /**
                 * Nombre temporal del archivo.
                 * 
                 * @var string
                 */
                $tmp_name = (string) $files['tmp_name'][$key];

                /**
                 * Indica si se produce un error durante la subida del archivo. Si
                 * error es `0`, entonces, ha subido exitosamente al directorio temporal `/tmp/`.
                 * 
                 * @var integer
                 */
                $error = (int) $files['error'][$key];

                /**
                 * Tamaño en bytes del archivo.
                 * 
                 * @var integer
                 */
                $size = (int) $files['size'][$key];

                /**
                 * Tipo MIME de archivo.
                 * 
                 * @var string
                 */
                $type = $this->get_mime_type($tmp_name);

                /**
                 * Formato de archivo.
                 * 
                 * @var string
                 */
                $format = $this->get_file_format($tmp_name);

                /**
                 * Tamaño legible del archivo. Se asignan unidades de tamaños.
                 * 
                 * @var string $readable_size
                 */
                $readable_size = $this->get_readable_size((int) $size);

                /**
                 * Nombre formateado del archivo enviado al servidor
                 * 
                 * @var string $name
                 */
                $name = $this->slug($filename, $type, ['file' => $tmp_name, 'type' => $files['type'][$key] ?? '']);

                if ($this->is_bitmap_image($type)) {
                    $name = $this->replace_to_webp($name);
                }

                /**
                 * Datos del archivo enviados al servidor
                 * 
                 * @var Filename $filename
                 */
                $filename = new Filename([
                    "name" => $name,
                    "tmp_name" => $tmp_name,
                    "full_path" => $full_path,
                    "type" => $type,
                    "file_format" => $format,
                    "size" => $size,
                    "readable_size" => $readable_size,
                    "error" => $error,
                    "basedir" =>  $this->basedir,
                    "relative_path" => $this->get_relative_basedir(),
                    "relative_path_thumbnail" => $this->get_relative_basedir() . "/thumbnail",
                ]);

                $filenames[] = $filename;
            }

            return $filenames;
        }

        /**
         * Nombre del archivo.
         * 
         * @var string
         */
        $name = $files['name'];

        /**
         * Nombre temporal del archivo.
         * 
         * @var string
         */
        $tmp_name = (string) $files['tmp_name'];

        /**
         * Ruta completa del archivo.
         * 
         * @var string
         */
        $full_path = (string) $files['full_path'];

        /**
         * Tipo de MIME del archivo.
         * 
         * @var string
         */
        $type = $this->get_mime_type($tmp_name);

        /**
         * Formato de archivo.
         * 
         * @var string
         */
        $file_format = $this->get_file_format($tmp_name);

        /**
         * Tamaño en bytes del archivo.
         * 
         * @var integer
         */
        $size = (int) $files['size'];

        /**
         * Tamaño legible del archivo.
         * 
         * @var string
         */
        $readable_size = $this->get_readable_size($size);

        /**
         * Indicador de errores de archivos. Si vale `0`, entonces, se envió exitosamente
         * al servidor.
         * 
         * @var integer.
         */
        $error = (int) $files['error'];

        $name = $this->slug($name, $type, ['file' => $tmp_name, 'type' => $files['type'] ?? '']);

        if ($this->is_bitmap_image($type)) {
            $name = $this->replace_to_webp($name);
        }

        /**
         * Datos del archivo enviados al servidor
         * 
         * @var Filename $filename
         */
        $filename = new Filename([
            "name" => $name,
            "tmp_name" => $tmp_name,
            "full_path" => $full_path,
            "type" => $type,
            "file_format" => $file_format,
            "size" => $size,
            "readable_size" => $readable_size,
            "error" => $error,
            "basedir" =>  $this->basedir,
            "target" => "{$basedir}/{$name}",
            "relative_path" => $this->get_relative_basedir(),
            "relative_path_thumbnail" => $this->get_relative_basedir() . "/thumbnail",
        ]);

        $filenames[] = $filename;

        return $filenames;
    }

    /**
     * Reemplaza cualquier extensión de archivo a `webp`
     *
     * @param string $input Entrada a ser analizada.
     * @return string
     */
    private function replace_to_webp(string $input): string {
        $input = trim($input, "\.");
        $input = preg_replace('/([^.]+)$/', 'webp', $input);

        return trim($input);
    }

    /**
     * Devuelve un tamaño legible de datos
     *
     * @param integer $size Tamaño en bytes
     * @return string
     */
    private function get_readable_size(int $size): string {
        /**
         * Tamaño legible de datos.
         * 
         * @var string
         */
        $readable_size = "";

        /**
         * Números formateados.
         * 
         * @var string
         */
        $formatted_number = "";

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} KB";
        }

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} MB";
        }

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} GB";
        }

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} TB";
        }

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} PB";
        }

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} EB";
        }

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} ZB";
        }

        if ($size > 1024) {
            $size /= 1024;

            $formatted_number = number_format($size, 2);
            $readable_size = "{$formatted_number} YB";
        }

        return $readable_size;
    }

    /**
     * Devuelve el tipo MIME del archivo analizado.
     *
     * @param string $filename Archivo a ser analizado.
     * @return string
     */
    private function get_mime_type(string $filename): string {
        /**
         * Tipo MIME del archivo analizado.
         * 
         * @var string
         */
        $mime_type = "";

        if (!file_exists($filename)) {
            return $mime_type;
        }

        $mime_type = mime_content_type($filename);

        return $mime_type;
    }

    /**
     * Devuelve el formato de archivo
     *
     * @param string $filename Archivo a ser analizado
     * @return string
     */
    private function get_file_format(string $filename): string {
        /**
         * Formato de archivos.
         * 
         * @var finfo
         */
        $finfo = new finfo();

        $filename = trim($filename);

        if (is_null($filename) || empty($filename)) {
            header("Content-Type: application/json; charset=utf-8", true, 500);

            echo DLOutput::get_json([
                "status" => false,
                "error" => "Posiblemente, deba configurar el servidor para aceptar archivos más grandes"
            ], true);

            exit;
        }

        return $finfo->file($filename);
    }

    /**
     * Establece un nombre único para el archivo cargado al servidor en función de su
     * fecha y contenido.
     *
     * @param string $filename Nombre de archivo a ser procesado.
     * @param string $mime_type
     * @param array $options Opciones de archivo
     * @return string
     */
    private function slug(string $filename, string $mime_type, array $options): string {
        /**
         * Nombre de archivos.
         * 
         * @var string
         */
        $file = "";

        /**
         * Tipo de archivos.
         * 
         * @var string
         */
        $type = "";

        if (array_key_exists('file', $options)) {
            $file = $options['file'];
        }

        if (array_key_exists('type', $options)) {
            $type = $options['type'];
        }

        if ($this->is_svg($mime_type)) {
            /**
             * Contenido a ser analizado y depurado.
             * 
             * @var string
             */
            $content = file_get_contents($file);
            $content = $this->sanitize_svg($content);

            file_put_contents($file, $content);
        }

        /**
         * Patrón de búsqueda de la extensión.
         * 
         * @var string
         */
        $extension_pattern = "/((!.*)?[^.*]+)$/";

        $filename = preg_replace("/\s+/", '-', $filename);
        $filename = strtolower($filename);

        /**
         * Indica existe o no alguna extensión.
         * 
         * @var boolean
         */
        $found = preg_match($extension_pattern, $filename, $matches);

        /**
         * Extensión de archivo.
         * 
         * @var string
         */
        $extension = "";

        if ($found) {
            $extension = trim($matches[0] ?? '');
        }

        /**
         * Partes de un tipo MIME de archivo.
         * 
         * @var string[]
         */
        $parts = explode("/", $mime_type);

        /**
         * Subcategoría.
         * 
         * @var string
         */
        $subcategory = "";

        if (array_key_exists(1, $parts)) {
            $subcategory = $parts[1];
        }

        $filename = preg_replace($extension_pattern, '', $filename);
        $filename = rtrim($filename, ".");
        $filename = trim($filename);

        /**
         * Hash como identificador en función de su nombre
         * 
         * @var string
         */
        $hash = "";

        if (file_exists($file)) {
            // $hash .= "-" . hash_file('fnv132', $file);
            $hash .= "-" . hash_file('sha256', $file);
        }

        /**
         * Indica si está o estuvo vacía `$filename`
         * 
         * @var boolean
         */
        $is_empty = empty($filename);

        if ($is_empty) {
            $filename = "{$extension}-{$hash}.{$subcategory}";
        } else {
            $filename = "{$filename}-{$hash}";
        }

        if (!empty($extension) && !$is_empty) {
            $filename .= ".{$extension}";
        }

        if ($mime_type === 'image/x-ms-bmp' || $mime_type === "image/bmp") {
            $mime_type = $type;
        }

        if ($type !== $mime_type) {
            $filename = str_replace(".{$extension}", '', $filename);
            $filename .= ".{$subcategory}";
        }

        $filename = preg_replace("/-+/", "-", $filename);

        return $filename;
    }

    /**
     * Devuelve el directorio base de donde se subirán los archivos.
     *
     * @return string
     */
    private function get_basedir(): string {
        /**
         * Directorio raíz de la aplicación.
         * 
         * @var string
         */
        $root = DLServer::get_document_root();

        /**
         * Directorio base para subir archivos.
         * 
         * @var string
         */
        $basedir = "{$root}/{$this->basedir}";
        $basedir = RouteDebugger::clear_route($basedir);

        /**
         * Año actual del servidor.
         * 
         * @var string
         */
        $year = date('Y');

        /**
         * Mes actual del servidor.
         * 
         * @var string
         */
        $month = date('m');

        $basedir = "/{$basedir}/{$year}/{$month}";

        if (!file_exists($basedir)) {
            mkdir($basedir, 0755, true);
        }

        if (!is_dir($basedir)) {
            $this->error('La ruta especificada no es un directorio. Considere otro nombre');
        }

        if (!is_readable($basedir)) {
            $this->error("No tienes permiso de lectura. Contacte con el administrador para cambiar los permisos de lectura");
        }

        if (!is_writable($basedir)) {
            $this->error("No tienes permiso de escritura. Contacte con el administrador del servidor");
        }

        return $basedir;
    }

    /**
     * Ayuda a establecer mensajes de error.
     *
     * @param string $message Mensaje personalizado de error.
     * @return void
     */
    private function error(string $message): void {
        header("Content-Type: application/json; charset=utf-9", true, 500);

        echo DLOutput::get_json([
            "status" => false,
            "error" => trim($message)
        ], true);

        exit;
    }

    /**
     * Mueve los archivos previamente subidos en `/tmp/` al directorio de
     * archivos subidos de la aplicación.
     *
     * @param array<Filename> $filenames
     * @return void
     */
    private function move_uploaded(array &$filenames): void {

        foreach ($filenames as &$file) {

            if (!($file instanceof Filename)) {
                continue;
            }

            /**
             * Ruta absoluta de archivo
             * 
             * @var string $absolute_file
             */
            $absolute_file = $file->get_absolute_path($file->target_file);

            move_uploaded_file($file->tmp_name, $absolute_file);

            if (!file_exists($absolute_file) || !(FileInfo::is_image($absolute_file))) {
                $file->set_thumbnail(null);
                continue;
            }

            if (!$this->is_bitmap_image($file->type)) {
                continue;
            }

            /**
             * Vista previa de la imagen
             * 
             * @var string $preview
             */
            $preview = $this->to_webp($file->target_file, $this->preview_width);

            $file->set_thumbnail($preview);

            /**
             * Imagen en formato WebP
             * 
             * @var string | null $image_file
             */
            $image_file = $this->format_image($file->target_file, $file->type);

            if (is_null($image_file)) {
                continue;
            }

            $file->target_file = $image_file;
        }
    }

    /**
     * Sanea el código SVG para evitar la ejecución de código JavaScript no deseada.
     *
     * @param string $content
     * @return string
     */
    private function sanitize_svg(string $content): string {
        /**
         * Patrón de búsqueda de bloque de código JavaScript.
         * 
         * @var string
         */
        $script_block_pattern = '/<script(.*?)>[\s\S]+?<\/script(.*?)>/i';

        /**
         * Patrón de búsqueda de etiquetas sobrantes de JavaScript.
         * 
         * @var string
         */
        $script_pattern = '/<script(.*?)>[\s\S]+/i';

        /**
         * Patrón de búsqueda de eventos de JavaScript.
         * 
         * @var string
         */
        $js_events_pattern = '/(\b((?<!-)on\w+=\"?(.*)\"?)|\b((?<!-)on\w+=\'?(.*)\'?))/i';

        /**
         * Patrón de búsqueda de atributos.
         * 
         * @var string
         */
        $attributes_pattern = '/(?<!xlink:)(eval\((.*)(\)|\"|\')?|href=[\S]*(\"|\'))/i';

        /**
         * Patrón de búsqueda de atributos `data-*` incompletos.
         * 
         * @var string
         */
        $data_attributes_pattern = '/\b(data-)(?=\s+)/i';

        /**
         * Patrón de búsqueda de bloques de estilos.
         * 
         * @var string
         */
        $style_block_pattern = '/<style(.*?)>[\s\S]+?<\/style(.*?)>/i';

        /**
         * Patrón de búsqueda de etiquetas de estilos sobranes.
         * 
         * @var string
         */
        $style_pattern = '/<style(.*?)>[\s\S]+/i';

        /**
         * Patrón de búsqueda de bloques PHP.
         * 
         * @var string
         */
        $php_block_pattern = '/<\?(php)?[\s\S]*?\?>/i';

        /**
         * Contenido a ser depurado.
         * 
         * @var string
         */
        $content = trim($content);

        /**
         * Patrón de búsqueda de cabeceras XML.
         * 
         * @var string
         */
        $xml_pattern = '/<\?xml version=("|\')[0-9]+\.*[0-9]*("|\') encoding=("|\')(.*?)("|\') standalone="(.*?)"\?>/i';

        /**
         * Patrón de búsqueda de fragmentos PHP sobrantes.
         * 
         * @var string
         */
        $php_pattern = '/(<\?(.*)?|\?>)|(<\%)|(%>)|(\{+)|(\}+)/i';

        /**
         * Si por alguna razón se cuela algúna instrucción de un archivo ASP
         * o similar.
         * 
         * @var string $pattern
         */
        $pattern = "/<\%(.*)?/i";

        /**
         * Remover atributos src y srcset
         * 
         * @var string $attributes
         */
        $attributes = '/(src|srcset)\=(\"|\')[\s\S]*?(\"|\')/i';

        /**
         * Indicador de existencia de cabeceras XML en el archivo SVG.
         * 
         * @var boolean
         */
        $xml_exists = preg_match($xml_pattern, $content);

        $content = preg_replace($script_block_pattern, '', $content);
        $content = preg_replace($script_pattern, '', $content);
        $content = preg_replace($style_block_pattern, '', $content);
        $content = preg_replace($style_pattern, '', $content);
        $content = preg_replace($php_block_pattern, '', $content);
        $content = preg_replace($php_pattern, '', $content);
        $content = preg_replace($js_events_pattern, '', $content);
        $content = preg_replace($attributes_pattern, '', $content);
        $content = preg_replace($data_attributes_pattern, '', $content);
        $content = preg_replace($attributes, '', $content);
        $content = preg_replace($pattern, '', $content);

        $content = trim($content);

        if ($xml_exists) {
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
            $content = "{$xml}\n{$content}";
        }

        return $content;
    }

    /**
     * Cambia el formato de la imagen a formato WebP de forma automática.
     *
     * @param string $filename Archivo a ser analizado y procesado.
     * @param string $mime_type Indica el tipo a ser analizado.
     * @return string|null
     */
    private function format_image(string $filename, string $mime_type): string|null {

        if (!file_exists($filename)) {
            return null;
        }

        $mime_type = trim($mime_type);

        /**
         * Patrón de búsqueda de formatos de imágenes.
         * 
         * @var  string $pattern
         */
        $pattern = "/^image\/(.*?)$/i";

        /**
         * Valida si el archivo es una imagen.
         * 
         * @var boolean
         */
        $is_image = preg_match($pattern, $mime_type);

        if (!$is_image) {
            return null;
        }

        /**
         * Información de la imagen.
         * 
         * @var array|false
         */
        $info = getimagesize($filename);

        if ($info === FALSE) {
            return null;
        }

        /**
         * Comprueba si las proporciones están disponibles.
         * 
         * @var boolean
         */
        $is_available = array_key_exists(0, $info) && array_key_exists(1, $info);

        if (!$is_available) {
            return null;
        }

        if (!class_exists('GdImage')) {
            return null;
        }

        /**
         * Anchura original de la imagen.
         * 
         * @var integer
         */
        $width = (int) $info[0];

        /**
         * Altura original de la imagen.
         * 
         * @var integer
         */
        $height = (int) $info[1];

        /**
         * Directorio base donde se almacenarán las miniaturas.
         * 
         * @var string
         */
        $dir = dirname($filename);

        if (file_exists($dir) && !is_dir($dir)) {
            unlink($dir);
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        /**
         * Imagen creada a partir de un fichero o ruta.
         * 
         * @var GdImage|resource|false
         */
        $image = false;

        if ($this->is_jpeg($mime_type)) {
            $image = imagecreatefromjpeg($filename);
        }

        if ($this->is_png($mime_type)) {
            $image = imagecreatefrompng($filename);
        }

        if ($this->is_gif($mime_type)) {
            $image = imagecreatefromgif($filename);
        }

        if ($this->is_bitmap($mime_type)) {
            $image = @imagecreatefrombmp($filename);
        }

        if ($this->is_webp($mime_type)) {
            $image = imagecreatefromwebp($filename);
        }

        /**
         * Indica si `$image` es un recurso o no.
         * 
         * @var boolean
         */
        $is_resource = is_resource($image) || ($image instanceof GdImage);

        if (!$is_resource) {
            return null;
        }

        /**
         * Nueva imagen creada a partir de la original con otras dimensiones.
         * 
         * @var GdImage|resource|false
         */
        $new_image = imagecreatetruecolor($width, $height);

        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);

        $is_resource = is_resource($new_image) || ($new_image instanceof GdImage);

        if (!$is_resource) {
            return null;
        }

        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        /**
         * Ruta completa del thumbnail.
         * 
         * @var string
         */
        $file = preg_replace('/\.(.*?)$/i', '', $filename);
        $file .= ".webp";

        /**
         * Indica si se ha creado la imagen en la ruta indicada.
         * 
         * @var boolean
         */
        $it_created = @imagewebp($new_image, $file);

        if ($it_created) {
            imagedestroy($image);
            imagedestroy($new_image);
        }

        return $it_created ? $file : $filename;
    }
    /**
     * Cambia el tamaño de las imágenes y devuelve la ruta de la vista previa, 
     * caso contrario, devuelve `NULL`.
     *
     * @param string $filename Archivo a ser analizado y procesado.
     * @param string $mime_type Indica el tipo a ser analizado.
     * @return string|null
     */
    private function resize_image(string $filename, string $mime_type): string|null {

        if (!file_exists($filename)) {
            return null;
        }

        $mime_type = trim($mime_type);
        $mime_type = strtolower($mime_type);

        /**
         * Patrón de búsqueda de formatos de imágenes.
         * 
         * @var  string
         */
        $pattern = "/^image\/(.*?)$/i";

        /**
         * Valida si el archivo es una imagen.
         * 
         * @var boolean
         */
        $is_image = preg_match($pattern, $mime_type);

        if (!$is_image) {
            return null;
        }

        /**
         * Información de la imagen.
         * 
         * @var array|false
         */
        $info = getimagesize($filename);

        if ($info === FALSE) {
            return null;
        }

        /**
         * Comprueba si las proporciones están disponibles.
         * 
         * @var boolean
         */
        $is_available = array_key_exists(0, $info) && array_key_exists(1, $info);

        if (!$is_available) {
            return null;
        }

        if (!class_exists('GdImage')) {
            http_response_code(500);
            echo DLOutput::get_json([
                "status" => false,
                "error" => "Instale la extensión GdImage"
            ]);
            exit;
        }

        /**
         * Anchura original de la imagen.
         * 
         * @var integer
         */
        $width = (int) $info[0];

        /**
         * Altura original de la imagen.
         * 
         * @var integer
         */
        $height = (int) $info[1];

        /**
         * Nueva anchura establecida para miniaturas.
         * 
         * @var integer
         */
        $thumbnail_width = $this->thumbnail_width;

        /**
         * Se establece la altura automáticamente en función de la anchura
         * original del archivo.
         * 
         * @var float
         */
        $thumbnail_height = (float) $thumbnail_width / $width * $height;

        /**
         * Directorio base donde se almacenarán las miniaturas.
         * 
         * @var string
         */
        $dir = dirname($filename) . "/thumbnail";

        if (file_exists($dir) && !is_dir($dir)) {
            unlink($dir);
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        /**
         * Imagen creada a partir de un fichero o ruta.
         * 
         * @var GdImage|resource|false
         */
        $image = false;

        if ($this->is_jpeg($mime_type)) {
            $image = imagecreatefromjpeg($filename);
        }

        if ($this->is_png($mime_type)) {
            $image = imagecreatefrompng($filename);
        }

        if ($this->is_gif($mime_type)) {
            $image = imagecreatefromgif($filename);
        }

        if ($this->is_bitmap($mime_type)) {
            $image = @imagecreatefrombmp($filename);
        }

        if ($this->is_webp($mime_type)) {
            $image = imagecreatefromwebp($filename);
        }

        /**
         * Indica si `$image` es un recurso o no.
         * 
         * @var boolean
         */
        $is_resource = is_resource($image) || ($image instanceof GdImage);

        if (!$is_resource) {
            return null;
        }

        /**
         * Nueva imagen creada a partir de la original con otras dimensiones.
         * 
         * @var GdImage|resource|false
         */
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $thumbnail_width, $thumbnail_height, $transparent);

        $is_resource = is_resource($new_image) || ($new_image instanceof GdImage);

        if (!$is_resource) {
            return null;
        }

        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, imagesx($image), imagesy($image));

        /**
         * Ruta completa del thumbnail.
         * 
         * @var string
         */
        $thumbnail_file = preg_replace('/\b[0-9]{2}\//', "$0thumbnail/", $filename);
        $thumbnail_file = preg_replace('/\.(.*?)$/i', '', $thumbnail_file);
        $thumbnail_file .= ".webp";

        /**
         * Indica si se ha creado la imagen en la ruta indicada.
         * 
         * @var boolean
         */
        $it_created = @imagewebp($new_image, $thumbnail_file);

        if ($it_created) {
            imagedestroy($image);
            imagedestroy($new_image);
        }

        return $it_created ? $thumbnail_file : null;
    }

    /**
     * Devuelve la ruta de directorio de archivos por fecha de ejecución
     * del servidor.
     *
     * @return string
     */
    private function get_dir_by_date(): string {
        /**
         * Año del actual del servidor de ejecución.
         * 
         * @var string
         */
        $year = date('Y');

        /**
         * Mes actual de ejecución del servidor.
         * 
         * @var string
         */
        $month = date('m');

        return "{$year}/{$month}";
    }

    /**
     * Devuelve la ruta relativa de los archivos.
     *
     * @return string
     */
    private function get_relative_basedir(): string {
        /**
         * Devuelve la ruta de directorio en función de la fecha.
         * 
         * @var string
         */
        $dir_by_date = $this->get_dir_by_date();

        /**
         * Ruta relativa del directorio de archivo
         * 
         * @var string $relative_path
         */
        $relative_path = "{$this->basedir}/{$dir_by_date}";

        return RouteDebugger::trim_slash($relative_path);
    }

    /**
     * Devuelve la ruta absoluta del archivo
     *
     * @return string
     */
    public function get_absolute_path(string $relative_path): string {

        /**
         * Directorio raíz de la aplicación
         * 
         * @var string $root
         */
        $root = DLServer::get_document_root();

        /**
         * Ruta absoluta de la aplicación
         * 
         * @var string $path
         */
        $path = "{$root}/{$relative_path}";

        /**
         * Ruta física del archivo
         * 
         * @var string $absolute_path
         */
        $absolute_path = $this->get_path($path);

        $absolute_path = rtrim($absolute_path, "\/\\");

        return $absolute_path;
    }
}
