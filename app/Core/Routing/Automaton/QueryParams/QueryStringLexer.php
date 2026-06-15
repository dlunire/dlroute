<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\QueryParams;

use DLRoute\Core\Data\QueryParam;
use DLRoute\Interfaces\Routing\RouteLexerInterface;

/**
 * Analizador léxico base del querystring de la petición HTTP.
 *
 * Define el autómata completo de análisis — carga, escaneo y emisión de
 * tokens — y expone «get_tokens()» a las clases concretas que extiendan
 * esta. No puede instanciarse directamente porque el consumo de los tokens
 * depende del contexto de cada subclase.
 *
 * Un parámetro del querystring produce una o dos instancias de QueryParam:
 *  - Sin «=» → una instancia de tipo QUERY_NAME (value implícito null)
 *  - Con «=» → dos instancias: QUERY_NAME y QUERY_VALUE
 * 
 * @package DLRoute\Core\Routing\Automaton
 * 
 * @version v1.0.0 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 * @license MIT
 */
abstract class QueryStringLexer implements RouteLexerInterface {

    /**
     * Parámetros de la petición
     *
     * @var string|null $query_string
     */
    private readonly ?string $query_string;

    /**
     * Posición del cursor del autómata en la cadena de bytes.
     *
     * @var integer $offset
     */
    private int $offset = 0;

    /**
     * Longitud en bytes de la cadena del querystring.
     *
     * Se calcula una sola vez en «load_query_string()» con «strlen()» y se
     * almacena como propiedad inmutable para evitar recalcularla en cada
     * iteración del autómata. Vale 0 cuando el querystring está vacío o ausente.
     *
     * @var int
     */
    private readonly int $size;


    /**
     * Tipo del token que el subautómata está procesando en el ciclo actual.
     *
     * Siempre inicia en QUERY_NAME porque el primer byte de cualquier bloque
     * pertenece al nombre del parámetro. Cambia a QUERY_VALUE cuando el
     * subautómata encuentra el operador de asignación «=».
     *
     * @var QueryStringTokenType
     */
    private QueryStringTokenType $tokentype = QueryStringTokenType::QUERY_NAME;

    /**
     * Tokens capturados durante el análisis léxico
     *
     * @var QueryParam[] $tokens
     */
    protected array $tokens = [];

    /**
     * Cantidad de tokens capturados durante el análisis léxico del querystring.
     *
     * Se incrementa cada vez que «emit_token()» agrega una instancia de
     * QueryParam a «$this->tokens», permitiendo a las subclases consultar
     * cuántos tokens fueron emitidos sin necesidad de llamar a «count()»
     * sobre el array en cada acceso.
     *
     * @var int
     */
    protected int $token_count = 0;

    /**
     * Inicializa el analizador léxico del querystring.
     *
     * Orquesta la secuencia de análisis en dos fases:
     *  1. «load_query_string()» — carga y normaliza la cadena de parámetros
     *     desde «$_SERVER['QUERY_STRING']» y calcula «$this->size».
     *  2. «scanner()» — ejecuta el autómata sobre la cadena cargada y
     *     popula «$this->tokens» con instancias de QueryParam.
     *
     * Si el querystring está vacío o ausente, «scanner()» retorna
     * inmediatamente y «$this->tokens» queda como array vacío.
     */
    public function __construct() {
        $this->load_query_string();
        $this->scanner();
    }

    /**
     * Carga la cadena de parámetros de la URL
     *
     * @return void
     */
    private function load_query_string(): void {
        $query_string = $_SERVER['QUERY_STRING'] ?? null;

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
     * Itera sobre la cadena de bytes activando «request_emit_token()» en cada
     * posición del cursor. Cada llamada procesa un bloque completo y avanza
     * el cursor hasta «&» o EOF, por lo que «$this->offset++» en este nivel
     * consume el separador «&» antes de iniciar el siguiente bloque.
     *
     * Retorna inmediatamente si el querystring está vacío («$this->size === 0»).
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
     * Un bloque es el segmento entre dos separadores «&» o entre el inicio
     * de la cadena y el primer «&». Cada bloque puede producir uno o dos
     * tokens según la presencia del operador de asignación «=»:
     *
     *  - Sin «=» → emite un único token QUERY_NAME (value implícito null)
     *  - Con «=» → emite QUERY_NAME y luego QUERY_VALUE
     *
     * El subautómata reinicia «$tokentype» a QUERY_NAME al inicio de cada
     * bloque para garantizar que el estado no se contamine entre parámetros.
     * El cursor «$this->offset» queda posicionado sobre «&» al terminar,
     * para que «scanner()» lo avance y continúe con el siguiente bloque.
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
                $this->emit_token($start_offset);
                $start_offset = $this->offset;
                continue;
            }

            if ($byte === self::QUERY_SEPARATOR) {
                $this->emit_token($start_offset, $this->tokentype);
                $start_offset = $this->offset;
                return;
            }

            $this->offset++;
        }

        # EOF: emite el último token del bloque sin separador final.
        $this->emit_token($start_offset, $this->tokentype);
    }

    /**
     * Emite un token y lo agrega al array de tokens capturados.
     *
     * Calcula el lexema extrayendo la subcadena desde «`$start_offset`» hasta
     * la posición actual del cursor «$this->offset». Cuando el token es de
     * tipo `QUERY_VALUE`, avanza «`$start_offset`» en una posición y reduce
     * «$length» en uno para excluir el operador de asignación «=» del lexema.
     *
     * Si la longitud calculada es menor a 1, el token se descarta silenciosamente
     * para evitar emitir lexemas vacíos cuando «=» o «&» aparecen consecutivos
     * o al inicio de la cadena.
     *
     * El token emitido se almacena como una instancia de QueryParam en
     * «$this->tokens», garantizando tipado estricto en lugar de arrays anónimos.
     *
     * Ejemplo para la cadena «`campo=algo=30`»:
     *  - `QUERY_NAME`  → lexema «`campo`»    (desde 0 hasta «=»)
     *  - `QUERY_VALUE` → lexema «`algo=30`»  (desde «=»+1 hasta EOF o «&»)
     *
     * @param int $start_offset Posición inicial del cursor donde comienza el token.
     * @param QueryStringTokenType $tokentype Tipo del token emitido. Por defecto `QUERY_NAME`,
     *                                        ya que el primer byte de cualquier bloque
     *                                        pertenece siempre al nombre del parámetro.
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

        if ($length < 1) return;

        $this->tokens[] = new QueryParam(...[
            "lexeme" => \substr(
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
     * Cada elemento es una instancia de QueryParam con los datos del token:
     * lexema, offset, tipo y longitud.
     *
     * @return QueryParam[]
     */
    protected function get_tokens(): array {
        return $this->tokens;
    }
}
