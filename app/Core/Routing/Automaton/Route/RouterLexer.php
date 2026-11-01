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

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Enums\Methods;
use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\Routing\LexerSymbolInterface;

/**
 * Analizador léxico de rutas URI.
 *
 * Implementa un autómata finito que recorre byte a byte la URI registrada
 * y la descompone en una secuencia de tokens. Cada token clasifica un
 * segmento de la ruta como texto literal ({@see TokenType::TEXT_PLAIN})
 * o como parámetro dinámico ({@see TokenType::PARAM}), indicando además
 * si el parámetro es opcional.
 *
 * Ejemplo de tokenización:
 * ```php
 * $lexer->scanner();
 * // Tokens producidos:
 * // ["lexeme" => "/users/", "optional" => false, "tokentype" => TokenType::TEXT_PLAIN]
 * // ["lexeme" => "{id}",    "optional" => false, "tokentype" => TokenType::PARAM]
 * // ["lexeme" => "{slug?}", "optional" => true,  "tokentype" => TokenType::PARAM]
 * ```
 *
 * @package DLRoute\Core\Routing\Automaton
 *
 * @version v1.0.6 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
abstract class RouterLexer implements LexerSymbolInterface {

    /**
     * URI a ser analizada por el autómata.
     *
     * @var string
     */
    private readonly string $uri;

    /**
     * Posición del cursor del autómata.
     *
     * @var integer
     */
    private int $offset = 0;

    /**
     * Tamaño de la cadena a ser analizada.
     *
     * @var integer
     */
    private readonly int $size;

    /**
     * Tokens capturados de la ruta.
     *
     * Cada entrada contiene:
     * - `lexeme`    — Segmento extraído de la URI.
     * - `length`    — Longitud del lexema.
     * - `optional`  — indica si el parámetro es opcional.
     * - `tokentype` — clasificación del segmento ({@see TokenType}).
     * - `offset`    — Posición del cursor durante la emisión del token.
     *
     * @var array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int, method: Methods}>
     */
    private array $tokens = [];

    /**
     * El valor por defecto es `false`, pero si es `true`, significa que la ruta analizada
     * contiene parámetro.
     *
     * @var boolean $has_param
     */
    private bool $has_param = false;

    /**
     * Cadena de consulta capturada tras el símbolo `?`.
     *
     * Contiene lo que sigue al primer `?` que aparece fuera de un parámetro (`{param?}`), sin incluir el
     * propio `?`. Solo se captura si `$capture_query_string` es `true`. En caso contrario, o si la URI no
     * contiene ese símbolo, vale `null`.
     *
     * No se analiza aquí: se entrega tal cual al autómata especializado en cadenas de consulta.
     *
     * @var string|null
     */
    private readonly ?string $query_string;

    /**
     * Inicializa el autómata con la URI a analizar y el método HTTP asociado a la ruta.
     *
     * Normaliza la URI eliminando espacios en blanco al inicio y al final,
     * y calcula su tamaño en bytes para controlar el recorrido del cursor.
     *
     * Si `$capture_query_string` es `true`, al encontrar el símbolo `?` fuera de
     * un parámetro (`{param?}`), el autómata deja de analizar la URI y captura,
     * como un único lexema, todo lo que sigue después de ese símbolo. El propio
     * `?` no forma parte del lexema. Este lexema no se procesa aquí: se entrega
     * al autómata especializado en cadenas de consulta (query string), lo que
     * evita una segunda pasada sobre la URI.
     *
     * @param string       $uri                   URI del patrón de ruta a tokenizar.
     * @param Methods|null $method                Método HTTP con el que fue registrada la ruta.
     * @param bool         $capture_query_string  Si es `true`, captura como lexema lo que sigue
     *                                            al símbolo `?` (sin incluirlo). Por defecto: `false`.
     */
    public function __construct(
        string $uri,
        private readonly ?Methods $method = null,
        private readonly bool $capture_query_string = false
    ) {
        $this->uri = trim($uri);
        $this->size = \strlen($this->uri);
        $this->scanner();

        if (!isset($this->query_string)) {
            $this->query_string = null;
        }
    }

    /**
     * Escanea la URI ingresada por el usuario
     *
     * @return void
     */
    private function scanner(): void {
        while ($this->offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->uri[$this->offset];

            if ($byte !== self::WHITE_SPACE && $byte !== self::SLASH) {
                $this->tokenizer();

                if (isset($this->query_string)) {
                    break;
                }
            }

            $this->offset++;
        }
    }

    /**
     * Devuelve la ruta que será procesada por el analizador léxico.
     *
     * La ruta puede corresponder a la URI de una petición o a una ruta
     * previamente registrada, según el contexto en el que sea utilizado
     * el objeto.
     *
     * @return string Ruta destinada al procesamiento léxico.
     */
    public function get_uri(): string {
        return $this->uri;
    }

    /**
     * Descompone la URI en sus componentes en un token.
     *
     * Extrae el siguiente lexema desde la posición actual del cursor
     * hasta el próximo delimitador `/` o el final de la cadena,
     * lo clasifica y lo almacena en la lista de tokens.
     *
     * @return void
     */
    private function tokenizer(): void {

        /** @var integer $current_offset */
        $current_offset = $this->offset;

        /**
         * Posición final del cursor del autómata
         * 
         * @var int $end
         */
        $end = $this->next_delimiter(offset: $current_offset);

        /**
         * Tamaño del lexema.
         * 
         * @var int $length
         */
        $length = $end - ($current_offset);

        /** @var non-empty-string $lexeme */
        $lexeme = \substr($this->uri, $current_offset, $length);

        if ($lexeme === '') {
            $this->offset = $end;
            return;
        }

        /** @var boolean $is_optional */
        $is_optional = $this->is_optional($lexeme, $length);

        /**
         * Tipo de token asociado al componente de la ruta.
         * 
         * @var TokenType $tokentype
         */
        $tokentype = $this->get_tokentype($lexeme, $length);

        $this->tokens[] = [
            "lexeme" => $lexeme,
            "length" => $length,
            "optional" => $is_optional,
            "tokentype" => $tokentype,
            "offset" => $current_offset,
            "method" => $this->method
        ];

        if (!$this->has_param && $tokentype === TokenType::PARAM) {
            $this->has_param = true;
        }

        $this->offset = $end;
    }

    /**
     * Busca la posición del próximo delimitador `/` en la URI desde un offset dado.
     *
     * Recorre la URI byte a byte desde `$offset` hasta encontrar una barra diagonal
     * o el final de la cadena. Valida además que el signo `?` solo aparezca
     * inmediatamente antes de `}`, lanzando una excepción si la sintaxis es incorrecta.
     *
     * @param integer $offset Posición inicial del cursor en la URI.
     * @throws RouteException Si el signo `?` aparece en una posición inválida dentro del patrón.
     * @return integer Posición del próximo `/` encontrado, o {@see RouterLexer::$size} si no hay más delimitadores.
     */
    private function next_delimiter(int $offset): int {
        /** @var boolean $bracket_open */
        $bracket_open = false;

        while ($offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->uri[$offset];

            if ($byte === self::BRACKET_OPEN) {
                $bracket_open = true;
            }

            /** @var non-empty-string|null $pick */
            $pick = $this->uri[$offset + 1] ?? null;

            /**
             * Una pequeña observación: `self::OPTIONAL_PARAMETER` se usó original para indicar que el
             * símbolo de interrogación (`?`) fue utilizado para detectar parámetros opcionales, sin
             * embargo, si `$this->capture_query_string = true`, es decir, es `true`, entonces, el autómata
             * termina y carga la cadena en `$this->query_string`
             */
            if ($this->capture_query_string && $byte === self::OPTIONAL_PARAMETER) {
                $this->query_string = \substr($this->uri, $offset + 1, $this->size - $offset);
                return $offset;
            }

            if ($byte === self::SLASH) {
                $bracket_open = false;
                return $offset;
            }

            if ($byte === self::OPTIONAL_PARAMETER && $pick !== self::BRACKET_CLOSE && $bracket_open) {
                $fragment = \substr($this->uri, $offset, $this->size - $offset);
                $bracket_open = false;

                throw new RouteException(\sprintf(
                    "Se esperaba una llave de cierre (}) después del símbolo «?» (posición %d). En su lugar, se recibió «%s». Los parámetros opcionales deben tener el formato → «{parametro?}» en la definición de rutas.Ruta definida: «%s»",
                    $offset,
                    $fragment,
                    $this->uri
                ));
            }

            if ($bracket_open && $byte === self::BRACKET_CLOSE) {
                $bracket_open = false;
            }

            $offset++;
        }

        return $this->size;
    }

    /**
     * Determina si el parámetro es opcional.
     *
     * Un parámetro es opcional cuando el penúltimo carácter del lexema
     * es el signo `?` ({@see RouteLexerInterface::OPTIONAL_PARAMETER}).
     *
     * @param string  $lexeme Lexema extraído durante el análisis.
     * @param integer $length Tamaño en bytes del lexema.
     * @return boolean `true` si el parámetro es opcional, `false` en caso contrario.
     */
    private function is_optional(string &$lexeme, int &$length): bool {
        return ($lexeme[$length - 2] ?? '') === self::OPTIONAL_PARAMETER;
    }

    /**
     * Determina el tipo de token capturado en la URI.
     *
     * Clasifica el lexema como {@see TokenType::PARAM} si está delimitado
     * por llaves (`{` y `}`), o como {@see TokenType::TEXT_PLAIN} en caso
     * contrario.
     *
     * @param string  $lexeme Lexema capturado durante el análisis léxico.
     * @param integer $length Tamaño del lexema.
     * @return TokenType Tipo de token identificado.
     */
    private function get_tokentype(string &$lexeme, int $length): TokenType {

        /** @var int $end */
        $end = $length - 1;

        return ($lexeme[0] === self::BRACKET_OPEN && $lexeme[$end] === self::BRACKET_CLOSE)
            ? TokenType::PARAM
            : TokenType::TEXT_PLAIN;
    }

    /**
     * Devuelve todos los tokens capturados durante el análisis léxico.
     *
     * @return array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int, method: Methods}> Lista de tokens producidos por {@see scanner()}.
     */
    public function get_tokens(): array {
        return $this->tokens;
    }

    /**
     * Devuelve el método HTTP con el que registró la ruta
     *
     * @return Methods|null
     */
    public function get_method(): ?Methods {
        return $this->method;
    }

    /**
     * Deuelve un booleando que indica si la ruta analizada contiene al menos un parámetro. El valor por
     * defecto es `false`. Si es `true`, entonces, la ruta analizada contiene uno o más parámetros.
     *
     * @return boolean
     */
    public function has_param(): bool {
        return $this->has_param;
    }

    /**
     * Devuelve la cadena de consulta capturada durante el análisis léxico de la URI.
     *
     * Contiene los datos originales situados después del primer símbolo `?`
     * reconocido como delimitador de la cadena de consulta, sin incluir dicho
     * símbolo ni realizar el análisis o la decodificación de sus parámetros.
     *
     * Si la cadena de consulta está vacía o no fue capturada durante el análisis
     * léxico, devuelve `null`.
     *
     * @return string|null Cadena de consulta sin procesar, o `null` si está vacía o no existe.
     */
    public function get_query_string(): ?string {
        return $this->query_string === '' || $this->query_string === null
            ? null
            : $this->query_string;
    }
}
