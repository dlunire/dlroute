<?php

declare(strict_types=1);

namespace DLRoute\Core\Data;

/**
 * Representa un par «nombre → valor» compuesto a partir de los tokens
 * capturados durante el análisis léxico del querystring.
 *
 * Cada instancia es inmutable — sus propiedades se asignan una sola vez
 * en el constructor y no pueden modificarse posteriormente. Esto garantiza
 * que los parámetros de la petición no sean alterados después del análisis.
 *
 * Además del par «nombre → valor», expone metadatos de posición que permiten
 * al analizador semántico localizar con precisión cada parámetro dentro de la
 * cadena original del querystring sin necesidad de releerla.
 *
 * La distinción entre valor nulo y valor vacío es semántica:
 *  - «null» → el parámetro existe pero no tiene valor asignado («?campo»)
 *  - «""»   → el parámetro tiene un valor explícitamente vacío («?campo=»),
 *             sin embargo el autómata normaliza este caso a «null» también.
 *
 * @package DLRoute\Core\Data
 *
 * @version v1.0.0 (release)
 * @author  David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 * @license MIT
 */
final class QueryParamValue {

    /**
     * @param string $name Nombre del parámetro tal como fue capturado en el
     *        querystring, con decodificación URL aplicada.
     *
     * @param int $offset Posición inicial en bytes del nombre del parámetro
     *        dentro de la cadena del querystring.
     *        Permite localizar el nombre programáticamente usando «substr()»:
     *        ```php
     *        $name = substr($query_string, $param->offset, strlen($param->name));
     *        ```
     * @param string|null $value Valor del parámetro, o «null» si no fue asignado
     *        o si su valor era una cadena vacía o en blanco.
     * @param int $offset_value Posición inicial en bytes del valor del parámetro
     *        dentro de la cadena del querystring. Vale 0 cuando «$value» es «null»
     *        — el parámetro no tiene valor asignado y por tanto no tiene posición.
     * @param int $length Longitud en bytes del valor capturado. Vale 0 cuando
     *        «$value» es «null».
     */
    public function __construct(
        public readonly string $name,
        public readonly int $offset,
        public readonly ?string $value,
        public readonly int $offset_value,
        public readonly int $length
    ) {
    }
}
