<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

/**
 * Define los identificadores utilizados para representar la identidad
 * semántica de una ruta dentro del autómata de enrutamiento.
 *
 * Los valores definidos por este enum corresponden a prefijos utilizados
 * exclusivamente en la representación interna de las rutas y no forman
 * parte de la URI expuesta al cliente.
 */
enum RouteType: string {

    /**
     * Identificador utilizado para representar una ruta que requiere
     * autenticación.
     *
     * Este prefijo se incorpora a la identidad interna de la ruta durante
     * su registro cuando esta se encuentra dentro de un contexto de
     * autenticación.
     *
     * @var string
     */
    case AUTH = "AUTH-";
}
