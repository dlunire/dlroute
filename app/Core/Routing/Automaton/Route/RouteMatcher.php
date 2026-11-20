<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;
use DLRoute\Core\Routing\Automaton\Route\TokenType as RouteTokenType;
use DLRoute\Enums\Methods;
use DLRoute\Requests\DLOutput;

/**
 * Analizador léxico de la ruta solicitada.
 *
 * Maneja dos conjuntos de tokens:
 *
 * - `$tokens`: los de la ruta a registrar, recibidos por el constructor.
 * - `$route_tokens`: los de la ruta enviada por el cliente HTTP o la CLI, obtenidos de {@see RequestURILexer}.
 *
 * Las instancias se crean exclusivamente mediante {@see self::new()}.
 */
final class RouteMatcher {

    private array $data = [];

    /**
     * Tokens de la ruta enviada por el cliente HTTP o la interfaz de línea de comandos (CLI, por sus
     * siglas en inglés).
     *
     * Se obtienen una sola vez al construir la instancia, mediante {@see RequestURILexer::get_route_tokens()}.
     *
     * @var non-empty-string[] $route_tokens
     */
    private readonly array $route_tokens;

    /**
     * Construye una instancia de RequestRouteLexer.
     *
     * Al construirse, además de recibir los tokens de la ruta a registrar, captura los tokens de la ruta
     * enviada por el cliente HTTP o la CLI.
     *
     * El constructor es privado para centralizar la creación de instancias mediante {@see self::new()}.
     *
     * @param array<int, array{
     *      lexeme: string,
     *      decoded_lexeme: string,
     *      length: int,
     *      optional: bool,
     *      tokentype: RouteTokenType,
     *      offset: int,
     *      method: Methods
     * }> $tokens Tokens de la ruta a registrar, capturados durante el análisis léxico de su
     *                      definición.
     */
    private function __construct(
        private readonly array $tokens = [],
    ) {
        $this->route_tokens = RequestURILexer::from()->get_route_tokens();
        $this->load_data();
    }

    /**
     * Crea una nueva instancia de RequestRouteLexer.
     *
     * Este método proporciona un punto de construcción alternativo mediante una llamada estática, evitando
     * depender de la sintaxis de instanciación directa de objetos sin paréntesis, disponible en versiones
     * recientes de PHP.
     *
     * @param array<int, array{
     *      lexeme: string,
     *      decoded_lexeme: string,
     *      length: int,
     *      optional: bool,
     *      tokentype: RouteTokenType,
     *      offset: int,
     *      method: Methods
     * }> $tokens
     *
     * @return static Nueva instancia de RequestRouteLexer.
     */
    public static function new(array $tokens): static {
        return new static(tokens: $tokens);
    }

    private function load_data(): void {

        /** @var integer $quantity_route_tokens */
        $quantity_route_tokens = \count($this->route_tokens);

        /** @var integer $quantity_tokens */
        $quantity_tokens = \count($this->tokens);

        /** @var array $params */
        $params = [];

        /** @var array $segments Segmento de rutas */
        $segments = [];

        $route_tokens = $this->route_tokens;

        foreach ($this->tokens as $key => $token) {
            $route_lexeme = $route_tokens[$key] ?? null;
            $lexeme = $token['lexeme'];

            if ($route_lexeme !== $lexeme && $token['tokentype'] === RouteTokenType::TEXT_PLAIN) {
                break;
            }

            $segments[$token['lexeme']] = $route_lexeme;
        }

        // echo "Segmentos de rutas que coinciden: \n";
        // echo DLOutput::to_json(
        //     content: $segments,
        //     pretty: true
        // );
        // echo PHP_EOL;
    }
}
