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
use DLRoute\Server\DLOutput;

/**
 * Excepción lanzada cuando se desea devolver un error estructurado
 * como salida JSON o similar al cliente.
 *
 * Permite incluir:
 * - Código de error HTTP.
 * - Mensaje personalizado.
 * - Información adicional opcional.
 *
 * @package DLRoute\Errors
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
final class OutputException extends RuntimeException {

    /**
     * Mensaje base por defecto
     */
    private const BASE_MESSAGE = 'Ocurrió un error inesperado';

    /**
     * Código HTTP por defecto
     */
    private const BASE_CODE = 500;

    /**
     * Información adicional que puede incluirse en la salida
     *
     * @var array<string, mixed>
     */
    private array $details = [];

    /**
     * Constructor.
     *
     * @param string|null $message Mensaje específico que complementa el mensaje base.
     * @param int|null $code Código de error HTTP (por defecto 500).
     * @param array<string, mixed> $details Información adicional que se incluirá en la salida.
     * @param Throwable|null $previous Excepción previa.
     */
    public function __construct(
        ?string $message = null,
        ?int $code = null,
        array $details = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message ?? self::BASE_MESSAGE, $code ?? self::BASE_CODE, $previous);
        $this->details = $details;
    }
}
