<?php

declare(strict_types=1);

namespace DLRoute\Route;

use DLRoute\Route\Contracts\RouteLexerInterface;

class RouteLexer implements RouteLexerInterface {

    /**
     * URI registrada por el desarrolador
     *
     * @var string $uri
     */
    private readonly string $uri;

    /**
     * Tamaño de la cadena de bytes a ser analizado
     *
     * @var integer
     */
    private readonly int $size;

    /**
     * Posición del cursor del analizador léxico
     *
     * @var integer
     */
    private int $offset = 0;

    /**
     * Token capturado del analizador léxico
     *
     * @var array
     */
    private array $tokens = [];


    public function __construct(string $uri) {
        $uri = \trim($uri, '\t\n\r\0\x0B/');

        if ($uri !== '' && $uri[0] === self::SEPARATOR) {
            $uri = "/{$uri}";
        }

        $this->uri = $uri;
        $this->size = \strlen($this->uri);
    }

    /**
     * Escanea la URI registrada por el desarrollador para descomponerla en tokens
     *
     * @return void
     */
    protected function scanner(): void {

        while($this->offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->uri[$this->offset];

            /** @var non-empty-string|null $pick */
            $pick = $this->uri[$this->offset + 1] ?? null;

            if ($byte === self::WHITE_SPACE) {
                $this->request_emit_token();
                continue;
            }

            $this->offset++;
        }
    }

    /**
     * Solicita la emisión de un token una vez que el scanner detecta el byte disparador
     * de emisión de token
     *
     * @return void
     */
    private function request_emit_token(): void {

        /** @var int $start_offset */
        $start_offset = $this->offset;


    }

    private function emit_token(): void {

    }

    /**
     * Avanza el delimitador a la siguientes posición.
     * 
     * @return void
     */
    private function next_delimiter(): void {

        /** @var int $start_offset */
        $start_offset = $this->offset;

        while ($this->offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->uri[$this->offset];

            if ($byte === self::WHITE_SPACE) {
                // IMPORTANTE: durante el análisis léxico, el espacio en blanco será
                // reemplazado por subguiones.
                $this->uri[$this->offset] = self::UNDESCORE;
            }

            $this->offset++;
        }
    }
}