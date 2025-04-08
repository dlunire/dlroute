<?php

namespace DLRoute\Traits;

use CurlHandle;
use DLRoute\Requests\DLOutput;
use DLRoute\Requests\HeadersInit;
use DLRoute\Requests\RequestInit;

trait Request {

    /**
     * Método de envío HTTP GET
     * 
     * @var string
     */
    public const GET = 'GET';

    /**
     * Método HTTP POST
     * 
     * @var string
     */
    public const POST = 'POST';

    /**
     * Método HTTP PUT
     * 
     * @var string
     */
    public const PUT = 'PUT';

    /**
     * Método HTTP PATCH
     * 
     * @var string
     */
    public const PATCH = 'PATCH';

    /**
     * Método HTTP DELETE
     * 
     * @var string
     */
    public const DELETE = 'DELETE';

    /**
     * Envía una petición HTTP a un servidor remoto
     *
     * @param string $url URL
     * @param string $method
     * @param HeadersInit|null $headers
     * @return string|boolean
     */
    protected function request(string $url, string $method = 'GET', ?HeadersInit $headers = null, array $data = []): string|bool {

        /**
         * @var CurlHandle|false $curl
         */
        // Inicializar sesión cURL
        $ch = curl_init();

        if (!($ch instanceof CurlHandle)) {
            return false;
        }

        /**
         * Cabeceras actuales
         * 
         * @var array $current_headers
         */
        $current_headers = [];

        if ($headers instanceof HeadersInit) {
            $current_headers = $headers->get_headers();
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $current_headers);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        /**
         * Respuesta de la solicitud
         * 
         * @var string|bool $response
         */
        $response = curl_exec($ch);

        if (!$response) {
            return $response;
        }

        /**
         * @var string|int $response_code
         */
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'Error en cURL: ' . curl_error($ch);

            http_response_code(500);
            DLOutput::get_json([
                "status" => false,
                "error" => "Error en cURL {$ch}"
            ]);

            exit;
        }

        curl_close($ch);

        http_response_code($response_code);
        return $response;
    }

    /**
     * Envía una petición al servidor remoto
     *
     * @param string $action URL base del servidor
     * @return string
     */
    public function fetch(string $action, RequestInit $init): string {
        /**
         * @var string $response
         */
        $response = $this->request($action, $init->method, $init->headers, $init->body);

        return $response;
    }
}