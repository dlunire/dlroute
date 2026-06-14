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
            $byte = $this->query_string[$this->offset];

            if ($byte !== self::WHITE_SPACE) {
                $this->emit_token();
            }

            $this->offset++;
        }

        print_r($this->query_string);
        exit;
    }

    /**
     * Emite un token
     *
     * @return void
     */
    private function emit_token(): void {

        $current_offset = $this->offset;

        
    }

    private function next_delimiter(): void {
       
        while ($this->offset < $this->size) {

            $this->offset++;
        }
    }
}
