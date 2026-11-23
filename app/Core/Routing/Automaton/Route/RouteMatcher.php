<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Core\Data\RouteData\Param;
use DLRoute\Core\Data\RouteData\RouteContext;
use DLRoute\Core\Data\RouteData\RouteParam;
use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;
use DLRoute\Core\Routing\Automaton\Route\TokenType;
use DLRoute\Errors\RouteException;

/**
 * Compara la ruta solicitada con los segmentos de una ruta registrada.
 *
 * Maneja dos conjuntos de datos:
 *
 * - `$route_segments`: los tokens de la ruta a registrar, recibidos por el constructor.
 * - `$route_tokens_data`: los datos de la ruta enviada por el cliente HTTP o la CLI,
 *   obtenidos de {@see RequestURILexer}.
 *
 * Al construirse, la comparación se ejecuta una sola vez. Si la ruta solicitada coincide con
 * la registrada, se almacena el resultado de la coincidencia, que incluye los parámetros
 * capturados como {@see RouteParam}, cada uno con instancias de {@see Param} completas
 * (nombre, valor original y valor decodificado). Si no coincide, ese resultado es `null`.
 *
 * El resultado se consulta mediante {@see self::get_context()}, que lo expone como un
 * {@see RouteContext} tanto si hubo coincidencia como si no.
 *
 * Las instancias se crean exclusivamente mediante {@see self::new()}.
 */
final class RouteMatcher {

    /**
     * Datos de los segmentos de la ruta enviada por el cliente HTTP o la interfaz de línea de
     * comandos (CLI, por sus siglas en inglés).
     *
     * Se obtienen una sola vez al construir la instancia, mediante
     * {@see RequestURILexer::get_route_data()}.
     *
     * @var array<int, array{lexeme: non-empty-string, decoded_lexeme: non-empty-string}>
     */
    private readonly array $route_tokens_data;

    /**
     * Resultado de la coincidencia entre la ruta solicitada y la registrada.
     *
     * Es `null` cuando la cantidad de segmentos difiere, cuando un segmento de texto plano
     * no coincide, o cuando no se alcanza a procesar la totalidad de los segmentos. En caso
     * de coincidencia contiene:
     *
     * - `route`: la ruta solicitada.
     * - `registered_route`: la ruta registrada reconstruida a partir de sus segmentos.
     * - `params`: los parámetros capturados, como {@see RouteParam}.
     *
     * Se inicializa en {@see self::load_data()}, invocado desde el constructor, y se expone
     * mediante {@see self::get_context()}.
     *
     * @var array{route: string, registered_route: string, params: RouteParam}|null
     */
    private readonly ?array $matchet_route;

    /**
     * Construye una instancia de {@see self}.
     *
     * Al construirse, además de recibir los tokens de la ruta a registrar, captura los datos
     * de la ruta enviada por el cliente HTTP o la CLI y ejecuta la comparación.
     *
     * El constructor es privado para centralizar la creación de instancias mediante
     * {@see self::new()}.
     *
     * @param array<int, array{
     *      lexeme: non-empty-string,
     *      type: TokenType,
     *      is_optional: bool,
     *      offset: int,
     *      uri: non-empty-string
     * }> $route_segments Tokens de la ruta a registrar, capturados durante el análisis léxico
     *                    de su definición.
     *
     * @throws RouteException Si un parámetro aparece duplicado en la ruta registrada.
     */
    private function __construct(
        private readonly array $route_segments = [],
    ) {
        $this->route_tokens_data = RequestURILexer::from()->get_route_data();
        $this->load_data();
    }

    /**
     * Crea una instancia de {@see self}.
     *
     * @param array<int, array{
     *      lexeme: non-empty-string,
     *      type: TokenType,
     *      is_optional: bool,
     *      offset: int,
     *      uri: non-empty-string
     * }> $route_segments Tokens de la ruta a registrar.
     *
     * @return static Nueva instancia, con la comparación ya resuelta.
     *
     * @throws RouteException Si un parámetro aparece duplicado en la ruta registrada.
     */
    public static function new(array $route_segments): static {
        return new static(route_segments: $route_segments);
    }

    /**
     * Compara los segmentos registrados con los de la ruta solicitada y establece
     * {@see self::$matchet_route}.
     *
     * El proceso es el siguiente:
     *
     * 1. Si la cantidad de segmentos difiere, no hay coincidencia.
     * 2. Se recorren los segmentos registrados: un segmento de texto plano debe coincidir
     *    literalmente con el solicitado; si no, se interrumpe la comparación.
     * 3. Cada segmento de tipo parámetro se captura como {@see Param} con su nombre
     *    (sin llaves), su valor original y su valor decodificado.
     * 4. Solo hay coincidencia si se procesaron todos los segmentos.
     *
     * @return void
     *
     * @throws RouteException Con código 500 si un parámetro se repite en la ruta registrada.
     */
    private function load_data(): void {

        /** @var integer $quantity_route_tokens */
        $quantity_route_tokens = \count($this->route_tokens_data);

        /** @var integer $quantity_tokens */
        $quantity_tokens = \count($this->route_segments);

        /** @var array $params */
        $params = [];

        /** @var array $registered_route_segments Segmento de rutas registradas */
        $registered_route_segments = [];

        if ($quantity_route_tokens !== $quantity_tokens) {
            $this->matchet_route = null;
            return;
        }

        /** @var int $segments_quantity */
        $segments_quantity = 0;

        foreach ($this->route_segments as $key => $token) {
            $route_data = $this->route_tokens_data[$key];

            /** @var TokenType $toketype */
            $tokentype = $token['type'];

            /** @var non-empty-string $lexeme */
            $lexeme = $token['lexeme'];

            if ($route_data['lexeme'] !== $lexeme && $tokentype === TokenType::TEXT_PLAIN) {
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

            $params[$param_name] = Param::new(
                name: $param_name,
                value: $route_data['lexeme'],
                decoded_value: $route_data['decoded_lexeme']
            );
        }

        if ($segments_quantity !== $quantity_tokens) {
            $this->matchet_route = null;
            return;
        };

        $this->matchet_route = [
            "route" => RequestURILexer::from()->get_route(),
            "registered_route" => "/" . join(
                array: $registered_route_segments,
                separator: "/"
            ),
            "params" => RouteParam::new(fields: $params)
        ];
    }

    /**
     * Devuelve el contexto de la ruta.
     *
     * Siempre devuelve un {@see RouteContext}:
     *
     * - Si la ruta solicitada coincidió con la registrada, el contexto contiene la ruta
     *   solicitada, la ruta registrada y los parámetros capturados.
     * - Si no hubo coincidencia, el contexto contiene la ruta base `/`, `registered_route`
     *   en `null` y un {@see RouteParam} sin parámetros, de modo que cualquier parámetro
     *   consultado devuelve un {@see Param} con valores `null`.
     *
     * Cada llamada construye un nuevo {@see RouteContext} a partir del resultado ya resuelto;
     * no repite la comparación.
     *
     * @return RouteContext Contexto de la ruta resuelta, o un contexto con la ruta base si no
     *                      hubo coincidencia.
     */
    public function get_context(): RouteContext {
        $matchet_route = $this->matchet_route ?? [
            "params" => RouteParam::new(),
            "route" => "/",
            "registered_route" => null
        ];

        return RouteContext::new(...$matchet_route);
    }
}
