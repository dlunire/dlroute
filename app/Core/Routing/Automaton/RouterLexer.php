<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\Routing\RouteLexerInterface;

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
 * @license MIT
 */
abstract class RouterLexer implements RouteLexerInterface {

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
     * @var array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int}>
     */
    private array $tokens = [];

    /**
     * Inicializa el autómata con la URI a analizar.
     *
     * Normaliza la URI eliminando espacios en blanco al inicio y al final,
     * y calcula su tamaño en bytes para controlar el recorrido del cursor.
     *
     * @param string $uri URI del patrón de ruta a tokenizar.
     */
    public function __construct(string $uri) {
        $this->uri = trim($uri);
        $this->size = \strlen($this->uri);
    }

    /**
     * Escanea la URI ingresada por el usuario
     *
     * @return void
     */
    public function scanner(): void {

        while ($this->offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->uri[$this->offset];

            if ($byte !== self::WHITE_SPACE) {
                $this->tokenizer();
            }

            $this->offset++;
        }
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

        $end = $this->next_delimiter($current_offset);

        if ($end === false) {
            $end = $this->size;
        }

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

        $this->tokens[] = [
            "lexeme" => $lexeme,
            "length" => $length,
            "optional" => $is_optional,
            "tokentype" => $this->get_tokentype($lexeme, $length),
            "offset" => $current_offset
        ];

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

        while ($offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->uri[$offset];

            /** @var non-empty-string|null $pick */
            $pick = $this->uri[$offset + 1] ?? null;

            if ($byte === self::SLASH) {
                return $offset;
            }

            if ($byte === self::OPTIONAL_PARAMETER && self::BRACKET_CLOSE !== $pick) {
                throw new RouteException(
                    "La sintaxis de la ruta es incorrecta a partir de la posición «{$offset}». Subcadena: «"
                        . \substr($this->uri, $offset, $this->size - $offset) . "»"
                );
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
     * @return array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int}> Lista de tokens producidos por {@see scanner()}.
     */
    protected function get_tokens(): array {
        return $this->tokens;
    }
}
