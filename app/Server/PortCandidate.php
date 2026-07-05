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

namespace DLRoute\Server;

/**
 * Permite determinar un puerto candidato. No se puede garantizar que el puerto obtenido
 * sea el puerto real utilizado.
 *
 * @package DLRoute\Server
 *
 * @author David E Luna M <info@dlunire.dev>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
trait PortCandidate
{
    use SchemeHTTP;

    /**
     * Claves candidatas desde las cuales puede resolverse el puerto
     * efectivo de la petición.
     *
     * El orden importa: de mayor intención semántica a menor.
     *
     * @var string[]
     */
    private const PORT_KEYS = ["HTTP_X_FORWARDED_PORT", "SERVER_PORT"];

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
    private static function get_likely_port(bool $local = false): int
    {
        /** @var int $port */
        $port = self::is_https() ? 443 : 80;

        /** @var mixed $local_port */
        $local_port = $_SERVER["SERVER_PORT"] ?? $port;

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

            if (!\is_numeric($value)) {
                continue;
            }
            $value = \intval($value);

            if (!self::is_valid_range($value)) {
                continue;
            }
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
    private static function is_valid_range(int $value): bool
    {
        return !($value < 0 || $value > 65535);
    }

    /**
     * Devuelve el puerto inferido. Es posible que el puerto no sea real, porque dependerá
     * de la configuración del entorno.
     *
     * @return integer
     */
    public static function get_port(): int
    {
        return self::get_likely_port();
    }

    /**
     * Devuelve el puerto local donde corre el script, siempre que sea posible.
     *
     * @return integer
     */
    public static function get_local_port(): int
    {
        return self::get_likely_port(local: true);
    }
}
