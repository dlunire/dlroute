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
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
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
