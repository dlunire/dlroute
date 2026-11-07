<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

use DLRoute\Core\Data\QueryParamValue;

/**
 * Representa el resultado de la resolución de una URI de petición.
 *
 * La URI se conserva en dos formas dentro de un {@see ProcessedURI}: la original, tal como llegó,
 * y la decodificada, que genera el analizador léxico en la misma pasada con la que recorre la URI.
 * La ruta lógica se conserva solo en su forma original. La codificación solo aplica a lo que llega
 * por HTTP o a las rutas escritas por el cliente de la aplicación.
 *
 * Los valores se establecen en la construcción y permanecen inmutables.
 *
 * @package DLRoute\Core\Data\RouteData
 * @version 1.0.0
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RouteResolution {

    /**
     * Construye una nueva representación del resultado de resolución de la URI.
     *
     * @param ProcessedURI $uri URI procesada en su forma original y decodificada. La cadena de consulta, cuando existe, va sin decodificar en ambas.
     * @param string $dir Directorio del sistema operativo sobre el que se construyen los directorios lógicos de la ruta base. No depende de la codificación.
     * @param string $route Ruta lógica sin decodificar.
     * @param string|null $query_string Cadena de consulta original, sin el delimitador `?`, o null si no existe.
     * @param QueryParamValue[] $query_param_values Valores de los parámetros de consulta asociados a la resolución.
     */
    public function __construct(
        public readonly ProcessedURI $uri = new ProcessedURI(
            uri: "/",
            decoded_uri: "/"
        ),
        public readonly string $dir = "/",
        public readonly string $route = "/",
        public readonly ?string $query_string = null,
        public readonly array $query_param_values = []
    ) {
    }
}
