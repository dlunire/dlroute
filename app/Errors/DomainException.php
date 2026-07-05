<?php

/**
 * DLUnire
 * Copyright (C) 2026 David E Luna M
 *
 * Operando bajo el establecimiento de comercio "DLUnire",
 * NIT 700551569-1, matrícula mercantil Nº 10007069
 * (matrícula mercantil personal Nº 10007068).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace DLRoute\Errors;

use RuntimeException;
use Throwable;

/**
 * Excepción lanzada cuando no es posible resolver el dominio
 * o el host desde el contexto de ejecución.
 *
 * Puede representar:
 * - Fallos de resolución del entorno (headers, proxy, host inexistente).
 * - Errores de configuración relacionados con el dominio.
 *
 * @package DLRoute\Errors
 * 
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
final class DomainException extends RuntimeException {
    /**
     * Mensaje genérico en caso de que no definan uno durante el lanzamiento de
     * esta excepción
     */
    private const BASE_MESSAGE = 'Dominio no resuelto o host no encontrado';

    /**
     * Constructor.
     *
     * @param string|null $message Mensaje específico que complementa el mensaje base.
     * @param int $code Código de error (por defecto 500).
     * @param Throwable|null $previous Excepción previa.
     */
    public function __construct(?string $message = null, int $code = 500, ?Throwable $previous = null) {
        parent::__construct(self::resolve_message($message), $code, $previous);
    }

    /**
     * Resuelve el mensaje. Si no pasa el mensaje a través del constructor durante la excepción,
     * entonces utilizará el mensaje genérico de `DomainException::BASE_MESSAGE`.
     *
     * @param string|null $message Mensaje que será recibido por el constructor.
     * @return string
     */
    private static function resolve_message(?string $message): string {
        return $message ?? self::BASE_MESSAGE;
    }
}
