<?php

declare(strict_types=1);

namespace DLRoute\Core\Data;

use DLRoute\Core\Routing\Automaton\QueryParams\QueryStringTokenType;

/**
 * Representa un token capturado durante el análisis léxico del querystring.
 *
 * Cada instancia es inmutable — sus propiedades se asignan una sola vez
 * en el constructor y no pueden modificarse posteriormente. Esto garantiza
 * que los datos de la petición no sean alterados después del análisis.
 *
 * Un parámetro del querystring produce una o dos instancias de QueryParam:
 *  - Sin «=» → una instancia de tipo QUERY_NAME (value implícito null)
 *  - Con «=» → dos instancias: QUERY_NAME y QUERY_VALUE
 * 
 * @package DLRoute\Core\Data;
 * 
 * @version v1.0.0 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 * @license MIT
 */
final class QueryParam {

    /**
     * @param string $lexeme Secuencia de bytes que conforman el token capturado.
     * @param int $offset Posición inicial del token dentro de la cadena del querystring.
     * @param QueryStringTokenType $type Tipo del token capturado: QUERY_NAME o QUERY_VALUE.
     * @param int $length Longitud en bytes del lexema capturado.
     */
    public function __construct(
        public readonly string $lexeme,
        public readonly int $offset,
        public readonly QueryStringTokenType $type,
        public readonly int $length,
    ) {}
}