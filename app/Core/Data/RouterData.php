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

namespace DLRoute\Core\Data;

use DLRoute\Core\Times\DLTime;
use DLRoute\Server\DLHost;
use DLRoute\Server\DLServer;
use DLRoute\Server\Domain;
use DLRoute\Server\PortCandidate;
use DLRoute\Server\SchemeHTTP;

/**
 * Telemetría de la petición
 * 
 * @package DLRoute\Core\Data
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
final class RouterData {
    use SchemeHTTP, PortCandidate, Domain;

    /**
     * URL completa de la petición
     *
     * @var string $url
     */
    public readonly string $url;

    /**
     * Dirección IP del cliente
     *
     * @var string
     */
    public readonly string $ip_client;

    /**
     * Dirección IP remota desde donde se hace la petición. Puede ser un cliente IP o
     * o desde donde se hace la petición. No es necesariamente la dirección IP del cliente HTTP.
     *
     * @var string
     */
    public readonly string $remote_addr;

    /**
     * Agente de usuario del visitante
     *
     * @var string
     */
    public readonly string $user_agent;

    /**
     * Protocolo HTTP
     *
     * @var non-empty-string $scheme
     */
    public readonly string $scheme;

    /**
     * Nombre de host o dominio
     *
     * @var non-empty-string $host
     */
    public readonly string $host;

    /**
     * Número de puerto de la ruta. No el puerto real de ejecución de la aplicación
     *
     * @var integer $port
     */
    public readonly int $port;

    /**
     * Puerto local o real de ejecución de la aplicación y no de la aplicación
     * del cliente HTTP.
     *
     * @var integer
     */
    public readonly int $local_port;

    /**
     * Directorio de ejecución de la aplicación
     *
     * @var string $dir
     */
    public readonly string $dir;

    /**
     * Ruta de la aplicación. No importa si la ruta está registrada o no.
     *
     * @var string $route
     */
    public readonly string $route;

    /**
     * Ruta completa. Incluye el directorio de ejecución de la aplicación
     *
     * @var string $uri
     */
    public readonly string $uri;

    /**
     * Método de la petición HTTP
     *
     * @var non-empty-string
     */
    public readonly string $method;

    /**
     * Fecha de consulta o de la petición
     *
     * @var string
     */
    public readonly string $time;

    /**
     * Devuelve el nombre del desarrollador del sistema de rutas.
     *
     * @var non-empty-string $developer
     */
    public readonly string $developer;

    public function __construct(string $url) {
        $this->ip_client = DLServer::get_ipaddress();
        $this->remote_addr = DLServer::get_remote_addr();
        $this->user_agent = DLServer::get_user_agent();
        $this->url = trim($url);
        $this->port = self::get_port();
        $this->local_port = self::get_local_port();
        $this->scheme = self::get_scheme();
        $this->dir = DLServer::get_dir();
        $this->route = DLServer::get_route();
        $this->host = DLHost::get_domain();
        $this->uri = DLServer::get_uri();
        $this->method = DLServer::get_method();
        $this->time = DLTime::now_string();
        $this->developer = "David E Luna M";
    }
}