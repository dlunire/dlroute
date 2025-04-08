<?php

namespace DLRoute\Requests;

/**
 * Conjunto de cabeceras HTTPS
 * 
 * @package dlunamontilla/dlroute
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <dlunamontilla@kazecode.com>
 * @copyright 2024 David E Luna M
 * @license MIT
 */
final class HeadersInit {

    /**
     * Valores 
     *
     * @var array $headers
     */
    private array $headers = [];

    /**
     * Establece el valor de la cabecera
     *
     * @param string $name Nombre de la cabecera
     * @param string $value Valor de la cabecera
     * @return void
     */
    public function set(string $name, string $value): void {
        $this->headers[$name] = trim($value);
    }

    /**
     * Devuelve el valor de la cabecera
     *
     * @param string $name Nombre de la cabecera
     * @return string|null
     */
    public function get(string $name): ?string {
        return $this->headers[$name] ?? null;
    }

    /**
     * Establece de forma dinámica el valor de la cabeera
     *
     * @param string $name Nombre de la cabecera
     * @param string $value Valor de la cabecera
     * @return void
     */
    public function __set(string $name, string $value): void {
        $this->set($name, $value);
    }

    /**
     * Devuelve el valor de la cabecera de forma dinámica
     *
     * @param string $name Nombre de la cabecera
     * @return string|null
     */
    public function __get(string $name): ?string {
        return $this->get($name);
    }

    /**
     * Devuelve todas las cabeceras que se han definido
     *
     * @return array
     */
    public function get_headers(): array {

        /**
         * Cabeceras HTTP
         * 
         * @var array<int, string> $headers
         */
        $headers = [];

        foreach ($this->headers as $key => $value) {

            if (!is_string($key) || !is_string($value)) {
                continue;
            }

            $key = trim($key);
            $value = trim($value);

            /**
             * Cabecera actualmente capturada
             * 
             * @var string $header
             */
            $header = "{$key}: {$value}";

            array_push($headers, $header);
        }

        return $headers;
    }
}
