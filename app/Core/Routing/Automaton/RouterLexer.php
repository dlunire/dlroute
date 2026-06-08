<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

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
 * $lexer = new RouterLexer('/users/{id}/{slug?}');
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
class RouterLexer implements RouteLexerInterface {

    /**
     * URI a ser analizada por el autómata.
     *
     * @var string
     */
    private static string $uri;

    /**
     * Posición del cursor del autómata.
     *
     * @var integer
     */
    private static int $offset = 0;

    /**
     * Tamaño de la cadena a ser analizada.
     *
     * @var integer
     */
    private static int $size = 0;

    /**
     * Tokens capturados de la ruta.
     *
     * Cada entrada contiene:
     * - `lexeme`    — segmento extraído de la URI.
     * - `optional`  — indica si el parámetro es opcional.
     * - `tokentype` — clasificación del segmento ({@see TokenType}).
     *
     * @var array<int, array{lexeme: string, option: boolean, tokentype: TokenType}>
     */
    private static array $tokens = [];

    public function __construct(string $uri) {
        self::$uri = trim($uri);
        self::$size = \strlen(self::$uri);
    }

    /**
     * Escanea la URI ingresada por el usuario
     *
     * @return void
     */
    public function scanner(): void {

        while (self::$offset < self::$size) {
            /** @var non-empty-string $byte */
            $byte = self::$uri[self::$offset];

            if ($byte !== self::WHITE_SPACE) {
                $this->tokenizer();
            }

            self::$offset++;
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
        $current_offset = self::$offset;

        $end = \strpos(
            haystack: self::$uri,
            needle: self::SLASH,
            offset: $current_offset
        );

        if ($end === false) {
            $end = self::$size;
        }

        /**
         * Tamaño del lexema.
         * 
         * @var int $length
         */
        $length = $end - ($current_offset);

        /** @var non-empty-string $lexeme */
        $lexeme = \substr(self::$uri, $current_offset, $length);

        if ($lexeme === '') {
            self::$offset = $end;
            return;
        }

        /** @var boolean $is_optional */
        $is_optional = $this->is_optional($lexeme, $length);

        self::$tokens[] = [
            "lexeme" => $lexeme,
            "optional" => $is_optional,
            "tokentype" => $this->get_tokentype($lexeme, $length)
        ];

        self::$offset = $end;
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
     * @return array<int, array{lexeme: string, option: boolean, tokentype: TokenType}> Lista de tokens producidos por {@see scanner()}.
     */
    protected function get_tokens(): array {
        return self::$tokens;
    }
}