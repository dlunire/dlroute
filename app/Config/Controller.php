<?php

namespace DLRoute\Config;

use DLRoute\Requests\DLOutput;
use DLRoute\Requests\DLRequest;
use DLRoute\Requests\DLUpload;
use DLRoute\Server\DLServer;
use DLRoute\Traits\Request;
use DLRoute\Validates\DLValidates;

/**
 * Controlador base
 * 
 * @package DLRoute\Config
 * 
 * @version 0.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
abstract class Controller {

    use DLValidates, DLUpload, Request;

    /**
     * Procesa las peticiones del usuario.
     *
     * @var DLRequest
     */
    protected DLRequest $request;

    public function __construct() {
        $this->request = DLRequest::get_instance();
    }

    /**
     * Devuelve una dirección IP
     *
     * @return string
     */
    protected function get_ip(): string {
        return DLServer::get_ipaddress();
    }

    /**
     * Devuelve el hombre de host con puerto incluido en formato HTTP, es decir,
     * de una forma similar a esta: http://localhost:3000/
     *
     * @return string
     */
    protected function get_http_host(): string {
        return DLServer::get_http_host();
    }

    /**
     * Convierte un objeto o un array en una cadena de texto en formato JSON y la devuelve.
     *
     * Esta función toma un objeto o array y lo convierte en una cadena de texto en formato JSON.
     *
     * @param object|array $content El contenido que se va a parsear.
     * @param bool $pretty Indica si la salida en formato JSON debe tener formato legible o no.
     * @return string La cadena de texto en formato JSON resultante.
     */
    protected function get_json(array|object $data, bool $pretty = false): string {
        return DLOutput::get_json($data, $pretty);
    }
}
