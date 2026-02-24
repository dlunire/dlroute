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

namespace DLRoute\Core\Routing;

use DLRoute\Core\Data\RouterData;
use DLRoute\Errors\RouteException;
use DLRoute\Server\DLServer;

/**
 * Clase principal para la gestión de rutas en la aplicación.
 *
 * Esta clase proporciona métodos para:
 * - Generar URLs absolutas a partir de rutas relativas (`Router::to()`).
 * - Obtener telemetría detallada de la ruta actualmente visitada (`Router::from()`).
 *
 * La clase **no realiza validaciones sobre la existencia de rutas** ni controla
 * permisos de acceso. Su objetivo es manejar la construcción de URLs y la
 * obtención de información contextual de las rutas.
 *
 * Ejemplo de uso:
 * ```php
 * // Generar URL absoluta
 * $url = Router::to('/ciencia/entorno');
 *
 * // Obtener telemetría de la ruta actual
 * $data = Router::from();
 * echo $data->url;   // URL completa
 * echo $data->method; // Método HTTP
 * ```
 *
 * @package DLRoute\Core\Routing
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
final class Router {

    /**
     * Devuelve la URL absoluta completa hacia una ruta específica de la aplicación.
     *
     * Este método genera la URL tomando como base la URL de la aplicación (`DLServer::get_base_url()`)
     * y concatenando la ruta proporcionada. Si la ruta es `'/'` o una cadena vacía,
     * se devuelve únicamente la URL base de la aplicación.
     *
     * Este método **no verifica** si la ruta existe en el sistema de rutas ni si está activa.
     * Su finalidad es construir URLs absolutas a partir de rutas conocidas.
     *
     * Ejemplo:
     * ```php
     * echo Router::to('/alguna/ruta');
     * // Devuelve algo como: "https://your-server.com/subdirectorio/alguna/ruta"
     * ```
     *
     * @param string $route [Opcional] La ruta relativa hacia donde se desea navegar. Por defecto `'/'`.
     * @return non-empty-string URL absoluta generada.
     *
     * @throws \DLRoute\Errors\RouteException Si la ruta tiene un formato inválido tras normalizarla.
     */
    public static function to(string $route = '/'): string {
        self::normalize_route($route);

        /** @var non-empty-string $url_base */
        $url_base = DLServer::get_base_url();

        return ($route !== '/' && trim($route) !== '')
            ? "{$url_base}/{$route}"
            : $url_base;
    }

    /**
     * Devuelve telemetría completa de la ruta actualmente visitada.
     *
     * Este método construye un objeto `RouterData` que contiene información
     * detallada sobre la URL y el contexto de la petición actual, incluyendo:
     * - URL absoluta visitada.
     * - Protocolo HTTP (`http` o `https`).
     * - Host o dominio.
     * - Puerto de la ruta.
     * - Directorio de ejecución de la aplicación.
     * - Ruta relativa dentro de la aplicación.
     * - URI completa (incluyendo directorio de la aplicación).
     * - Método HTTP (`GET`, `POST`, etc.).
     * - Marca temporal de la consulta.
     *
     * La telemetría devuelta representa la **ruta actualmente visitada**, por lo que
     * no es útil para generar datos sobre rutas futuras o aún no visitadas.
     *
     * Ejemplo de uso:
     * ```php
     * $info = Router::from();
     * echo $info->url;      // URL completa de la petición actual
     * echo $info->method;   // Método HTTP de la petición
     * ```
     *
     * @return RouterData Objeto con la telemetría de la ruta actual.
     */
    public static function from(): RouterData {
        return new RouterData(self::to(DLServer::get_route()));
    }

    /**
     * Normalizador de ruta
     *
     * @param string $route Ruta a normalizar
     * @return void
     */
    private static function normalize_route(string &$route): void {

        $route = trim($route);
        $route = trim($route, '\/');

        $route = preg_replace("/[\/\\\]+/", '/', $route);

        if (!\is_string($route)) {
            throw new RouteException("La ruta ruta ingresada tiene un formato inválido");
        }
    }
}