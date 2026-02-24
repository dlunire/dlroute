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
 * @license MIT
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
    }
}