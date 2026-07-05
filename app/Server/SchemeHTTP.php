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