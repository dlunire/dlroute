<?php
/**
 * Copyright (c) 2025 David E Luna M
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @license MIT
 */

declare(strict_types=1);

namespace DLRoute\Server;

use DLRoute\Config\DLRealPath;
use DLRoute\Interfaces\ServerInterface;
use DLRoute\Routes\RouteDebugger;

class DLServer implements ServerInterface {
    use Domain, IPAddress, PortCandidate;

    public static function get_uri(): string {
        /** @var string $uri */
        $uri = "";

        if (\array_key_exists('REQUEST_URI', $_SERVER)) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        return trim($uri);
    }

    public static function get_hostname(): string {
        return self::get_host();
    }

    public static function get_method(): string {
        $method = "";

        if (\array_key_exists('REQUEST_METHOD', $_SERVER)) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        return trim($method);
    }

    public static function get_script_filename(): string {
        $script_filename = "";

        if (\array_key_exists('SCRIPT_FILENAME', $_SERVER)) {
            $script_filename = $_SERVER['SCRIPT_FILENAME'];
        }

        return trim($script_filename);
    }

    public static function get_ipaddress(): string {
        return self::get_ip();
    }

    public static function get_user_agent(): string {
        $user_agent = "";

        if (\array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        }

        return $user_agent;
    }

    public static function get_document_root(): string {
        $realpath = DLRealPath::get_instance();
        return trim($realpath->get_document_root());
    }

    public static function is_post(): bool {
        return self::get_method() === "POST";
    }

    public static function is_get(): bool {
        return self::get_method() === "GET";
    }

    public static function is_put(): bool {
        return self::get_method() === "PUT";
    }

    public static function is_patch(): bool {
        return self::get_method() === "PATCH";
    }

    public static function is_delete(): bool {
        return self::get_method() === "DELETE";
    }

    public static function get_http_host(): string {
        /** @var string $http_host */
        $http_host = self::get_host();

        /** @var string $scheme */
        $scheme = self::get_scheme();

        /** @var int $port */
        $port = self::get_port();

        /** @var string $host */
        $host = "{$scheme}://{$http_host}";

        return $host;
    }

    /**
     * Devuelve el software del servidor (en el caso de que sea posible)
     *
     * @return string|null
     */
    public static function get_server_software(): ?string {

        /**
         * Software del servidor
         * 
         * @var string|null
         */
        $server_software = null;

        if (\array_key_exists('SERVER_SOFTWARE', $_SERVER)) {
            $server_software = $_SERVER['SERVER_SOFTWARE'];
        }

        return $server_software;
    }

    public static function get_route(): string {
        /**
         * URI de la aplicación.
         * 
         * @var string
         */
        $uri = self::get_uri();
        $uri = urldecode($uri);

        self::remove_query($uri);

        /**
         * Nombre del script
         * 
         * @var string
         */
        $script_name = self::get_script_name();

        /**
         * Ruta relativa de ejecución de la aplicación.
         * 
         * @var string
         */
        $relative_route = dirname($script_name);
        $relative_route = trim($relative_route);
        $relative_route = urldecode($relative_route);

        if ($relative_route === "/") {
            $relative_route = "";
        }

        /**
         * Ruta virtual.
         * 
         * @var string
         */
        $virtual_route = str_replace($relative_route, '', $uri);
        $virtual_route = trim($virtual_route);
        $virtual_route = "/{$virtual_route}";
        $virtual_route = preg_replace("/\/+/", '/', $virtual_route);

        if (empty($virtual_route)) {
            $virtual_route .= "/";
        }

        return $virtual_route;
    }

    public static function get_script_name(): string {
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        return urldecode($script_name);
    }

    /**
     * Devuelve el directorio principal de ejecución
     *
     * @return string
     */
    public static function get_script_dir(): string {
        /**
         * Archivo principal de ejecución de la aplicación.
         * 
         * @var string
         */
        $file = self::get_script_name();

        /**
         * Directorio principal de ejecución.
         * 
         * @var string
         */
        $script_dir = dirname($file);
        $script_dir = RouteDebugger::trim_slash($script_dir);

        return $script_dir;
    }

    /**
     * Devuelve la URL base de la aplicación.
     *
     * @return string
     */
    public static function get_base_url(): string {
        /**
         * Ruta del host de ejecución de la aplicación.
         * 
         * @var string
         */
        $host = self::get_http_host();

        /**
         * Directorio base de ejecución de la aplicación.
         * 
         * @var string
         */
        $basedir = self::get_script_dir();

        return "{$host}/{$basedir}";
    }

    /**
     * Devuelve el subdirectorio en función de la URL base de la aplicación
     *
     * @param string $subdir Subdirectorio
     * @return string
     */
    public static function get_subdir(string $subdir): string {
        /**
         * URL base de la aplicación
         * 
         * @var string
         */
        $base_url = self::get_base_url();

        $base_url = rtrim($base_url, "\/");
        $base_url = trim($base_url);

        $subdir = RouteDebugger::dot_to_slash($subdir);
        $subdir = RouteDebugger::trim_slash($subdir);
        $subdir = "{$base_url}/{$subdir}";

        return $subdir;
    }

    /**
     * Devuelve el protocolo HTTP que se está usando, es decir: `http` o `https`.
     *
     * @return string
     */
    private static function get_protocol(): string {
        $is_https = DLHost::is_https();

        $protocol = "http://";

        if ($is_https) {
            $protocol = "https://";
        }

        return $protocol;
    }

    /**
     * Remueve las query de las URI.
     *
     * @param string $input Entrada a ser procesada
     * @return void
     */
    private static function remove_query(string &$input): void {
        /**
         * Patrón de búsqueda de las query a ser removida.
         * 
         * @var string
         */
        $pattern = '\?(.*)$';

        $input = trim($input);

        $input = preg_replace("/{$pattern}/", '', $input);
    }

    /**
     * Calcula el directorio base de ejecución de la aplicación.
     *
     * La lógica se basa en restar la ruta registrada (Route) de la URI completa
     * solicitada. El resultado corresponde al subdirectorio donde se encuentra
     * la aplicación dentro del servidor, considerando mayúsculas/minúsculas
     * de manera insensible.
     *
     * Ejemplo:
     * URI = /subdirectorio/subdirectorio/ciencia
     * Route = /ciencia
     * Resultado: /subdirectorio/subdirectorio
     * 
     * Porque `/ciencia` es la ruta registrada.
     *
     * @return string Directorio base de la aplicación, siempre comenzando con '/'
     */
    private static function calculate_dir(): string {
        /** @var string $uri */
        $uri = trim(self::get_uri(), '/');

        /** @var string $route */
        $route = trim(self::get_route(), '/');

        /** @var string $scape_route */
        $escape_route = self::escape_route($route);

        /** @var string $route_pattern */
        $route_pattern = "/\/*{$escape_route}/i";

        /** @var array|string|null $dir */
        $dir = preg_replace($route_pattern, '', $uri);

        return "/" . \strval($dir ?? '');
    }

    private static function escape_route(string $input): string {
        return preg_replace("/\/+/", "\/", $input);
    }

    /**
     * Devuelve el directorio de ejecución o punto de entrada de la aplicación.
     *
     * @return string
     */
    public static function get_dir(): string {
        return self::calculate_dir();
    }
}
