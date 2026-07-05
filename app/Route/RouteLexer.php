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

namespace DLRoute\Route;

use DLRoute\Core\Data\QueryParamValue;
use DLRoute\Enums\TokenType;
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

    private TokenType $tokentype = TokenType::LITERAL;

    /**
     * Parámetros de la consulta
     *
     * @var QueryParamValue[] $query_param
     */
    private array $query_param = [];

    /**
     * @param string $uri URI a ser analizada
     */
    public function __construct(string $uri) {
        $uri = \trim($uri, '\t\n\r\0\x0B/');

        if ($uri !== "" && $uri[0] === self::SEPARATOR) {
            $uri = "/{$uri}";
        }

        $this->uri = $uri;
        $this->size = \strlen($this->uri);
    }

    /**
     * Escanea la URI registrada por el desarrollador para descomponerla en tokens.
     *
     * Analizar algo parecido a esto:
     * `/api/{uuid?}/usuarios/{id}`
     *
     * @return void
     */
    protected function scanner(): void {
        while ($this->offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->uri[$this->offset];

            if ($byte !== self::WHITE_SPACE) {
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
    }

    /**
     * Emite el token solicitado durante el análisis léxico.
     *
     * @return void
     */
    private function emit_token(): void {
        /** @var int $start_offset */
        $start_offset = $this->offset;

        $this->next_delimiter($start_offset);
    }

    /**
     * Avanza el delimitador a la siguientes posición.
     *
     * @return void
     */
    private function next_delimiter(int $offset): void {
        /** @var int $start_offset */
        $start_offset = $this->offset;

        while ($this->offset < $this->size) {
            /** @var non-empty-string $byte Byte actual capturado durante el análisis léxico */
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
