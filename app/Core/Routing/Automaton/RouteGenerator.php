<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

use DLRoute\Errors\RouteException;

final class RouteGenerator extends RouterLexer {

    /**
     * Rutas generadas a partir de parámetros opcionales.
     * 
     * @var non-empty-string[]
     */
    private array $routes = [];

    public function __construct(string $uri) {
        parent::__construct($uri);
        $this->scanner();
    }

    public function generate(): void {
        
        /** @var array{lexeme: string, length: int, optional: bool, tokentype: TokenType, offset: int}[] $tokens */
        $tokens = $this->get_tokens();

        /** @var array $buffer */
        $buffer = [];

        foreach ($tokens as $token) {
            /** @var non-empty-string $lexeme */
            $lexeme = \strval($token['lexeme']);

            /** @var int $length */
            $length = \intval($token['length']);

            /** @var boolean $optional */
            $optional = \boolval($token['optional']);

            /** @var TokenType $tokentype */
            $tokentype = $token['tokentype'];

            /** @var int $offset */
            $ofsset = \intval($token['offset']);

            if (!$tokentype instanceof TokenType) {
                throw new RouteException("El token «{$lexeme}» es inesperado en la posición «{$ofsset}»", 500);
            }

            $this->remove_param($lexeme, $length);

            if ($optional && $tokentype === TokenType::PARAM) {
                $this->routes[] = self::SLASH . implode(self::SLASH, $buffer);
            }

            $buffer[] = $lexeme;
        }

        $this->routes[] = self::SLASH . implode(self::SLASH, $buffer);

        print_r($this->routes);
    }

    /**
     * Remueve el parámetro del lexema.
     *
     * @param string $lexeme Lexema a ser depurado.
     * @param integer $length Longitud de byte del lexema.
     * @return void
     */
    private function remove_param(string &$lexeme, int $length): void {
        $test = "";
        if (self::OPTIONAL_PARAMETER === $lexeme[$length - 2]) {
            $lexeme = \substr($lexeme, 0, $length - 2) . "}";
        }
    }


}