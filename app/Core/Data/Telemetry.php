<?php

declare(strict_types=1);

namespace DLRoute\Core\Data;

use DLRoute\Core\Routing\Router;
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
        $this->timestamp  = date(DATE_ATOM);
        $this->cliente_ip = DLServer::get_ipaddress();
        $this->method     = DLServer::get_method();
        $this->user_agent = DLServer::get_user_agent();
        $this->from       = Router::from();
    }
}