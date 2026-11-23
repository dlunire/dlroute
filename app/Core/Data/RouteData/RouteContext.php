<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

/**
 * Representa el contexto de una ruta resuelta.
 *
 * Cuando no hay una ruta definida, el contexto sigue siendo válido y sus valores
 * son predecibles:
 *
 * - `route` es siempre la ruta base `/`.
 * - `registered_route` es `null`.
 * - Cualquier parámetro consultado en `params` devuelve un {@see Param} con valores `null`.
 *
 * Son dos reglas distintas, según el origen de la ruta:
 *
 * - La ruta de la petición, obtenida mediante `RequestURILexer::from()`,
 *   `DLServer::get_route()` o {@see RouteMatcher::get_context()}, es siempre la ruta
 *   base `/` cuando no hay una definida.
 * - La ruta registrada mediante `DLRoute::<método>(...)` es `null` mientras no se registre.
 *
 * La construcción directa está restringida mediante un constructor privado. Las instancias
 * se crean a través de {@see self::new()}.
 *
 * @package DLRoute\Core\Data\RouteData
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RouteContext {

    /**
     * Construye una instancia con los datos del contexto de la ruta.
     *
     * El constructor es privado para centralizar la creación de instancias
     * mediante {@see self::new()}.
     *
     * @param string $route Ruta solicitada por el cliente.
     * @param string|null $registered_route Ruta registrada que coincidió con la solicitada,
     *                                      o `null` si no hay ninguna.
     * @param RouteParam $params Parámetros capturados durante la resolución, consultables
     *                           por nombre (véase {@see RouteParam::__get()}).
     */
    private function __construct(
        public readonly string $route,
        public readonly ?string $registered_route,
        public readonly RouteParam $params
    ) {
    }

    /**
     * Crea una nueva instancia de {@see self}.
     *
     * Permite inicializar el contexto sin exponer directamente el constructor de la clase.
     * Si no se indica la ruta solicitada, se usa la ruta base `/`; si no se indica la ruta
     * registrada, queda en `null`.
     *
     * @param RouteParam $params Parámetros capturados durante la resolución.
     * @param string $route Ruta solicitada por el cliente. Por defecto, la ruta base `/`.
     * @param string|null $registered_route Ruta registrada que coincidió con la solicitada,
     *                                      o `null` si no hay ninguna.
     *
     * @return static Nueva instancia de la clase.
     */
    public static function new(
        RouteParam $params,
        string $route = "/",
        ?string $registered_route = null
    ): static {
        return new static(
            route: $route,
            registered_route: $registered_route,
            params: $params
        );
    }
}
