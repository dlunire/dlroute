<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Server\DLServer;

/**
 * Analiza léxicamente la URI de la petición HTTP actual.
 *
 * Extiende el analizador léxico base ({@see RouterLexer}) para tokenizar la ruta enviada por el cliente
 * HTTP en el momento de la instanciación, reutilizando el mismo autómata utilizado para analizar las rutas
 * registradas por el programador.
 *
 * A diferencia de una ruta registrada, la URI aquí analizada nunca contiene segmentos delimitados por llaves:
 * son los valores reales enviados por el cliente, no parámetros dinámicos.
 *
 * @package DLRoute\Core\Routing\Automaton\Route
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RequestRouterRequest extends RouterLexer {

    /**
     * Tokens capturados con la metadata de la ruta registrada por el programador.
     *
     * @var array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int}> $route_tokens
     */
    private readonly array $route_tokens;

    /**
     * Cantidad de tokens de la ruta envianda por el cliente HTTP
     *
     * @var int $request_token_quantity
     */
    private readonly int $request_token_quantity;

    /**
     * Cantidad de tokens de rutas.
     *
     * @var integer $route_tokens_quantity
     */
    private readonly int $route_tokens_quantity;

    /**
     * Construye el analizador léxico de la petición HTTP actual.
     *
     * Toma la ruta de la petición actual mediante {@see DLServer::get_route()}
     * y ejecuta de inmediato el análisis léxico sobre ella.
     * 
     * @param array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int}> $tokens Tokens de la ruta registrada por el desarrollador.
     */
    public function __construct(array $tokens) {
        parent::__construct(DLServer::get_route());
        $this->route_tokens = $tokens;
        $this->scanner();

        $this->route_tokens_quantity = \count($this->route_tokens);

        $this->get_params_value();
        echo "\n";
    }

    /**
     * Devuelve los segmentos de la URI de la petición HTTP actual.
     *
     * Extrae únicamente el lexema de cada token capturado durante el análisis
     * léxico, descartando el resto de los metadatos del token.
     *
     * @return array<string> Segmentos de la ruta de la petición actual, en el
     * orden en que aparecen en la URI.
     */
    public function get_request_tokens(): array {
        /** @var array $request_tokens Tokens capturados de la URI enviada por el cliente HTTP */
        $request_tokens = $this->get_tokens();

        /** @var array<string> $tokens Lexemas extraídos de cada token */
        $tokens = [];

        foreach ($request_tokens as $token) {
            $tokens[] = $token['lexeme'];
        }

        $this->request_token_quantity = \count($tokens);

        return $tokens;
    }

    public function get_params_value(): array {
        $params = [];

        /** @var array<string> $request_tokens */
        $request_tokens = $this->get_request_tokens();

        if ($this->request_token_quantity !== $this->route_tokens_quantity) {
            return $params;
        }

        foreach ($this->route_tokens as $key => $token) {
            /** @var TokenType $type */
            $type = $token['tokentype'];

            /** @var non-empty-string $lexeme */
            $lexeme = $token['lexeme'];

            $this->remove_bracket($lexeme);

            if ($type !== TokenType::PARAM) continue;
            $params[$lexeme] = $request_tokens[$key] ?? null;
        }

        return $params;
    }

    /**
     * Remueve las llaves y signos de interrogación del token marcado como parámetro
     *
     * @param string $lexeme Lexema capturado durante el análisis léxico de la ruta.
     * @return void
     */
    private function remove_bracket(string &$lexeme): void {
        $lexeme = \trim($lexeme, " \n\r\t\v\x00{}\?");
    }
}
