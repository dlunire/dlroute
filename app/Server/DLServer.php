<?php

/**
 * Copyright (c) 2026 David E Luna M
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
use DLRoute\Interfaces\Routing\RouteLexerInterface;
use DLRoute\Interfaces\ServerInterface;
use DLRoute\Routes\RouteDebugger;

class DLServer implements ServerInterface, RouteLexerInterface {
    use Domain, IPAddress, PortCandidate;

    public static function get_uri(): string {
        /** @var string $uri */
        $uri = "";

        if (\array_key_exists('REQUEST_URI', $_SERVER)) {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $uri = trim($uri, "/");

        self::remove_duplicate_slash($uri);

        return $uri;
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

    /**
     * Determina si el método HTTP es HEAD.
     * 
     * @return bool
     */
    public static function is_head(): bool {
        return self::get_method() === "HEAD";
    }

    /**
     * Determina si el método HTTP es OPTIONS.
     * 
     * @return bool
     */
    public static function is_options(): bool {
        return self::get_method() === "OPTIONS";
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
        return $_SERVER['SERVER_SOFTWARE'] ?? NULL;
    }

    public static function get_route(): string {
        /**
         * URI de la aplicación.
         * 
         * @var string
         */
        $uri = self::get_uri();
        $uri = urldecode($uri);

        /** @var int $uri_length */
        $uri_length = \strlen($uri);

        self::remove_querystring($uri);

        /** @var int $offset */
        $offset = \strlen(self::get_script_dir()) - 1;

        /** @var non-empty-string $route */
        $route = \substr($uri, $offset, $uri_length);

        self::remove_duplicate_slash($route);

        return "/" . trim($route, "/");
    }

    /**
     * Elimina barras diagonales duplicadas y normaliza la estructura de la URI.
     * * Este método actúa como un analizador léxico básico que fragmenta la entrada 
     * mediante el delimitador SLASH. Durante el recorrido, filtra vacíos, 
     * decodifica entidades URL y aplica un trim, reconstruyendo la cadena 
     * final con un formato canónico (un solo separador entre tokens).
     *
     * @param string $input Referencia a la URI original; será sobrescrita con 
     * la versión normalizada.
     * @return void
     */
    public static function remove_duplicate_slash(string &$input): void {

        /** @var int $length */
        $length = \strlen($input);

        /** @var non-empty-string[] $buffer Contenedor de tokens normalizados */
        $buffer = [];

        /** @var int $start_offset Puntero de inicio del lexema actual */
        $start_offset = 0;

        /** @var int $offset Puntero de lectura actual */
        $offset = 0;

        while ($offset < $length) {
            $byte = $input[$offset];

            /** @var boolean $end Indicador de fin de cadena */
            $end = $offset === $length - 1;

            if (self::SLASH === $byte || $end) {

                /** @var int $lexeme_length Tamaño del segmento extraído */
                $lexeme_length = $offset - $start_offset;

                // Salto de seguridad: ignora segmentos vacíos consecutivos
                if ($lexeme_length < 1) {
                    $offset++;
                    $start_offset = $offset;
                    continue;
                }

                // Extracción, decodificación y limpieza del token
                $lexeme = \substr($input, $start_offset, $end ? $lexeme_length + 1 : $lexeme_length);
                $lexeme = urldecode($lexeme);
                $lexeme = trim($lexeme);

                // Solo almacena lexemas válidos
                if ($lexeme !== '') {
                    $buffer[] = $lexeme;
                }
                $start_offset = $offset + 1;
            }

            $offset++;
        }

        // Reconstrucción de la ruta normalizada
        $input = "/" . implode("/", $buffer);
    }

    public static function get_script_name(): string {
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        return trim(urldecode($script_name));
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
        $script_dir = trim($script_dir, '/');
        $script_dir = trim($script_dir);

        return "/{$script_dir}";
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
         * @var non-empty-string $host
         */
        $host = self::get_http_host();

        /**
         * Directorio base de ejecución de la aplicación.
         * 
         * @var string $basedir
         */
        $basedir = self::get_script_dir();

        /** @var non-empty-string $base_url */
        $base_url = trim("{$host}{$basedir}", '/');

        return $base_url;
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
     * Remueve las query de las URI.
     *
     * @param string $input Entrada a ser procesada
     * @return void
     */
    private static function remove_querystring(string &$input): void {
        $input = trim($input);

        /** @var int|false $offset */
        $offset = \strpos($input, '?', 0);

        if ($offset === false) {
            $offset = \strlen($input);
        }

        $input = \substr($input, 0, $offset);
        $input = \trim($input, "/");
    }

    /**
     * Devuelve el directorio de ejecución o punto de entrada de la aplicación.
     *
     * @return string
     */
    public static function get_dir(): string {
        return self::get_script_dir();
    }
}
