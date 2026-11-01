<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Config\RealPath;
use DLRoute\Core\Data\RouteData\RouteResolution;
use DLRoute\Core\Routing\Automaton\Route\RouterLexer;
use DLRoute\Enums\Methods;

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
     * Instancia de clase
     *
     * @var self|null $instance
     */
    private static ?self $instance = null;

    /**
     * Resultado de la resolución de la URI de la petición.
     *
     * Permanece en `null` mientras no se haya completado la resolución.
     *
     * @var RouteResolution|null $route_resolution
     */
    private ?RouteResolution $route_resolution = null;

    /**
     * Analiza la URI durante su instancia
     */
    private function __construct() {
        global $argv;

        parent::__construct(
            uri: PHP_SAPI === 'cli'
                ? ($argv[1] ?? "/")
                : ($_SERVER['REQUEST_URI'] ?? "/"),
            method: null,
            capture_query_string: true
        );
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

    /**
     * Devuelve la URI procesada por el autómata.
     *
     * Es la ruta reconstruida ({@see RequestURILexer::get_processed_route()})
     * seguida, si hay una cadena de consulta no vacía, del símbolo `?` y de esa
     * cadena sin procesar ni decodificar. Si no hay cadena de consulta, o está
     * vacía (`/users?`), devuelve solo la ruta.
     *
     * Ejemplo: `//users//42?page=2` produce `/users/42?page=2`.
     *
     * @return non-empty-string URI procesada, siempre con `/` inicial.
     */
    public function get_processed_uri(): string {
        /** @var non-empty-string|null $query_string */
        $query_string = $this->get_query_string();

        return $this->get_processed_route() . (
            $query_string === null
            ? ''
            : "?{$query_string}"
        );
    }

    /**
     * Devuelve el directorio base de ejecución de la aplicación.
     *
     * @return non-empty-string
     */
    public function get_dir(): string {
        /** @var RealPath $realpath */
        $realpath = RealPath::from();

        /** @var non-empty-string $entry_point_dir */
        $entry_point_dir = $realpath->get_entry_point();

        $tokens = $this->get_tokens();

        /** @var non-empty-string[] $segments Segmentos de ruta */
        $segments = [];

        foreach ($tokens as $token) {
            /** @var non-empty-string $segment */
            $segment = $token['lexeme'];

            /** @var non-empty-string $path */
            $path = $entry_point_dir . DIRECTORY_SEPARATOR . $segment;

            if (!\is_dir($path)) {
                break;
            }

            $segments[] = $segment;
            $entry_point_dir = $path;
        }

        return "/" . \implode(
            separator: "/",
            array: $segments
        );
    }

    private function load_route_resolution(): void {
        /** @var RealPath $realpath */
        $realpath = RealPath::from();

        /** @var non-empty-string $entry_point_dir */
        $entry_point_dir = $realpath->get_entry_point();

        $tokens = $this->get_tokens();

        /** @var non-empty-string[] $dir_segments Segmentos de ruta */
        $dir_segments = [];

        /** @var non-empty-string[] $route_segments */
        $route_segments = [];

        /** @var boolean $resolve_dir */
        $resolve_dir = true;

        foreach ($tokens as $token) {
            /** @var non-empty-string $segment */
            $segment = $token['lexeme'];

            /** @var non-empty-string $path */
            $path = $entry_point_dir . DIRECTORY_SEPARATOR . $segment;

            if ($resolve_dir) {
                $route_segments[] = $segment;
                continue;
            }

            if (!\is_dir($path)) {
                continue;
            }

            $dir_segments[] = $segment;
            $entry_point_dir = $path;
        }

        $this->route_resolution = new RouteResolution(
            uri: $this->get_processed_uri(),
            dir: "/" . implode("/", $dir_segments),
            route: "/" . implode("/", $route_segments)
        );
    }

    /**
     * Obtiene la instancia única del analizador léxico de la URI de petición.
     *
     * Implementa el patrón Singleton mediante inicialización diferida (Lazy
     * Initialization). Si todavía no existe una instancia de la clase, la
     * construye y la almacena para su reutilización durante el ciclo de vida
     * de la aplicación.
     *
     * Las llamadas posteriores devuelven la instancia previamente construida,
     * evitando nuevas inicializaciones y análisis repetidos de la URI.
     *
     * @return static Instancia única de RequestURILexer.
     */
    public static function from(): static {
        if (!(static::$instance instanceof static)) {
            static::$instance = new static;
        }

        return self::$instance;
    }
}
