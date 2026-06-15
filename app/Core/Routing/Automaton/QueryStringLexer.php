<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

use DLRoute\Interfaces\Routing\RouteLexerInterface;

class QueryStringLexer implements RouteLexerInterface {

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
     * @var array
     */
    protected array $tokens = [];

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

    protected function scanner(): void {
        if ($this->size === 0) {
            return;
        }

        while ($this->offset < $this->size) {
            $this->request_emit_token();
            $this->offset++;
        }

        print_r($this->query_string . PHP_EOL);
        print_r($this->tokens);
        exit;
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
     * Calcula el lexema extrayendo la subcadena desde «$start_offset» hasta
     * la posición actual del cursor «$this->offset». Cuando el token es de
     * tipo `QUERY_VALUE`, avanza «$start_offset» en una posición y reduce
     * «$length» en uno para excluir el operador de asignación «=» del lexema.
     *
     * Ejemplo para la cadena «campo=algo=30»:
     *  - `QUERY_NAME`  → lexema «campo»    (desde 0 hasta «=»)
     *  - `QUERY_VALUE` → lexema «algo=30»  (desde «=»+1 hasta EOF o «&»)
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

        $this->tokens[] = [
            "lexeme" => \substr(
                string: $this->query_string,
                offset: $start_offset,
                length: $length
            ),
            "offset" => $start_offset,
            "type"   => $tokentype,
            "length" => $length,
        ];
    }
}
