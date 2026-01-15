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

/**
 * Permite determinar el esquema correcto para el contexto adecuado, es decir, dentro
 * del protocolo HTTP si se trata de HTTP o HTTPs.
 * 
 * Incluso, en contextos de ejecución de línea de comando (desde una terminal) devolverá
 * `http`.
 * 
 * @package DLRoute\Server
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
trait SchemeHTTP {

    private const SCHEME_KEYS = [
        'HTTP_X_FORWARDED_PROTO',
        'REQUEST_SCHEME',
        'HTTPS'
    ];

    /**
     * Aquí, encontrar el esquema sí es determinista. En el caso de que se ejecute en 
     * modo CLI no hay esquema protocolo HTTP disponible, sin embargo, se devolverá 
     * `http` por defecto, incluso, si se ejecuta desde una CLI.
     * 
     * El objetivo buscado con esto es facilitar las pruebas automatizadas para simular
     * un host para una petición HTTP simulada.
     *
     * @return non-empty-string
     */
    private static function determine_scheme() {

        foreach (self::SCHEME_KEYS as $key) {
            if (!\array_key_exists($key, $_SERVER))
                continue;

            /** @var mixed $value */
            $value = $_SERVER[$key];

            if (self::is_likely_https($value))
                return 'https';
        }

        return 'http';
    }

    /**
     * Devuelve `true` si detecta un candidado `https` potencial. Si no es posible, entonces,
     * devolverá `false` para indicar que no se encontró.
     *
     * @param mixed $value Valor a ser analizado para determinar el esquema HTTP.
     * @return boolean
     */
    private static function is_likely_https(mixed $value): bool {
        if (!\is_string($value)) {
            return false;
        }

        $value = strtolower(trim($value));

        if ($value === 'on' || $value === '1' || $value === 'true' || $value === 'https') {
            return true;
        }

        return false;
    }

    /**
     * Devuelve el Scheme adecuado para el contexto. El esquema devuelto será
     * siempre `https` o `http`, siempre que el contexto lo permita.
     * 
     * **Nota:** si se ejecuta desde un desde un CLI el esquema devuelto será `http`
     * con el objeto de permitir simular las pruebas automatizadas en `hosts` simulados.
     *
     * @return string
     */
    public static function get_scheme(): string {
        return self::determine_scheme();
    }

    /**
     * Si el protocolo HTTP es HTTPs, entonces, devolverá `true`. En cualquier caso,
     * será `false` para cualquier coso.
     *
     * @return boolean
     */
    public static function is_https(): bool {
        return self::get_scheme() === "https";
    }
}