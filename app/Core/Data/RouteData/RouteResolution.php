<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

use DLRoute\Core\Data\QueryParamValue;

/**
 * Representa el resultado de la resolución de una URI de petición.
 *
 * Transporta la información estructurada obtenida durante el análisis y la resolución de una URI,
 * estableciendo la correspondencia entre la URI procesada, el directorio base identificado, la ruta
 * lógica resultante y los datos asociados a la cadena de consulta.
 *
 * Esta estructura permite conservar los resultados de la resolución para su reutilización en las
 * distintas etapas del procesamiento de rutas, evitando iteraciones adicionales sobre los tokens
 * del analizador y facilitando el acceso a la información de la solicitud.
 *
 * Los valores se establecen durante la construcción del objeto y permanecen inmutables durante
 * su ciclo de vida.
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
     * Inicializa la instancia con la URI procesada, el directorio base identificado durante la
     * resolución, la ruta lógica resultante, la cadena de consulta original y los valores de los
     * parámetros de consulta asociados.
     *
     * La URI puede incluir la cadena de consulta cuando corresponda. El directorio y la ruta lógica
     * se representan mediante separadores `/` y comienzan desde la raíz lógica (`/`).
     *
     * La cadena de consulta se conserva por separado cuando está disponible, mientras que los
     * parámetros de consulta se proporcionan como un arreglo de instancias de {@see QueryParamValue}.
     *
     * @param string $uri URI procesada, incluyendo la cadena de consulta cuando corresponda.
     * @param string $dir Directorio base identificado durante la resolución de los segmentos de la URI.
     * @param string $route Ruta lógica reconstruida a partir de los segmentos correspondientes de la URI.
     * @param string|null $query_string Cadena de consulta original, sin el delimitador `?`, o null si no existe.
     * @param QueryParamValue[] $query_param_values Valores de los parámetros de consulta asociados a la resolución.
     */
    public function __construct(
        public readonly string $uri = "/",
        public readonly string $dir = "/",
        public readonly string $route = "/",
        public readonly ?string $query_string = null,
        public readonly array $query_param_values = []
    ) {
    }
}
