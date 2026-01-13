<?php

namespace DLRoute\Requests;

use DLRoute\Interfaces\OutputInterface;
use DLRoute\Server\DLServer;

class DLOutput implements OutputInterface {

    /**
     * Instancia de clase
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Contenido a ser analizado
     *
     * @var mixed
     */
    private mixed $content = null;

    private function __construct() {
    }

    /**
     * Devuelve una instancia de Output
     *
     * @return self
     */
    public static function get_instance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function print_response_data(?string $mime_type = null): void {
        $mime = "blob";

        if ($this->is_string()) {
            $mime = "text/html";
        }

        if ($this->is_boolean() || $this->is_null() || $this->is_numeric()) {
            $mime = "text/plain";
        }

        if ($this->is_boolean()) {
            $this->content = $this->content ? "true" : "false";
        }

        if ($this->is_array() || $this->is_object()) {
            $mime = "application/json";
            $this->content = self::get_json($this->content, true);
        }

        if (!is_null($mime_type)) {
            $mime = $mime_type;
        }

        header("Content-Type: {$mime}; charset=utf-8");
        print_r($this->content);
    }

    public function set_content(mixed $content): void {
        $this->content = is_string($content) ? trim($content) : $content;
    }

    public static function get_json(object|array $content, bool $pretty = false): string {
        $stringData = $pretty
            ? json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK)
            : json_encode($content, JSON_NUMERIC_CHECK);


        return trim($stringData);
    }

    public static function not_found(): void {
        header("Content-Type: application/json; charset=utf-8", true, 404);

        /**
         * Ruta actual de la petición.
         * 
         * @var string
         */
        $route = DLServer::get_route();

        echo DLOutput::get_json([
            "code" => 404,
            "route" => $route,
            "uri" => DLServer::get_uri(),
            "base_url" => DLServer::get_base_url(),
            "message" => "La ruta solicitada no existe",
            "timestamp" => date(DATE_ATOM),
            "method" => DLServer::get_method(),
            "client_ip" => DLServer::get_ipaddress(),
            "hint" => "Verifica que la ruta sea correcta y esté registrada en el servidor"
        ], true);

        exit;
    }

    /**
     * Valida si la salida es un array
     *
     * @return boolean
     */
    private function is_array(): bool {
        return is_array($this->content);
    }

    /**
     * Valida si la salida es un objeto.
     *
     * @return boolean
     */
    private function is_object(): bool {
        return is_object($this->content);
    }

    /**
     * Valida si es un booleano
     *
     * @return boolean
     */
    private function is_boolean(): bool {
        return is_bool($this->content);
    }

    /**
     * Valida si es nulo
     *
     * @return boolean
     */
    private function is_null(): bool {
        return is_null($this->content);
    }

    /**
     * Valida si es numérico
     *
     * @return boolean
     */
    private function is_numeric(): bool {
        return is_numeric($this->content);
    }

    /**
     * Valida si es una cadena de texto.
     *
     * @return boolean
     */
    private function is_string(): bool {
        return is_string($this->content);
    }
}
