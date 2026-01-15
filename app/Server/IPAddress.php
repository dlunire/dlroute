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

use DLRoute\Errors\DomainException;

/**
 * Permite obtener la dirección IP desde varias fuente o posibles fuentes. Si no 
 * es posible devolver una dirección IP, entonces, su método estático público
 * devolverá un valor nulo.
 * 
 * @package DLRoute\Server
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
trait IPAddress {

    /**
     * Permite iterar las claves disponibles donde se puedean obtener la
     * dirección IP del cliente.
     * 
     * @var string[]
     */
    private const IP_KEYS = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];

    /**
     * Permite determinar la dirección IP del cliente HTTP, en el caso de que sea
     * posible; caso contrario, devolverá un valor nulo.
     *
     * @return string|null
     */
    private static function resolve_ip_candidate(): ?string {

        /** @var non-empty-string|null $domain */
        $ip = null;

        foreach (self::IP_KEYS as $key) {
            /** @var string|null $domain */
            $domain = self::get_likely_ip($_SERVER[$key] ?? null);
            if (\is_string($domain))
                break;
        }

        return $domain;
    }

    /**
     * Devuelve una cadena texto no vacía que podría ser una dirección IP válida o no
     * o un valor nulo si no cumple con el formato de cadena (no formato de IP) esperado.
     *
     * @param mixed $input Entrada a ser analizada
     * @return string|null
     */
    private static function get_likely_ip(mixed $input): ?string {
        if (!\is_string($input))
            return null;
        $input = trim($input);

        if ($input === "")
            return null;
        return $input;
    }

    /**
     * Devuelve la dirección IP previametne determinada o resuelta o un valor
     * nulo si no se pudo determinar.
     * 
     * No se puede garantizar que devuelva una dirección IP real si el cliente HTTP
     * envía una dirección como cliente que no es la que le pertenece.
     *
     * @return string|null
     */
    protected static function get_ip(): ?string {
        return self::get_standard_ip();
    }

    /**
     * Devuelve la dirección IP previamente determinada. Si no fue posible
     * determinarla, entonces, devolverá un valor nulo.
     *
     * @return string|null
     */
    private static function get_standard_ip(): ?string {
        /** @var mixed $ip */
        $ip = filter_var(
            self::resolve_ip_candidate(),
            FILTER_VALIDATE_IP
        );

        if (!\is_string($ip)) {
            $ip = null;
        }

        if (\is_string($ip) && empty(trim($ip))) {
            $ip = null;
        }

        return $ip;
    }

    /**
     * Devuelve la dirección IP del peer de conexión obtenida desde la clave
     * `REMOTE_ADDR`.
     *
     * Este valor corresponde a la dirección IP desde la cual el entorno de
     * ejecución recibió la conexión, y no necesariamente a la dirección IP
     * real del cliente HTTP final (por ejemplo, en presencia de proxies,
     * balanceadores o CDNs).
     *
     * En contextos donde no existe un entorno HTTP (CLI), el valor puede
     * ser nulo.
     *
     * @return string|null
     */

    public static function get_remote_addr(): ?string {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Indica si existe una discrepancia entre la dirección IP del peer de conexión
     * (`REMOTE_ADDR`) y la dirección IP resuelta a partir de las cabeceras HTTP.
     *
     * Una diferencia entre ambas direcciones sugiere la posible presencia de
     * intermediarios (por ejemplo, proxies, balanceadores o CDNs), pero no constituye
     * una prueba concluyente de su uso.
     *
     * Este método se basa en heurísticas simples y no realiza validaciones
     * semánticas profundas sobre la dirección IP. En versiones futuras, podrán
     * incorporarse capas adicionales de análisis para determinar con mayor
     * precisión el tipo de intermediación presente.
     *
     * @return bool Devuelve `true` si las direcciones IP no coinciden; `false` en caso contrario.
     */
    public static function is_likely_proxy(): bool {
        return self::get_standard_ip() !== self::get_remote_addr();
    }
}