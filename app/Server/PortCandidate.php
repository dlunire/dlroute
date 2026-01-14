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

namespace DLRoute\Server;

/**
 * Permite determinar un puerto candidato. No se puede garantizar que el puerto obtenido
 * sea el puerto real utilizado.
 * 
 * @package DLRoute\Server
 * 
 * @version v0.0.1 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
trait PortCandidate {
    use SchemeHTTP;

    /**
     * Claves candidatas desde las cuales puede resolverse el puerto
     * efectivo de la petición.
     *
     * El orden importa: de mayor intención semántica a menor.
     *
     * @var string[]
     */
    private const PORT_KEYS = [
        'HTTP_X_FORWARDED_PORT',
        'SERVER_PORT'
    ];

    /**
     * Devuelve un número de puerto probable utilizado durante la ejecución.
     * Tome en cuenta que no siempre podría determinarse el puerto en entornos de 
     * ejecución mal configurados, que no informan del puerto o simplemente CLI.
     * 
     * Primero se intentará deducir por el esquema HTTP seleccionado el puerto. 
     * 
     * Con el objeto de permitir pruebas automatizadas, se devolverá el puerto número
     * `80` como puerto predeterminado. 
     * 
     * El objetivo es permitir construir peticiones HTTPs simuladas cuando no se utilicen 
     * el protocolo HTTP
     *
     * @param boolean $local Opcional. Solo para obtener puertos locales de ejecución siempre que sea
     *                posible, de lo contrario, devolverá `80`.
     * @return int
     */
    private static function get_likely_port(bool $local = false) {

        /** @var int $port */
        $port = self::is_https() ? 443 : 80;

        /** @var mixed $local_port */
        $local_port = $_SERVER['SERVER_PORT'] ?? $port;

        if (
            \is_numeric($local_port) &&
            self::is_valid_range(\intval($local_port)) &&
            $local
        ) {
            return \intval($local_port);
        }

        if ($port === 443) {
            return $port;
        }

        foreach (self::PORT_KEYS as $key) {
            /** @var mixed $value */
            $value = $_SERVER[$key] ?? null;

            if (!\is_numeric($value))
                continue;
            $value = \intval($value);

            if (!self::is_valid_range($value))
                continue;
            return $value;
        }

        return $port;
    }

    /**
     * Verifica si el está dentro del rango el puerto evaluado como entero previamente.
     *
     * @param int $value Valor a ser analizado
     * @return boolean
     */
    private static function is_valid_range(int $value): bool {
        return !($value < 0 || $value > 65535);
    }

    /**
     * Devuelve el puerto inferido. Es posible que el puerto no sea real, porque dependerá
     * de la configuración del entorno.
     *
     * @return integer
     */
    public static function get_port(): int {
        return self::get_likely_port();
    }

    /**
     * Devuelve el puerto local donde corre el script, siempre que sea posible.
     *
     * @return integer
     */
    public static function get_local_port(): int {
        return self::get_likely_port(local: true);
    }
}