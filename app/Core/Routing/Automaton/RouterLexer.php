<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

use DLRoute\Interfaces\Routing\RouteLexerInterface;

final class RouterLexer implements RouteLexerInterface {

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

        print_r(self::$tokens);
    }

    private function tokenizer_params() {

        /** @var integer $current_offset */
        $current_offset = self::$offset;

        /** @var int|false $end */
        $end = \strpos(
            haystack: self::$uri,
            needle: self::BRACKET_CLOSE,
            offset: $current_offset
        );

        if ($end === false) {
            $end = self::$size;
        }

        /**
         * Tamaño del lexema a ser extraído.
         * 
         * @var int $length
         */
        $length = $end - ($current_offset - 1);

        /** @var non-empty-string $lexeme */
        $lexeme = \substr(self::$uri, $current_offset, $length);

        /** @var boolean $is_optional */
        $is_optional = $this->is_optional($lexeme, $length);

        self::$tokens[] = [
            "lexeme" => \substr(self::$uri, $current_offset, $length),
            "optional" => $is_optional,
            "content" => self::$uri
        ];

        self::$offset = $end;
    }

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

        /** @var boolean $is_optional */
        $is_optional = $this->is_optional($lexeme, $length);

        self::$tokens[] = [
            "lexeme" => \substr(self::$uri, $current_offset, $length),
            "optional" => $is_optional,
            "content" => self::$uri
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
}
