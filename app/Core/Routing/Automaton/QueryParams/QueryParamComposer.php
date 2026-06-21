<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\QueryParams;

use DLRoute\Core\Data\QueryParam;
use DLRoute\Core\Data\QueryParamValue;

/**
 * Compone los tokens léxicos del querystring en pares estructurados `nombre → valor`.
 *
 * Extiende `QueryStringLexer` para consumir los tokens `QUERY_NAME` y `QUERY_VALUE`
 * emitidos por el autómata y construir instancias de `QueryParamValue` listas
 * para ser consumidas por el desarrollador o por la telemetría del sistema.
 *
 * Los pares compuestos se indexan por nombre normalizado en `$query_params`,
 * garantizando acceso directo en O(1). Cuando el mismo nombre aparece más de
 * una vez en el querystring, gana el último valor — last-write-wins.
 *
 * ---
 *
 * Reglas de composición:
 *
 * | Situación                       | Resultado                                          |
 * | ------------------------------- | -------------------------------------------------- |
 * | `QUERY_NAME` + `QUERY_VALUE`    | Par completo con nombre y valor                    |
 * | `QUERY_NAME` + `QUERY_NAME`     | Par con `value: null` — parámetro sin `=`          |
 * | `QUERY_NAME` al final           | Par con `value: null` — último token sin valor     |
 * | `QUERY_VALUE` en posición 0     | Descartado — valor huérfano sin nombre             |
 * | Nombre vacío o en blanco        | Descartado — `trim($token->lexeme) === ''`         |
 * | Valor vacío o en blanco         | Normalizado a `null`                               |
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
     * Pares `nombre → valor` compuestos a partir de los tokens del autómata.
     *
     * Indexados por nombre normalizado para acceso directo en O(1).
     * Cuando el mismo nombre aparece más de una vez en el querystring,
     * la última ocurrencia sobreescribe a la anterior — last-write-wins.
     *
     * @var QueryParamValue[]
     */
    private array $query_params = [];

    /**
     * Inicializa el compositor invocando el analizador léxico base
     * y construyendo los pares `nombre → valor` desde los tokens capturados.
     *
     * El parámetro `$uri` se pasa directamente al constructor de
     * `QueryStringLexer`. Si es `null`, el lexer lee `$_SERVER['QUERY_STRING']`
     * automáticamente. Si es una cadena, el lexer la analiza directamente —
     * útil para delegación desde el `RouteLexer` o para uso autónomo fuera
     * del ciclo de vida HTTP.
     *
     * @param string|null $uri Cadena de querystring a analizar, o `null` para
     *                         leer `$_SERVER['QUERY_STRING']` automáticamente.
     */
    public function __construct(?string $uri = null) {
        parent::__construct($uri);
        $this->build_tokens();
    }

    /**
     * Recorre los tokens capturados y compone los pares `nombre → valor`.
     *
     * Itera sobre `$this->tokens` en una sola pasada. Para cada token
     * `QUERY_NAME` determina si el siguiente token es `QUERY_VALUE` para
     * emitir un par completo, o `QUERY_NAME` (o ausente) para emitir un par
     * con `value: null`. Los tokens `QUERY_VALUE` en la posición `0` se
     * descartan por ser huérfanos — no tienen un `QUERY_NAME` precedente.
     *
     * Avanza el índice `$index` en uno extra cuando consume un `QUERY_VALUE`,
     * evitando que el siguiente ciclo lo procese como si fuera un `QUERY_NAME`.
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
     * Construye una instancia de `QueryParamValue` y la agrega a `$this->query_params`.
     *
     * Normaliza el valor a `null` cuando `$next_token` es `null`, está ausente,
     * o su lexema es una cadena vacía o en blanco — garantizando consistencia
     * semántica entre parámetros sin valor y parámetros con valor vacío.
     *
     * Descarta silenciosamente el token si el lexema del nombre queda vacío
     * tras aplicar `trim()` — evita indexar claves vacías en `$query_params`.
     *
     * El `$offset` del par resultante se ajusta con `$diff_lexeme_length` para
     * reflejar la posición del primer byte real del nombre en el querystring
     * original, descontando los espacios iniciales eliminados por `trim()`.
     *
     * @param QueryParam      $token      Token `QUERY_NAME` que aporta el nombre del parámetro.
     * @param QueryParam|null $next_token Token `QUERY_VALUE` que aporta el valor, o `null`
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

        /** @var int $original_lexeme_length Longitud original del lexema (incluye espacios en blanco) */
        $original_lexeme_length = \strlen($lexeme);

        $this->normalize_key($lexeme);

        /** @var int $diff_lexeme_length Bytes eliminados por trim() al inicio del nombre */
        $diff_lexeme_length = $original_lexeme_length - \strlen($lexeme);

        $this->query_params[$lexeme] = new QueryParamValue(...[
            "name"         => $lexeme,
            "offset"       => $token->offset + $diff_lexeme_length,
            "value"        => $value,
            "offset_value" => $value !== null ? $next_token?->offset : 0,
            "length"       => $length,
        ]);
    }

    /**
     * Normaliza el nombre de un parámetro extraído del querystring.
     *
     * Opera directamente sobre la variable original mediante paso por referencia,
     * evitando la creación de copias intermedias de la cadena. El proceso
     * consta de dos fases:
     *
     * 1. **Saneamiento de bordes** — `trim()` elimina espacios al inicio y al final.
     * 2. **Sustitución interna** — recorrido byte a byte que reemplaza cada
     *    ocurrencia de `self::WHITE_SPACE` por `self::UNDERSCORE`.
     *
     * Ejemplo:
     * ```
     * " nombre con espacios " → "nombre_con_espacios"
     * ```
     *
     * @param string &$input Referencia a la cadena del nombre a normalizar.
     *                       La variable es mutada directamente.
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
     * Devuelve los parámetros del querystring compuestos como pares `nombre → valor`.
     *
     * Cada elemento es una instancia de `QueryParamValue` indexada por el nombre
     * normalizado del parámetro. El array está vacío cuando el querystring está
     * ausente o todos sus parámetros fueron descartados por el autómata.
     *
     * El acceso por nombre es directo en O(1):
     *
     * ```php
     * $params = (new QueryParamComposer())->get_query_params();
     * $value  = $params['campo']->value ?? null;
     * ```
     *
     * @return QueryParamValue[]
     */
    public function get_query_params(): array {
        return $this->query_params;
    }
}
