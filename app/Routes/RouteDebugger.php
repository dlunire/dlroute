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

namespace DLRoute\Routes;
use DLRoute\Interfaces\DebuggerInterface;
use DLRoute\Server\DLServer;

/**
 * Depura las rutas introducidas por el usuario.
 * 
 * @package DLRoute\RouteDebugger
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2023 David E Luna M
 * @license AGPL-3.0 license
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