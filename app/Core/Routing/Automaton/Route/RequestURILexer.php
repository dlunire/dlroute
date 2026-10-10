<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Core\Routing\Automaton\Route\RouterLexer;

/**
 * Analizador léxico para la URI de una petición entrante.
 *
 * Extiende `RouterLexer` para tokenizar la URI solicitada y reconstruir
 * una ruta procesada a partir de los lexemas obtenidos.
 *
 * @package DLRoute\Core\Routing\Automaton\Route
 * @version 1.0.0
 * @license AGPL-3.0-or-later
 */
final class RequestURILexer extends RouterLexer {

    /**
     * Inicializa el analizador léxico con la URI de la petición.
     *
     * @param string $uri URI de la petición a tokenizar.
     * @return void
     */
    public function __construct(string $uri) {
        parent::__construct($uri);
    }

    /**
     * Devuelve la ruta reconstruida a partir de los lexemas de los tokens.
     *
     * Recorre los tokens generados por el lexer, concatena sus lexemas
     * separados por `/` y antepone una barra inicial.
     *
     * Ejemplo: una URI con los lexemas `api`, `users` y `42` produce
     * `/api/users/42`.
     *
     * Si no hay tokens, devuelve `/`.
     *
     * @return string Ruta procesada, siempre con `/` inicial.
     */
    public function get_processed_route(): string {
        /** Tokens de la URI */
        $tokens = $this->get_tokens();

        echo "\$tokens: ";
        print_r($tokens);
        echo "\n";

        /** @var string[] $buffer Lexemas de cada segmento de ruta */
        $buffer = [];

        foreach ($tokens as $token) {
            $buffer[] = $token['lexeme'];
        }

        return "/" . join(
            separator: "/",
            array: $buffer
        );
    }
}
