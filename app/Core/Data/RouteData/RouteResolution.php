<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

/**
 * Representa el resultado de la resolución de una URI de petición.
 *
 * Transporta la información estructurada obtenida durante el análisis y
 * resolución de una URI, estableciendo la correspondencia entre la URI
 * procesada, el directorio base identificado y la ruta lógica resultante.
 *
 * Esta estructura permite conservar los resultados de la resolución
 * para su reutilización en las distintas etapas del procesamiento de
 * rutas, evitando iteraciones adicionales sobre los tokens del analizador.
 *
 * Los valores se establecen durante la construcción del objeto y
 * permanecen inmutables durante su ciclo de vida.
 *
 * @package DLRoute\Core\Data\RouteData
 * @version 1.0.0
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RouteResolution {

    /**
     * Construye una nueva representación del resultado de resolución.
     *
     * @param string $uri URI procesada, incluyendo la cadena de consulta
     *                    cuando corresponda.
     * @param string $dir Directorio base identificado durante la resolución
     *                    de los segmentos de la URI.
     * @param string $route Ruta lógica reconstruida a partir de los
     *                     segmentos correspondientes de la URI.
     */
    public function __construct(
        public readonly string $uri = "/",
        public readonly string $dir = "/",
        public readonly string $route = "/"
    ) {
    }
}