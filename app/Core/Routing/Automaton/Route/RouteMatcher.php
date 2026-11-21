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
     * @param array<int, nom-empty-string> $route_segments Tokens de la ruta a registrar, capturados durante el análisis léxico de su
     *                      definición.
     */
    private function __construct(
        private readonly array $route_segments = [],
    ) {
        $this->route_tokens = RequestURILexer::from()->get_route_tokens();
        $this->load_data();
    }

    /**
     * Crea una instancia de {@see self}
     *
     * @param array $route_segments Segmentos de rutas que permitirán 
     * @return static
     */
    public static function new(array $route_segments): static {
        return new static(route_segments: $route_segments);
    }

    private function load_data(): void {

        /** @var integer $quantity_route_tokens */
        $quantity_route_tokens = \count($this->route_tokens);

        /** @var integer $quantity_tokens */
        $quantity_tokens = \count($this->route_segments);

        /** @var array $params */
        $params = [];

        /** @var array $segments Segmento de rutas */
        $segments = [];

        $route_tokens = $this->route_tokens;

        foreach ($this->route_segments as $key => $token) {
            $route_lexeme = $route_tokens[$key] ?? null;
            // $lexeme = $token['lexeme'];

            // echo "LEXEME: {$token}\n";
            continue;

            if ($route_lexeme !== $lexeme && $token['tokentype'] === RouteTokenType::TEXT_PLAIN) {
                break;
            }

            $segments[$token['lexeme']] = $route_lexeme;
        }
    }
}
