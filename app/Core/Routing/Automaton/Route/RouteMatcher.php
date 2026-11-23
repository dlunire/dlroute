<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Core\Data\RouteData\Param;
use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;
use DLRoute\Core\Routing\Automaton\Route\TokenType;
use DLRoute\Errors\RouteException;

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
     * @var array<int, array{lexeme: non-empty-string, decoded_lexeme: non-empty-string}> $route_tokens_data
     */
    private readonly array $route_tokens_data;

    private readonly ?array $matchet_route;

    /**
     * Construye una instancia de RequestRouteLexer.
     *
     * Al construirse, además de recibir los tokens de la ruta a registrar, captura los tokens de la ruta
     * enviada por el cliente HTTP o la CLI.
     *
     * El constructor es privado para centralizar la creación de instancias mediante {@see self::new()}.
     * 
     * @param array<int, array{
     *      lexeme: non-empty-string,
     *      type: TokenType,
     *      is_optional: boolean,
     *      offset: int,
     *      uri: non-empty-string
     * }> $route_segments Tokens de la ruta a registrar, capturados durante el análisis léxico de su
     *                      definición.
     */
    private function __construct(
        private readonly array $route_segments = [],
    ) {
        $this->route_tokens_data = RequestURILexer::from()->get_route_data();
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
        $quantity_route_tokens = \count($this->route_tokens_data);

        /** @var integer $quantity_tokens */
        $quantity_tokens = \count($this->route_segments);

        /** @var array $params */
        $params = [];

        /** @var array $registered_route_segments Segmento de rutas registradas */
        $registered_route_segments = [];

        /** @var array $route_segments */
        $route_segments = [];

        /** @var array $params */
        $params = [];

        if ($quantity_route_tokens !== $quantity_tokens) {
            $this->matchet_route = null;
            return;
        }

        /** @var int $segments_quantity */
        $segments_quantity = 0;

        foreach ($this->route_segments as $key => $token) {
            /** @var Param $route_lexeme */
            $route_lexeme = Param::new(...$this->route_tokens_data[$key]);

            /** @var TokenType $toketype */
            $tokentype = $token['type'];

            /** @var non-empty-string $lexeme */
            $lexeme = $token['lexeme'];

            if ($route_lexeme->lexeme !== $lexeme && $tokentype === TokenType::TEXT_PLAIN) {
                break;
            }

            $registered_route_segments[] = $lexeme;

            /** @var non-empty-string $param_name */
            $param_name = $tokentype === TokenType::PARAM
                ? \trim(string: $lexeme, characters: '{}')
                : $lexeme;

            if (($params[$param_name] ?? null) !== null) {
                throw new RouteException(
                    message: "El parámetro '{$param_name}' está duplicado en la posición «{$token['offset']}» de la ruta «{$token['uri']}».",
                    code: 500
                );
            }

            $segments_quantity++;
            if ($tokentype !== TokenType::PARAM) continue;
            $params[$param_name] = $route_lexeme;
        }

        if ($segments_quantity !== $quantity_tokens) {
            $this->matchet_route = null;
            return;
        };

        $this->matchet_route = [
            "route" => RequestURILexer::from()->get_route(),
            "registered_route" => "/" . join(array: $registered_route_segments, separator: "/"),
            "params" => $params
        ];

        echo PHP_EOL;
        echo "ROUTE:  ";
        print_r($this->matchet_route);
    }
}
