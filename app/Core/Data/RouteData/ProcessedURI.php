<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

use DLRoute\Core\Data\QueryParamValue;

/**
 * Contiene la URI procesada en sus dos formas, obtenidas en una sola iteración sobre los tokens.
 *
 * La forma original se arma con el `lexeme` de cada token y la decodificada con su
 * `decoded_lexeme`. Ambas llevan los lexemas separados por `/`, con `/` inicial, seguidos de la
 * cadena de consulta cuando existe.
 *
 * La cadena de consulta va sin decodificar en las dos formas: sus valores se decodifican al
 * evaluarlos, en {@see QueryParamValue}.
 *
 * Los valores se establecen en la construcción y permanecen inmutables.
 *
 * @package DLRoute\Core\Data\RouteData
 * @version 1.0.0
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class ProcessedURI {

    /**
     * Construye la representación de la URI procesada.
     *
     * @param string $uri URI procesada sin decodificar, con `/` inicial y la cadena de consulta cuando existe. Sin tokens, `/`.
     * @param string $decoded_uri URI procesada con los segmentos decodificados y la cadena de consulta sin decodificar. Sin tokens, `/`.
     */
    public function __construct(
        public readonly string $uri = "/",
        public readonly string $decoded_uri = "/"
    ) {
    }
}
