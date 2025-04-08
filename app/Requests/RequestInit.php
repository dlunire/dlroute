<?php

namespace DLRoute\Requests;

/**
 * Opciones de la petición
 * 
 * @package dlunamontilla/dlroute
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <contact@dlunire.pro>
 * @copyright 2024 David E Luna M
 * @license MIT
 */
final class RequestInit {

    /**
     * Nombre del método HTTP
     *
     * @var string
     */
    public string $method;

    /**
     * Cabeceras HTTP
     *
     * @var HeadersInit
     */
    public HeadersInit $headers;

    /**
     * Cuerpo o datos de la petición
     *
     * @var array
     */
    public array $body;

    /**
     * Establece el nombre del método de la petición
     *
     * @param string $method Nombre del métodode la petición
     * @return void
     */
    public function set_method(string $method): void {
        $this->method = trim(
            strtoupper($method)
        );
    }

    /**
     * Establece las cabeceras HTTPS
     *
     * @param HeadersInit $headers Cabeceras HTTP
     * @return void
     */
    public function set_headers(HeadersInit $headers): void {
        $this->headers = $headers;
    }

    /**
     * Establece el cuerpo o datos de la petición
     *
     * @param array $body Cuerpo o datos de la petición
     * @return void
     */
    public function set_body(array $body): void {
        $this->body = $body;
    }
}
