<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\Auth;

/**
 * DTO inmutable que encapsula la definición de una ruta protegida o
 * autenticada dentro de la capa de enrutamiento de DLRoute.
 *
 * Mantiene la asociación directa entre el verbo/método HTTP utilizado
 * (p. ej., `GET`, `POST`, `DELETE`) y el patrón de la URI correspondiente.
 * Se utiliza como estructura de datos de solo lectura (readonly) para
 * garantizar que la configuración de rutas autenticadas permanezca
 * inalterada durante el análisis léxico y el despacho de peticiones.
 *
 * @package DLRoute\Core\Data\Auth
 * @author David E Luna M <info@dlunire.dev>
 * @copyright 2026 DLUnire
 * @license AGPL-3.0-or-later
 */
final class RoutesAuth {

    /**
     * Nombre del método o verbo HTTP asociado a la ruta protegida
     * (p. ej. `GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
     *
     * @var string $method_name
     */
    public readonly string $method_name;

    /**
     * Patrón o representación de la URI de la ruta autenticada
     * (p. ej. `/dashboard`, `/api/user/{id}`).
     *
     * @var string $route
     */
    public readonly string $route;

    /**
     * Inicializa la estructura inmutable de la ruta autenticada.
     *
     * @param string $method_name Verbo HTTP en mayúsculas (p. ej. 'GET', 'POST').
     * @param string $route Patrón de la URI a registrar.
     */
    public function __construct(string $method_name, string $route) {
        $this->method_name = \strtoupper(\trim($method_name));
        $this->route = \trim($route);
    }
}
