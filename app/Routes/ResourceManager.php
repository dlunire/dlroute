<?php

namespace DLRoute\Routes;

use DLRoute\Config\DLRealPath;
use DLRoute\Config\FileInfo;
use DLRoute\Interfaces\ResourceInterface;
use DLRoute\Server\DLServer;

class ResourceManager implements ResourceInterface {

    public function __construct() {
    }

    public static function css(string $path, ?bool $external = false): string {
        # Elimina la extensión del archivo.
        $path = self::delete_extension($path);

        $path = RouteDebugger::dot_to_slash($path);

        /**
         * Ruta completa del archivo sin la extensión.
         * 
         * @var string
         */
        $route = RouteDebugger::process_route($path);

        /**
         * Ruta completa del archivo CSS con extensión incluida.
         * 
         * @var string
         */
        $filename = "{$route}.css";

        if (!file_exists($filename)) {
            return "<!-- El archivo {$path}.css no existe -->";
        }

        /**
         * Hash calculado del archivo.
         * 
         * @var string
         */
        $hash = self::calculate_hash($filename);

        /**
         * Contenido CSS.
         * 
         * @var string
         */
        $css_content = file_get_contents($filename);
        $css_content = trim($css_content);

        if (!$external) {
            return "<style>{$css_content}</style>";
        }

        return $css_content;
    }

    public static function js(string $path, ?array $options = []): string {
        $path = self::delete_extension($path, "js");

        /**
         * Ruta física el archivo sin la extensión.
         * 
         * @var string
         */
        $route = RouteDebugger::process_route($path);

        $filename = "{$route}.js";

        if (!file_exists($filename)) {
            return "<!-- El archivo {$path}.js no existe -->";
        }

        /**
         * @var array|object
         */
        $config = [];

        if (is_array($options)) {

            foreach ($options as $key => $option) {
                $config[$key] = $option;
            }
        }

        $config = (object) $config;

        /**
         * Token de seguridad.
         * 
         * @var string
         */
        $token = $config->token ?? '';

        /**
         * Indicar si el script se tratará como un archivo externo o se incorporará
         * directamente su contenido.
         * 
         * @var boolean
         */
        $external = $config->external ?? false;

        /**
         * Atributos de comportamiento para las etiquetas `<script></script>`
         * 
         * @var string
         */
        $behavior_attributes = trim($config->behavior_attributes ?? '');

        /**
         * Identifica el lenguaje de scripting en el que está escrito el código embebido dentro de
         * la etiqueta `script`
         * 
         * @var string
         */
        $type = trim($config->type ?? 'text/javascript');

        /**
         * Código JavaScript. Aplica solo si es embebido en el código HTML en lugar de tratarse
         * como un recurso externo.
         * 
         * @var string.
         */
        $js_content = file_get_contents($filename);
        $js_content = trim((string) $js_content);

        if ($external) {
            return $js_content;
        }

        return "<script type=\"{$type}\" nonce=\"{$token}\" {$behavior_attributes}>{$js_content}</script>";
    }

    public static function image(string $path, object|array|null $config = null): string | false {
        /**
         * Ruta auxiliar.
         * 
         * @var string
         */
        $aux_path = $path;

        if ($path === false) {
            return "<!-- El recurso {$aux_path} no existe -->";
        }

        /**
         * Indica si debe ser presentada codificada como base64.
         * 
         * @var boolean
         */
        $html = false;

        /**
         * Título de la imagen.
         * 
         * @var string
         */
        $title = "";

        if (!is_null($config)) {
            $config = (object) $config;

            $html = $config->html ?? false;
            $title = $config->title ?? '';
        }

        $realpath = DLRealPath::get_instance();

        /**
         * Directorio raíz de la aplicación.
         * 
         * @var string
         */
        $root = $realpath->get_document_root();

        /**
         * URI del directorio de trabajo.
         * 
         * @var string
         */
        $uri_from_workdir = $realpath->get_uri_from_workdir();
        $uri_from_workdir = RouteDebugger::trim_slash($uri_from_workdir);

        /**
         * Archivo de imagen.
         * 
         * @var string
         */
        $image_file = "{$root}/{$path}";

        /**
         * Tipo de archivos
         * 
         * @var string
         */
        $type = FileInfo::get_type($image_file);

        if (!file_exists($image_file)) {
            return false;
        }

        if (!FileInfo::is_image($image_file)) {
            return false;
        }

        /**
         * Contenido binario del archivo.
         * 
         * @var string
         */
        $content = file_get_contents($image_file);

        if (!$html) {
            header("Content-Type: {$type}; charset=utf-8");
            return trim($content);
        }

        $content = base64_encode($content);
        $content = "data:{$type};base64,{$content}";

        /**
         * Código HTML de la imagen
         * 
         * @var string
         */
        $html = self::get_image([
            "src" => "$content",
            "type" => $type,
            "title" => $title
        ]);

        return $html;
    }

    /**
     * Devuelve la estractura HTML de la imagen.
     * 
     * Ejemplo de uso:
     * 
     * ```php
     * <?php
     *  $image = self::get_image([
     *     "title" => $title,
     *     "type" => $type,
     *     "src" => $src
     * ]);
     * ```
     *
     * @param object | array $info Información del archivo.
     * @return string
     */
    private static function get_image(object | array $info): string {
        $info = (object) $info;

        /**
         * Tipo de archivos.
         * 
         * @var string
         */
        $type = $info->type ?? '';

        /**
         * Título de la imagen.
         * 
         * @var string
         */
        $title = $info->title ?? '';

        /**
         * Ruta o contenido de la imagen.
         * 
         * @var string
         */
        $src = $info->src ?? '';

        /**
         * Código HTML.
         * 
         * @var string
         */
        $html = "<picture>
                    <source srcset=\"{$src}\" type=\"{$type}\" title=\"{$title}\">
                    <img src=\"{$src}\" alt=\"{$title}\" title=\"{$title}\" loading=\"lazy\">
                </picture>";

        $html = preg_replace("/\s+/", ' ', $html);

        return trim($html);
    }

    public static function asset(string $path): string {
        /**
         * Ruta auxilar.
         * 
         * @var string
         */
        $aux_path = trim($path);

        $path = self::process_uri($path);

        if ($path === false) {
            return "El recurso {$aux_path} no existe";
        }

        /**
         * Instancia de DLRealPath
         * 
         * @var DLRealPath
         */
        $realpath = DLRealPath::get_instance();

        /**
         * URI del directorio de trabajo.
         * 
         * @var string
         */
        $uri_from_workdir = $realpath->get_uri_from_workdir();

        /**
         * Directorio raíz del proyecto.
         * 
         * @var string
         */
        $root = $realpath->get_document_root();

        /**
         * Ruta del archivo.
         * 
         * @var string
         */
        $route = RouteDebugger::trim_slash($path);

        /**
         * Nombre de archivo.
         * 
         * @var string
         */
        $filename = "{$root}/{$route}";

        /**
         * Ruta del archivo.
         * 
         * @var string
         */
        $route = self::exclude_first_part($route);

        if (!file_exists($filename)) {
            return "<!-- El archivo {$route} no existe -->";
        }

        /**
         * Contenido del archivo.
         * 
         * @var string
         */
        $content = file_get_contents($filename);

        /**
         * Suma de verificación del archivo.
         * 
         * @var string
         */
        $hash = hash('sha256', $content);

        /**
         * URL sin la URI.
         * 
         * @var string
         */
        $http_host = DLServer::get_http_host();

        $filename = "{$uri_from_workdir}/{$route}?{$hash}";
        $filename = trim($filename);
        $filename = RouteDebugger::trim_slash($filename);

        /**
         * URL completa del archivo.
         * 
         * @var string
         */
        $url = "{$http_host}/{$filename}";

        return RouteDebugger::url_encode($url);
    }

    /**
     * Elimina la extensión seleccionada de la ruta de archivo (si aplica).
     *
     * @param string $path
     * @param string $extension
     * @return string
     */
    private static function delete_extension(string $path, ?string $extension = "css"): string {
        $path = preg_replace("/\.{$extension}$/", '', $path);
        return $path;
    }

    /**
     * Excluye la primera parte de una ruta.
     *
     * @param string $path Ruta a la que se le excluirá la primera parte.
     * @return string
     */
    private static function exclude_first_part(string $path): string {
        return preg_replace("/^(.*?)\//", "", $path);
    }

    /**
     * Calcula el HASH del contenido de los archivos que se quieran analizar con el objeto
     * de que se pueda establecer de que ha cambiado el contenido.
     *
     * @param string $path Ruta del archivo al que se le calculará el hash
     * @return string
     */
    private static function calculate_hash(string $path): string {
        /**
         * Contenido del archivo a ser analizado.
         * 
         * @var string
         */
        $content = "";

        if (!file_exists($path)) {
            return $content;
        }

        $content = file_get_contents($path);

        if ($content === FALSE) {
            $content = "";
        }

        $hash = hash('sha256', $content);
        return $hash;
    }

    /**
     * Procesa la ruta que se pase como argumento. 
     *
     * @param string $path
     * @return string
     */
    private static function process_uri(string $path): string | false {
        $realpath = DLRealPath::get_instance();
        $root = $realpath->get_document_root();

        /**
         * Expresión regular de búsqueda de extensión del archivo
         * en el caso de que éste exista.
         * 
         * @var string
         */
        $pattern = "/\.(?!.*\.)[^.]+$/";

        /**
         * Indica si se capturó la extensión del archivo.
         * 
         * @var string
         */
        $found = preg_match($pattern, $path, $matches);

        /**
         * Extensión capturada del archivo.
         * 
         * @var string
         */
        $extension = "";

        if ($found) {
            $extension = $matches[0] ?? '';
        }

        $path = preg_replace($pattern, '', $path);
        $path = RouteDebugger::dot_to_slash($path);
        $path = "{$path}{$extension}";

        $filename = "{$root}/{$path}";

        /**
         * Ruta auxiliar.
         * 
         * @var string
         */
        $aux_path = $path;

        if (!file_exists($filename)) {
            $filename = RouteDebugger::dot_to_slash($filename);
            $path = RouteDebugger::dot_to_slash($path);
        }

        if (!file_exists($filename)) {
            return false;
        }

        return $path;
    }
}
