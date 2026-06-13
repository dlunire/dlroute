<?php

declare(strict_types=1);

namespace DLRoute\Core\Data;

use DLRoute\Core\Routing\Router;
use DLRoute\Server\DLHost;
use DLRoute\Server\DLServer;

/**
 * Clase inmutable de telemetría y observabilidad del entorno.
 *
 * Captura y expone una instantánea (snapshot) detallada de los metadatos
 * de la petición HTTP actual y el estado del entorno de ejecución.
 * Diseñada bajo propiedades de solo lectura para garantizar la integridad
 * de la información recolectada durante el análisis de diagnóstico.
 *
 * @package DLRoute\Core\Data
 * @author  David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
final class Telemetry {

    /**
     * Mensaje descriptivo o informativo del estado actual.
     *
     * @var string
     */
    public readonly string $message;

    /**
     * Ruta formateada y procesada por el framework.
     *
     * @var string
     */
    public readonly string $route;

    /**
     * URI completa de la petición (incluyendo query strings si aplican).
     *
     * @var string
     */
    public readonly string $uri;

    /**
     * Directorio base o subcarpeta desde donde se ejecuta la aplicación.
     *
     * @var string
     */
    public readonly string $dir;

    /**
     * URL base del servidor (esquema + dominio y puerto activo).
     *
     * @var string
     */
    public readonly string $base_url;

    /**
     * Dominio o nombre de host sin puerto.
     *
     * @var string
     */
    public readonly string $domain;

    /**
     * Hostname completo, incluyendo el puerto cuando no es estándar (ej. localhost:4000).
     *
     * @var string
     */
    public readonly string $hostname;

    /**
     * Indica si la conexión utiliza HTTPS.
     *
     * @var bool
     */
    public readonly bool $is_https;

    /**
     * Puerto expuesto al cliente HTTP (puerto remoto).
     *
     * @var int
     */
    public readonly int $port;

    /**
     * Puerto local donde se ejecuta la aplicación.
     *
     * @var int
     */
    public readonly int $local_port;

    /**
     * Estampa de tiempo en formato ISO 8601 (DATE_ATOM).
     *
     * @var string
     */
    public readonly string $timestamp;

    /**
     * Dirección IP real del cliente que originó la petición.
     *
     * @var string
     */
    public readonly string $cliente_ip;

    /**
     * Método o verbo HTTP de la petición activa (GET, POST, etc.).
     *
     * @var string
     */
    public readonly string $method;

    /**
     * Cadena de identificación del agente de usuario (navegador, Postman, etc.).
     *
     * @var string
     */
    public readonly string $user_agent;

    /**
     * Indica si la petición proviene probablemente de un proxy inverso.
     *
     * @var bool
     */
    public readonly bool $proxy;

    /**
     * Información del mapa y el almacén de datos del enrutador.
     *
     * @var RouterData
     */
    public readonly RouterData $from;

    /**
     * Inicializa la telemetría del sistema capturando el estado del entorno y la red.
     *
     * Invoca de forma lineal las utilidades del servidor de bajo nivel para poblar
     * las propiedades inmutables con el menor consumo de CPU posible.
     *
     * @param string $message Mensaje informativo que contextualiza el diagnóstico.
     */
    public function __construct(string $message) {
        $this->message    = trim($message);
        $this->route      = DLServer::get_route();
        $this->uri        = DLServer::get_uri();
        $this->dir        = DLServer::get_dir();
        $this->base_url   = DLServer::get_base_url();
        $this->domain     = DLHost::get_domain();
        $this->hostname   = DLHost::get_hostname();
        $this->is_https   = DLHost::is_https();
        $this->port       = DLServer::get_port();
        $this->local_port = DLServer::get_local_port();
        $this->timestamp  = date(DATE_ATOM);
        $this->cliente_ip = DLServer::get_ipaddress();
        $this->method     = DLServer::get_method();
        $this->user_agent = DLServer::get_user_agent();
        $this->proxy      = DLServer::is_likely_proxy();
        $this->from       = Router::from();
    }
}