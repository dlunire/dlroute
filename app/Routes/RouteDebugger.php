<?php

namespace DLRoute\Routes;
use DLRoute\Interfaces\DebuggerInterface;
use DLRoute\Server\DLServer;

/**
 * Depura las rutas introducidas por el usuario.
 * 
 * @package DLRoute\RouteDebugger
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
class RouteDebugger implements DebuggerInterface {

    public static function clear_route(string $route): string {
        $route = self::delete_duplicate_slash($route);
        $route = self::trim_slash($route);
        return $route;
    }

    public static function process_route(string $path): string {
        $root = DLServer::get_document_root();
        $dir = "{$root}/{$path}";

        $dir = self::dot_to_slash($dir);
        $dir = self::delete_duplicate_slash($dir);
        $dir = self::remove_trailing_slash($dir);

        return $dir;
    }

    public static function dot_to_slash(string $path): string {
        $path = preg_replace("/\.+/", DIRECTORY_SEPARATOR, $path);
        return trim($path);
    }

    /**
     * Elimina los duplicados de las barras diagionales (//).
     *
     * @param string $path
     * @return string
     */
    private static function delete_duplicate_slash(string $path): string {
        $path = preg_replace("/\/+/", '/', $path);
        return trim($path);
    }

    public static function remove_trailing_slash(string $path): string {
        $path = rtrim($path);
        $path = rtrim($path, '\/');

        return trim($path);
    }

    public static function trim_slash(string $path): string {
        $path = trim($path);
        $path = trim($path, '\/');

        return $path;
    }

    public static function url_encode(string $path): string {
        $path = urldecode($path);

        $path = urlencode($path);
        $path = str_replace('%2F', '/', $path);
        $path = str_replace('+', '%20', $path);
        $path = str_replace('%3A', ':', $path);
        $path = str_replace('%3F','?', $path);
        $path = str_replace('%26', '&', $path);
        $path = str_replace('%3D', '=', $path);
        
        return trim($path);
    }
}