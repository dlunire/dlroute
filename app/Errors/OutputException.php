<?php
/**
 * Copyright (c) 2025 David E Luna M
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @license MIT
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
 * @license MIT
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
