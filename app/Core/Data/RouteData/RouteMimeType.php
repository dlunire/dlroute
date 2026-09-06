<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

/**
 * Representa los tipos MIME asociados a los contextos público y privado de una ruta.
 *
 * Almacena el tipo MIME definido mediante el parámetro `$mime_type` durante el registro de una ruta,
 * diferenciando entre su contexto público y privado.
 *
 * Un valor `null` indica que no se definió un tipo MIME para el contexto correspondiente durante
 * el registro de la ruta.
 *
 * @package DLRoute
 * @license AGPL-3.0-or-later
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 */
final class RouteMimeType {

    /**
     * Construye una nueva estructura de tipos MIME para una ruta.
     *
     * @param ?string $private_mimetype Tipo MIME definido para el contexto público
     * de la ruta durante su registro.
     * @param ?string $public_mimetype Tipo MIME definido para el contexto privado
     * de la ruta durante su registro.
     */
    public function __construct(
        public readonly ?string $private_mimetype,
        public readonly ?string $public_mimetype,
    ) {
    }
}
