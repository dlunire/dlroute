<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

/**
 * Representa los controladores asociados a los contextos público y privado de una ruta.
 *
 * Almacena de forma independiente el controlador registrado para cada contexto de la ruta,
 * permitiendo que una misma definición de ruta disponga de un controlador público y otro privado.
 *
 * Los controladores se almacenan como valores de tipo `mixed`, debido a las restricciones
 * del sistema de tipos de PHP para la declaración de tipos compuestos en este contexto.
 *
 * La validación y resolución del controlador corresponden a las capas encargadas del registro
 * y procesamiento de la ruta, mientras que esta estructura se limita a transportar los valores
 * asociados a cada contexto.
 *
 * Un valor `null` indica que no se ha definido un controlador para el contexto correspondiente
 * durante el registro de la ruta.
 *
 * La estructura es inmutable después de su construcción.
 *
 * @package DLRoute\Core\Data\RouteData
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RouteContext {

    /**
     * Construye una nueva estructura de controladores de contexto.
     *
     * @param mixed $private_controller Controlador registrado para el contexto privado de la ruta.
     * @param mixed $public_controller Controlador registrado para el contexto público de la ruta.
     */
    public function __construct(
        public readonly mixed $private_controller,
        public readonly mixed $public_controller
    ) {
    }
}
