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

use DLRoute\Errors\DomainException;

/**
 * Permite obtener la dirección IP desde varias fuente o posibles fuentes. Si no 
 * es posible devolver una dirección IP, entonces, su método estático público
 * devolverá un valor nulo.
 * 
 * @package DLRoute\Server
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
trait IPAddress {

    /**
     * Permite iterar las claves disponibles donde se puedean obtener la
     * dirección IP del cliente.
     * 
     * @var string[]
     */
    private const IP_KEYS = [
        'HTTP_CF_CONNECTING_IP',
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