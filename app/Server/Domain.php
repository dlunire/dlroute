<?php
/**
 * Copyright (c) 2026 David E Luna M
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

namespace DLRoute\Server;

use DateObjectError;
use DLRoute\Errors\DomainException;

/**
 * Permite determinar el dominio o nombre de host que se implementará en la aplicación, pero
 * también, imponerlo si así lo desea para casos muy concretos.
 * 
 * @package DLRoute\Server
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
trait Domain {

    /**
     * Indica si el dominio o host personalizado que establece el desarrollador es opcional
     * es decir, cuando haya fallado la búsqueda del dominio; u obligatoria, que es cuando
     * se ke indica directamente a la aplicación que se se buscará ningún dominio, porque usará
     * el que le has establecido.
     * 
     * @var boolean $required
     */
    private static bool $required = false;

    /**
     * Nombre de host personalizado
     *
     * @var string|null $host
     */
    private static ?string $host = null;

    private const KEYS = [
        "HTTP_X_FORWARDED_HOST",
        "HTTP_HOST",
        "SERVER_NAME"
    ];

    /**
     * Permite determinar el Host o dominio utilizado durante la petición. Si el host no se
     * encuentra, lanzará una excepción de tipo DomainException
     *
     * @return string
     * @throws DomainException;
     */
    private static function determine_host(): string {
        if (self::$required) {
            return self::get_external_host();
        }

        /** @var non-empty-string|null $domain */
        $domain = null;

        foreach (self::KEYS as $key) {
            /** @var string|null $domain */
            $domain = self::get_value($_SERVER[$key] ?? null);
            if (\is_string($domain)) break;
        }
        
        if ($domain === null && self::get_external_host() === null) {
            throw new DomainException("No se pudo determinar el dominio");
        }

        return $domain ?? self::get_external_host();
    }

    /**
     * Devuelve una cadena o vacía o un valor nulo, incluso, si la codena contiene espacios
     * en blanco
     *
     * @param mixed $input Entrada a ser analizada
     * @return string|null
     */
    private static function get_value(mixed $input): ?string {
        if (!\is_string($input)) {
            return null;
        }

        if (empty(trim($input))) return null;

        return trim($input);
    }

    /**
     * Permite establecer el host externo en el caso de que haya fallado encontrar un dominio
     * o host válido cuando el segundo parámetro tiene como argumento el valor `false`. Es decir,
     * si no existe, se establecerá el dominio impuesto por el desarrollador.
     * 
     * Si se pasa como argumento `true` como valor del parámetro `$require`, el dominio establecido
     * en el primer parámetro será el único que se tomará en cuenta.
     *
     * @param string $host Dominio externo a ser implementado
     * @param boolean $required Indica si el dominio externo es opcional u obligatorio.
     * @return void
     * 
     * @throws DomainException
     */
    public static function set_external_host(string $host, bool $required = false): void {
        if (empty(trim($host))) {
            throw new DomainException(message: "domain: El dominio o nombre de host personalizado no puede estar vacío");
        }

        self::$host = trim($host);
        self::$required = $required;
    }

    /**
     * Devuelve el host externo, si éste fue establecido previamente, de los contario,
     * devolverá un valor nulo.
     *
     * @return string|null
     */
    private static function get_external_host(): ?string {
        return self::$host;
    }

    /**
     * Devuelve el host previamente determinado internamente.
     *
     * @return string
     */
    public static function get_host(): string {
        return self::determine_host();
    }
}