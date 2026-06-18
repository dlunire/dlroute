<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\QueryParams;

use DLRoute\Core\Data\QueryParam;
use DLRoute\Core\Data\QueryParamValue;

/**
 * Compone los tokens léxicos del querystring en pares estructurados «nombre → valor».
 *
 * Extiende QueryStringLexer para consumir los tokens QUERY_NAME y QUERY_VALUE
 * emitidos por el autómata y construir instancias de QueryParamValue listas
 * para ser consumidas por el desarrollador o por la telemetría del sistema.
 *
 * Reglas de composición:
 *  - QUERY_NAME seguido de QUERY_VALUE → par completo con nombre y valor
 *  - QUERY_NAME seguido de QUERY_NAME  → par con valor null (parámetro sin «=»)
 *  - QUERY_VALUE en posición 0         → valor huérfano, descartado
 *  - Valor vacío o en blanco           → normalizado a null
 * 
 * @package DLRoute\Core\Routing\Automaton\QueryParams
 * 
 * @version v1.0.0 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 * @license MIT
 */
final class QueryParamComposer extends QueryStringLexer {


    /**
     * Pares «nombre → valor» compuestos a partir de los tokens del autómata.
     *
     * @var QueryParamValue[]
     */
    private array $query_params = [];

    /**
     * Inicializa el compositor invocando el analizador léxico base
     * y construyendo los pares «nombre → valor» desde los tokens capturados.
     */
    public function __construct() {
        parent::__construct();
        $this->build_tokens();
    }

    /**
     * Recorre los tokens capturados y compone los pares «nombre → valor».
     *
     * Itera sobre «$this->tokens» en una sola pasada. Para cada token
     * QUERY_NAME determina si el siguiente token es QUERY_VALUE para
     * emitir un par completo, o QUERY_NAME para emitir un par con valor null.
     * Los tokens QUERY_VALUE en la posición 0 se descartan por ser huérfanos.
     *
     * @return void
     */
    private function build_tokens(): void {
        for ($index = 0; $index < $this->token_count; $index++) {

            /** @var QueryParam $token */
            $token = $this->tokens[$index];

            if ($token->type === QueryStringTokenType::QUERY_VALUE && $index === 0) {
                continue;
            }

            /** @var QueryParam|null $next_token */
            $next_token = $this->tokens[$index + 1] ?? null;

            if ($token->type === QueryStringTokenType::QUERY_NAME && $next_token?->type === QueryStringTokenType::QUERY_VALUE) {
                $this->emit_token($token, $next_token);
                $index++;

                continue;
            }

            if ($token->type === QueryStringTokenType::QUERY_NAME && $next_token?->type === QueryStringTokenType::QUERY_NAME) {
                $this->emit_token($token);
                continue;
            }

            $this->emit_token($token);
        }
    }

    /**
     * Construye una instancia de `QueryParamValue` y la agrega a «`$this->query_params`».
     *
     * Normaliza el valor a «`null`» cuando «`$next_token`» es nulo, está ausente,
     * o su lexema es una cadena vacía o en blanco, garantizando consistencia
     * semántica entre parámetros sin valor y parámetros con valor vacío.
     *
     * @param QueryParam $token      Token `QUERY_NAME` que aporta el nombre del parámetro.
     * @param QueryParam|null $next_token Token `QUERY_VALUE` que aporta el valor, o «`null`»
     *                                    si el parámetro no tiene valor asignado.
     * @return void
     */
    private function emit_token(QueryParam $token, ?QueryParam $next_token = null): void {
        /** @var non-empty-string|null $value */
        $value = null;

        /** @var int $length */
        $length = 0;

        if ($next_token instanceof QueryParam) {
            $value = trim($next_token->lexeme) === ''
                ? null
                : $next_token->lexeme;

            $length = trim($next_token->lexeme) === ''
                ? 0
                : $next_token->length;
        }

        if (trim($token->lexeme) === '') {
            return;
        }

        /** @var non-empty-string $lexeme */
        $lexeme = $token->lexeme;

        /** @var int $origina_lexeme_lenth Longitud original del lexema (incluye espacios en blanco) */
        $original_lexeme_length = \strlen($lexeme);

        $this->normalize_key($lexeme);

        /** @var int $diff_lexeme_length */
        $diff_lexeme_length = $original_lexeme_length    - \strlen($lexeme);

        $this->query_params[$lexeme] = new QueryParamValue(...[
            "name" => $lexeme,
            "offset" => $token->offset + $diff_lexeme_length,
            "value" => $value,
            "offset_value" => $value !== null ? $next_token?->offset : 0,
            "length" => $length,
        ]);
    }

    /**
     * Normaliza el nombre de una clave (key) extraída del querystring.
     *
     * Este método purifica el identificador de la clave preparándolo para el
     * análisis semántico. Opera directamente sobre el espacio de memoria de 
     * la variable original mediante paso por referencia, evitando la creación 
     * de copias intermedias de la cadena.
     *
     * El proceso de normalización consta de dos fases:
     * 1. Saneamiento de bordes: Elimina espacios en blanco al inicio y al
     * final utilizando «`trim()`».
     * 2. Sustitución interna: Realiza un recorrido secuencial (byte a byte)
     * sobre la cadena resultante, reemplazando cada coincidencia de
     * «`self::WHITE_SPACE`» por el carácter seguro «`self::UNDERSCORE`».
     *
     * @param string &$input Referencia a la cadena de texto de la clave a 
     * normalizar. La variable es mutada internamente.
     * @return void
     */
    private function normalize_key(string &$input): void {
        $input = trim($input);

        /** @var int $length */
        $length = \strlen($input);

        for ($index = 0; $index < $length; $index++) {
            /** @var non-empty-string $byte */
            $byte = $input[$index];

            if ($byte === self::WHITE_SPACE) {
                $input[$index] = self::UNDERSCORE;
            }
        }
    }

    /**
     * Devuelve los parámetros del querystring compuestos como pares «nombre → valor».
     *
     * Cada elemento es una instancia inmutable de QueryParamValue. El array
     * está vacío cuando el querystring está ausente o todos sus parámetros
     * fueron descartados por el autómata.
     *
     * @return QueryParamValue[]
     */
    public function get_query_params(): array {
        return $this->query_params;
    }
}
