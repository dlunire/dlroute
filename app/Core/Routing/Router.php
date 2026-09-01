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
 * @license AGPL-3.0 license
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