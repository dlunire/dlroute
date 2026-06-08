<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

use DLRoute\Interfaces\Routing\RouteLexerInterface;

class RouterLexer implements RouteLexerInterface {

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
     * Tokens capturados de la ruta
     *
     * @var array
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
    public function scanner() {

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
     * Determina si el parámetro es opcional
     *
     * @param string $lexeme Lexeme extraído a ser extraído.
     * @param integer $length Tamaño en bytes del lexema.
     * @return boolean
     */
    private function is_optional(string &$lexeme, int &$length): bool {
        return ($lexeme[$length - 2] ?? '') === self::OPTIONAL_PARAMETER;
    }

    /**
     * Determina el tipo de token capturado en la URI.
     *
     * @param string $lexeme Lexema capturado durante el análisis léxico.
     * @param integer $length Tamaño del lexema.
     * @return TokenType
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
     * @return array
     */
    protected function get_tokens(): array {
        return self::$tokens;
    }
}
