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

        $this->load_route_resolution();
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
     * Devuelve el directorio lógico base de ejecución de la aplicación, determinado durante el proceso de
     * resolución de la URI.
     *
     * El directorio se obtiene de la instancia de {@see RouteResolution} y representa los segmentos iniciales
     * de la URI que corresponden a directorios físicos existentes desde el punto de entrada.
     *
     * La ruta devuelta utiliza `/` como separador y comienza siempre desde la raíz lógica (`/`).
     *
     * @return non-empty-string Directorio base de ejecución de la aplicación.
     */
    public function get_dir(): string {
        return $this->route_resolution->dir;
    }

    /**
     * Devuelve la ruta lógica que debe resolver el enrutador.
     *
     * Esta ruta se obtiene de la instancia de {@see RouteResolution} y corresponde a los segmentos de la
     * URI que no forman parte de los directorios físicos existentes desde el punto de entrada.
     *
     * La ruta devuelta utiliza `/` como separador y comienza siempre desde la raíz lógica (`/`).
     *
     * @return non-empty-string Ruta lógica de la solicitud.
     */
    public function get_route(): string {
        return $this->route_resolution->route;
    }



    /**
     * Resuelve la URI de la solicitud en relación con el punto de entrada físico.
     *
     * Este método determina qué segmentos de la URI corresponden a directorios físicos existentes a partir
     * del directorio del punto de entrada y cuáles constituyen la ruta lógica que debe resolver DLRoute.
     *
     * La resolución se realiza de forma progresiva:
     *
     * - Los segmentos iniciales que correspondan a directorios físicos existentes
     *   se incorporan a `dir_segments`.
     * - Al encontrar el primer segmento que no corresponda a un directorio físico,
     *   finaliza la resolución de directorios.
     * - El segmento que interrumpe la resolución y todos los segmentos posteriores
     *   se incorporan a `route_segments`, independientemente de que existan
     *   directorios físicos con esos nombres.
     *
     * La resolución física comienza desde el directorio real del punto de entrada, obtenido mediante
     * {@see RealPath::get_entry_point()}.
     *
     * Finalmente, construye una instancia de {@see RouteResolution} con la URI procesada, el directorio lógico
     * resuelto y la ruta lógica resultante.
     *
     * Esta separación permite distinguir el contexto físico de ejecución de la ruta lógica que debe procesar
     * el enrutador, sin derivar el prefijo de montaje de la URL a partir del sistema de archivos.
     *
     * @return void
     */
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

                if (\is_dir($path)) {
                    $dir_segments[] = $segment;
                    $entry_point_dir = $path;

                    continue;
                } else {
                    $resolve_dir = false;
                }
            }

            $route_segments[] = $segment;
        }

        $this->route_resolution = new RouteResolution(
            uri: $this->get_processed_uri(),
            dir: "/" . implode("/", $dir_segments),
            route: "/" . implode("/", $route_segments)
        );
    }

    /**
     * Obtiene la resolución de la ruta de la solicitud.
     *
     * Devuelve la instancia de {@see RouteResolution} que contiene la información obtenida durante el
     * proceso de resolución, incluyendo los segmentos correspondientes al directorio físico y a la
     * ruta lógica.
     *
     * @return RouteResolution Resolución de la ruta de la solicitud.
     */
    public function get_route_resolution(): RouteResolution {
        return $this->route_resolution;
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
