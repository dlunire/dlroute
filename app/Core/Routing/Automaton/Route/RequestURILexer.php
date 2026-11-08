<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Config\RealPath;
use DLRoute\Core\Data\RouteData\ProcessedURI;
use DLRoute\Core\Data\RouteData\RouteResolution;
use DLRoute\Core\Routing\Automaton\QueryParams\QueryParamComposer;
use DLRoute\Core\Routing\Automaton\Route\RouterLexer;
use DLRoute\Errors\RouteException;

/**
 * Analizador léxico para la URI de una petición entrante.
 *
 * Extiende `RouterLexer` para tokenizar la URI solicitada y reconstruir
 * una ruta procesada a part    ir de los lexemas obtenidos.
 *
 * @package DLRoute\Core\Routing\Automaton\Route
 * @version 1.0.0
 * @license AGPL-3.0-or-later
 */
final class RequestURILexer extends RouterLexer {

    /**
     * Tokens de la URI sin la cadena de consultas
     *
     * @var array $uri_tokens;
     */
    private readonly array $uri_tokens;

    /**
     * Contiene la URI procesada de la petición (sea una HTTP o por CLI)
     *
     * @var ProcessedURI $processed_uri
     */
    private readonly ProcessedURI $processed_uri;

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
     * Constructor privado. Inicializa el enrutador resolviendo la URI según el entorno de ejecución:
     *
     * - **CLI** (`PHP_SAPI === 'cli'`): la ruta se obtiene de las opciones `-r` o `--route` (véase `get_options()`).
     * - **Servidor web**: se usa `$_SERVER['REQUEST_URI']`, o `/` si no está definida.
     *
     * Se declara privado de forma intencional: la instancia se crea a través del punto de entrada de la clase,
     * tanto para peticiones HTTP como para ejecución por terminal.
     *
     * Tras construir la clase padre, carga la resolución de rutas con `load_route_resolution()`.
     */
    private function __construct() {

        parent::__construct(
            uri: PHP_SAPI === 'cli'
                ? $this->get_options()
                : ($_SERVER['REQUEST_URI'] ?? "/"),
            method: null,
            capture_query_string: true
        );

        $this->uri_tokens = $this->get_tokens();
        $this->build_processed_uri();
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
        /** @var string[] $buffer Lexemas de cada segmento de ruta */
        $buffer = [];

        foreach ($this->uri_tokens as $token) {
            $buffer[] = $token['lexeme'];
        }

        return "/" . \join(
            separator: "/",
            array: $buffer
        );
    }

    public function get_decoded_processed_route(): string {
        /** @var string[] $buffer Lexemas de cada segmento de ruta */
        $buffer = [];

        foreach ($this->uri_tokens as $token) {
            $buffer[] = $token['decoded_lexeme'];
        }

        return "/" . \join(
            separator: "/",
            array: $buffer
        );
    }

    /**
     * Devuelve la URI procesada por el autómata.
     *
     * Es la ruta reconstruida ({@see RequestURILexer::get_processed_route()}) seguida, si hay una cadena
     * de consulta no vacía, del símbolo `?` y de esa cadena sin procesar ni decodificar. Si no hay cadena
     * de consulta, o está vacía (`/users?`), devuelve solo la ruta.
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
     * Implementa el patrón Singleton mediante inicialización diferida (Lazy Initialization). Si todavía
     * no existe una instancia de la clase, la construye y la almacena para su reutilización durante el
     * ciclo de vida de la aplicación.
     *
     * Las llamadas posteriores devuelven la instancia previamente construida, evitando nuevas inicializaciones
     * y análisis repetidos de la URI.
     *
     * @return static Instancia única de RequestURILexer.
     */
    public static function from(): static {
        if (!(static::$instance instanceof static)) {
            static::$instance = new static;
        }

        return self::$instance;
    }

    # MÉTODOS PRIVADOS

    /**
     * Construye la URI procesada en sus dos formas, en una sola iteración sobre los tokens.
     *
     * Une con `/` los `lexeme` y los `decoded_lexeme` de cada token, antepone la barra inicial y
     * guarda el resultado en {@see self::$processed_uri} como un {@see ProcessedURI}.
     *
     * Si no hay tokens, ambas formas son `/`.
     *
     * @return void
     */
    private function build_processed_uri(): void {
        /** @var non-empty-string[] $uri_buffer Lexemas de cada segmento de ruta */
        $uri_buffer = [];

        /** @var non-empty-string[] $uri_decoded_buffer */
        $uri_decoded_buffer = [];

        /** @var string $querystring */
        $querystring = $this->get_query_string() !== null
            ? "?" . $this->get_query_string()
            : '';

        foreach ($this->uri_tokens as $token) {
            $uri_buffer[] = $token['lexeme'];
            $uri_decoded_buffer[] = $token['decoded_lexeme'];
        }

        $uri = "/" . \join(
            separator: "/",
            array: $uri_buffer
        ) . $querystring;

        $decoded_uri = "/" . \join(
            separator: "/",
            array: $uri_decoded_buffer
        ) . $querystring;

        $this->processed_uri = new ProcessedURI(
            uri: $uri,
            decoded_uri: $decoded_uri
        );
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
     * Reglas de la resolución:
     *
     * - Cada segmento se comprueba con `is_dir()` sobre la ruta construida a partir del punto de entrada,
     *   por lo que los enlaces simbólicos a directorios se siguen y pueden encadenarse
     *   (por ejemplo, `/dir/dir/dir`).
     * - Los segmentos se comparan tal como llegan, sin decodificar: `%2e%2e` es un nombre literal y no `..`.
     *   Decodificar la ruta es responsabilidad de quien consuma `route`.
     * - Los segmentos `.` y `..` se rechazan en cualquier posición de la URI, incluso después de que la
     *   resolución de directorios haya terminado.
     *
     * La resolución física comienza desde el directorio real del punto de entrada, obtenido mediante
     * {@see RealPath::get_entry_point()}.
     *
     * Finalmente, construye una instancia de {@see RouteResolution} con la URI procesada, el directorio lógico
     * resuelto y la ruta lógica resultante.
     *
     * Esta separación permite distinguir el contexto físico de ejecución de la ruta lógica que debe procesar
     * el enrutador. No infiere el prefijo de montaje de la URL (por ejemplo, `/dir/dir`) comparando la
     * ubicación física del punto de entrada con la URI: toma los segmentos de la URI tal como llegan y solo
     * consulta el sistema de archivos (`is_dir()`) para decidir cuáles corresponden a directorios existentes
     * a partir del punto de entrada.
     *
     * @return void
     *
     * @throws RouteException Con código 403, si algún segmento de la URI es `.` o `..`.
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

            $decoded_segment = $token['decoded_lexeme'];

            if (
                $segment === ".." ||
                $decoded_segment === ".." ||
                $segment === "." ||
                $decoded_segment === "."
            ) {
                throw new RouteException(
                    message: "El segmento de directorio '{$segment}' no está permitido",
                    code: 403
                );
            }

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

        /** @var non-empty-string|null $query_string */
        $query_string = $this->get_query_string();

        /** @var QueryParamComposer $query_param_composer */
        $query_param_composer = new QueryParamComposer(
            query_string: $query_string
        );

        $this->route_resolution = new RouteResolution(
            uri: $this->processed_uri,
            dir: "/" . join(separator: "/", array: $dir_segments),
            route: "/" . join(separator: "/", array: $route_segments),
            query_string: $query_string,
            query_param_values: $query_param_composer->get_query_params()

        );
    }

    /**
     * Obtiene la ruta solicitada cuando la aplicación se ejecuta desde la línea de comandos (CLI).
     *
     * Lee las opciones `-r` o `--route` con `getopt()` y devuelve su valor.
     * Si se indican ambas, tiene prioridad `-r`. Si no se indica ninguna, devuelve la ruta raíz `/`.
     *
     * Ejemplos de uso:
     *
     * ```bash
     * php index.php -r /usuarios
     * php index.php --route=/usuarios
     * php index.php --route /usuarios
     * ```
     *
     * Nota: en la forma corta, la sintaxis `-r=/usuarios` no es válida porque el valor incluiría el
     * signo `=`. Use `-r /usuarios` o la forma larga.
     *
     * @return string|array Ruta solicitada. Si la opción se repite en la línea de comandos, `getopt()`
     *                      devuelve un array.
     */
    private function get_options() {
        $options = \getopt(
            short_options: 'r:',
            long_options: ["route:"]
        );

        return $options['r'] ?? $options['route'] ?? "/";
    }
}
