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
 * Permite determinar el esquema correcto para el contexto adecuado, es decir, dentro
 * del protocolo HTTP si se trata de HTTP o HTTPs.
 * 
 * Incluso, en contextos de ejecución de línea de comando (desde una terminal) devolverá
 * `http`.
 * 
 * @package DLRoute\Server
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
 */
trait SchemeHTTP {

    private const SCHEME_KEYS = [
        'HTTP_X_FORWARDED_PROTO',
        'REQUEST_SCHEME',
        'HTTPS'
    ];

    /**
     * Determina el esquema de la petición actual.
     *
     * En la terminal (`PHP_SAPI === 'cli'`) no existe un esquema HTTP, y devuelve `cli` sin consultar
     * `$_SERVER`. Así se distingue una ejecución por terminal de una petición HTTP.
     *
     * En cualquier otro contexto devuelve `https` si alguna de las claves de {@see self::SCHEME_KEYS}
     * (`HTTP_X_FORWARDED_PROTO`, `REQUEST_SCHEME` o `HTTPS`) existe en `$_SERVER` con un valor que
     * {@see self::is_likely_https()} reconozca: `on`, `1`, `true` o `https`, sin distinguir mayúsculas
     * ni espacios en los extremos. Las claves se comprueban en ese orden. Si ninguna lo cumple, devuelve
     * `http`.
     *
     * @return non-empty-string `cli`, `http` o `https`.
     */
    private static function determine_scheme() {
        if (PHP_SAPI === 'cli') return PHP_SAPI;

        foreach (self::SCHEME_KEYS as $key) {
            if ($_SERVER[$key] ?? null) {
                continue;
            }

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
     * Devuelve el esquema del contexto actual.
     *
     * Delega en {@see self::determine_scheme()}:
     *
     * - En la terminal (CLI), devuelve `cli`: no existe un esquema HTTP.
     * - En una petición HTTP, devuelve `https` si `$_SERVER` indica una conexión segura y `http` en
     *   cualquier otro caso.
     *
     * Por tanto, el valor devuelto no siempre es `http` o `https`. Quien use este método para armar una
     * URL debe tener en cuenta el caso `cli`.
     *
     * @return non-empty-string `cli`, `http` o `https`.
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
