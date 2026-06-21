<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\QueryParams;

use DLRoute\Core\Data\QueryParam;
use DLRoute\Interfaces\Routing\RouteLexerInterface;

/**
 * Analizador léxico base del querystring de la petición HTTP.
 *
 * Define el autómata completo de análisis — carga, escaneo y emisión de
 * tokens — y expone `get_tokens()` a las clases concretas que extiendan
 * esta. No puede instanciarse directamente porque el consumo de los tokens
 * depende del contexto de cada subclase.
 *
 * El análisis se realiza en una sola pasada sobre la cadena de bytes,
 * sin `parse_str()`, sin `explode()` y sin expresiones regulares.
 *
 * El constructor acepta una URI externa opcional, lo que permite que el
 * lexer opere tanto sobre `$_SERVER['QUERY_STRING']` como sobre una cadena
 * suministrada directamente — por ejemplo, cuando el `RouteLexer` delega
 * el fragmento del querystring detectado en la URI registrada por el
 * desarrollador.
 *
 * Un parámetro del querystring produce una o dos instancias de QueryParam:
 *  - Sin `=` → una instancia de tipo `QUERY_NAME` (value implícito null)
 *  - Con `=` → dos instancias: `QUERY_NAME` y `QUERY_VALUE`
 *
 * ---
 *
 * Arquitectura del autómata en dos niveles:
 *
 * ```
 * Nivel 1 — scanner()
 *   Itera sobre la cadena completa byte a byte.
 *   Cada llamada a request_emit_token() procesa un bloque completo.
 *   Al retornar, scanner() avanza el cursor sobre el separador «&»
 *   antes de iniciar el siguiente bloque.
 *
 * Nivel 2 — request_emit_token()
 *   Procesa un bloque completo (segmento entre «&» o entre inicio y «&»).
 *   Reinicia el estado a QUERY_NAME al entrar si el estado anterior era QUERY_VALUE.
 *   Cambia el estado interno según los bytes encontrados.
 *   Emite tokens como instancias de QueryParam y retorna al encontrar «&».
 * ```
 *
 * @package DLRoute\Core\Routing\Automaton\QueryParams
 *
 * @version v1.0.0 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 * @license MIT
 */
abstract class QueryStringLexer implements RouteLexerInterface {

    /**
     * Cadena del querystring decodificada con `urldecode()`.
     *
     * Se asigna en `load_query_string()` a partir de `$_SERVER['QUERY_STRING']`
     * o de la URI suministrada externamente. Vale `null` cuando el querystring
     * está ausente o es una cadena vacía o en blanco.
     *
     * @var string|null $query_string
     */
    private readonly ?string $query_string;

    /**
     * Posición actual del cursor del autómata en la cadena de bytes.
     *
     * Se incrementa en `scanner()` al consumir el separador `&` entre bloques,
     * y en `request_emit_token()` al avanzar byte a byte dentro de cada bloque.
     * Inicia en `0` al construir el lexer.
     *
     * @var int $offset
     */
    private int $offset = 0;

    /**
     * Longitud en bytes del querystring.
     *
     * Se calcula una sola vez en `load_query_string()` con `strlen()` y se
     * almacena como propiedad inmutable para evitar recalcularla en cada
     * iteración del autómata. Vale `0` cuando el querystring está vacío o
     * ausente.
     *
     * @var int $size
     */
    private readonly int $size;

    /**
     * Estado actual del subautómata.
     *
     * Representa el tipo de token que el subautómata está procesando en el
     * ciclo actual. Siempre inicia en `QUERY_NAME` porque el primer byte de
     * cualquier bloque pertenece al nombre del parámetro.
     *
     * Cambia a `QUERY_VALUE` cuando `request_emit_token()` encuentra el
     * operador de asignación `=`. Al inicio de cada nuevo bloque,
     * `request_emit_token()` lo reinicia explícitamente a `QUERY_NAME` si
     * quedó en `QUERY_VALUE` por el bloque anterior — garantizando que el
     * estado no se contamine entre parámetros.
     *
     * @var QueryStringTokenType $tokentype
     */
    private QueryStringTokenType $tokentype = QueryStringTokenType::QUERY_NAME;

    /**
     * Tokens capturados durante el análisis léxico.
     *
     * Cada elemento es una instancia de `QueryParam` emitida por `emit_token()`.
     * La visibilidad `protected` permite que las subclases — principalmente
     * `QueryParamComposer` — consuman los tokens directamente sin pasar por
     * `get_tokens()`.
     *
     * @var QueryParam[] $tokens
     */
    protected array $tokens = [];

    /**
     * Cantidad de tokens capturados durante el análisis léxico del querystring.
     *
     * Se incrementa en `emit_token()` cada vez que se agrega una instancia de
     * `QueryParam` a `$this->tokens`, permitiendo a las subclases consultar
     * cuántos tokens fueron emitidos sin necesidad de llamar a `count()` sobre
     * el array en cada acceso — mantenido en O(1).
     *
     * @var int $token_count
     */
    protected int $token_count = 0;

    /**
     * Inicializa el analizador léxico del querystring.
     *
     * Orquesta la secuencia de análisis en dos fases:
     *  1. `load_query_string()` — carga y normaliza la cadena de parámetros
     *     y calcula `$this->size`.
     *  2. `scanner()` — ejecuta el autómata sobre la cadena cargada y
     *     popula `$this->tokens` con instancias de `QueryParam`.
     *
     * Si el querystring está vacío o ausente, `scanner()` retorna
     * inmediatamente y `$this->tokens` queda como array vacío.
     *
     * #### Fuente de datos según el parámetro `$uri`
     *
     * | Valor de `$uri`        | Fuente del querystring                                                              |
     * | ---------------------- | ----------------------------------------------------------------------------------- |
     * | `null` (por defecto)   | `$_SERVER['QUERY_STRING']` — querystring de la petición HTTP activa                 |
     * | `string`               | La cadena suministrada directamente — delegación desde `RouteLexer` o uso autónomo |
     *
     * @param string|null $uri URI o fragmento de querystring a analizar.
     *                         Si es `null`, se usa `$_SERVER['QUERY_STRING']`.
     */
    public function __construct(?string $uri = null) {
        $this->load_query_string($uri);
        $this->scanner();
    }

    /**
     * Carga y normaliza la cadena de parámetros del querystring.
     *
     * Si `$uri` es `null`, intenta leer `$_SERVER['QUERY_STRING']`. Si la
     * cadena resultante está vacía o contiene solo espacios en blanco, asigna
     * `null` a `$this->query_string` y `0` a `$this->size`, lo que hace que
     * `scanner()` retorne inmediatamente sin procesar nada.
     *
     * Cuando la cadena es válida, aplica `urldecode()` para normalizar
     * secuencias de escape URL (`%20`, `%3D`, etc.) antes del análisis léxico,
     * y calcula `$this->size` con `strlen()` sobre la cadena ya decodificada.
     *
     * @param string|null $uri URI de la petición a ser analizada.
     * @return void
     */
    private function load_query_string(?string $uri = null): void {
        $query_string = $uri === null
            ? $_SERVER['QUERY_STRING'] ?? null
            : $uri;

        if ($query_string !== null && trim($query_string) !== '') {
            $this->query_string = \urldecode($query_string);
            $this->size = \strlen($this->query_string);

            return;
        }

        $this->size = 0;
        $this->query_string = null;
    }

    /**
     * Orquesta el análisis léxico completo del querystring.
     *
     * Itera sobre la cadena de bytes activando `request_emit_token()` en cada
     * posición del cursor. Cada llamada procesa un bloque completo y avanza
     * el cursor hasta `&` o EOF, por lo que `$this->offset++` en este nivel
     * consume el separador `&` antes de iniciar el siguiente bloque.
     *
     * Retorna inmediatamente si el querystring está vacío (`$this->size === 0`).
     *
     * @return void
     */
    private function scanner(): void {
        if ($this->size === 0) {
            return;
        }

        while ($this->offset < $this->size) {
            $this->request_emit_token();
            $this->offset++;
        }
    }

    /**
     * Subautómata que procesa un bloque del querystring y emite sus tokens.
     *
     * Un bloque es el segmento entre dos separadores `&` o entre el inicio
     * de la cadena y el primer `&`. Cada bloque puede producir uno o dos
     * tokens según la presencia del operador de asignación `=`:
     *
     *  - Sin `=` → emite un único token `QUERY_NAME` (value implícito null)
     *  - Con `=` → emite `QUERY_NAME` y luego `QUERY_VALUE`
     *
     * Al entrar, verifica si `$tokentype` quedó en `QUERY_VALUE` por el bloque
     * anterior y lo reinicia a `QUERY_NAME` — garantizando que el estado no se
     * contamine entre parámetros. El cursor `$this->offset` queda posicionado
     * sobre `&` al retornar, para que `scanner()` lo avance y continúe con el
     * siguiente bloque.
     *
     * @return void
     */
    private function request_emit_token(): void {

        if ($this->tokentype === QueryStringTokenType::QUERY_VALUE) {
            $this->tokentype = QueryStringTokenType::QUERY_NAME;
        }

        /** @var int $start_offset Posición inicial del bloque actual. */
        $start_offset = $this->offset;

        while ($this->offset < $this->size) {
            /** @var non-empty-string $byte */
            $byte = $this->query_string[$this->offset];

            if ($byte === self::QUERY_ASSIGN && $this->tokentype === QueryStringTokenType::QUERY_NAME) {
                $this->tokentype = QueryStringTokenType::QUERY_VALUE;
                $this->emit_token($start_offset, QueryStringTokenType::QUERY_NAME);
                $start_offset = $this->offset;
                continue;
            }

            if ($byte === self::QUERY_SEPARATOR) {
                $this->emit_token($start_offset, $this->tokentype);
                $this->tokentype = QueryStringTokenType::QUERY_NAME;
                $start_offset = $this->offset;

                return;
            }

            $this->offset++;
        }

        $this->emit_token($start_offset, $this->tokentype);
    }

    /**
     * Emite un token y lo agrega al array de tokens capturados.
     *
     * Calcula el lexema extrayendo la subcadena desde `$start_offset` hasta
     * la posición actual del cursor `$this->offset`. Cuando el token es de
     * tipo `QUERY_VALUE`, avanza `$start_offset` en una posición y reduce
     * `$length` en uno para excluir el operador de asignación `=` del lexema.
     *
     * Si la longitud calculada es menor a `1`, se normaliza a `0` y el lexema
     * se emite como cadena vacía `''` — sin descartar el token. La conversión
     * de lexemas vacíos a `null` es responsabilidad de `QueryParamComposer`,
     * no de este lexer.
     *
     * El token emitido se almacena como una instancia de `QueryParam` en
     * `$this->tokens`, garantizando tipado estricto en lugar de arrays anónimos.
     *
     * #### Ejemplo para la cadena `campo=algo=30`:
     *
     * ```
     * QUERY_NAME  → lexema "campo"    (desde offset 0 hasta «=»)
     * QUERY_VALUE → lexema "algo=30"  (desde «=»+1 hasta EOF o «&»)
     * ```
     *
     * @param int $start_offset Posición inicial del cursor donde comienza el token.
     * @param QueryStringTokenType $tokentype Tipo del token emitido. Por defecto
     *                                        `QUERY_NAME`, ya que el primer byte de
     *                                        cualquier bloque pertenece siempre al
     *                                        nombre del parámetro.
     * @return void
     */
    private function emit_token(
        int $start_offset,
        QueryStringTokenType $tokentype = QueryStringTokenType::QUERY_NAME
    ): void {
        /** @var int $length */
        $length = $this->offset - $start_offset;

        if ($tokentype === QueryStringTokenType::QUERY_VALUE) {
            $start_offset++;
            $length--;
        }

        if ($length < 1) {
            $length = 0;
        }

        $this->tokens[] = new QueryParam(...[
            "lexeme" => $length === 0 ? '' : \substr(
                string: $this->query_string,
                offset: $start_offset,
                length: $length
            ),
            "offset" => $start_offset,
            "type"   => $tokentype,
            "length" => $length,
        ]);

        $this->token_count++;
    }

    /**
     * Devuelve los tokens capturados durante el análisis léxico.
     *
     * Cada elemento es una instancia de `QueryParam` con los campos:
     * `lexeme`, `offset`, `type` y `length`.
     *
     * Expuesto como `protected` para que las subclases — principalmente
     * `QueryParamComposer` — puedan consumir los tokens sin necesidad de
     * acceder directamente a `$this->tokens`.
     *
     * @return QueryParam[]
     */
    protected function get_tokens(): array {
        return $this->tokens;
    }
}