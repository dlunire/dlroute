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
 * Permite determinar el dominio o nombre de host que se implementará en la aplicación, pero
 * también, imponerlo si así lo desea para casos muy concretos.
 * 
 * @package DLRoute\Server
 * 
 * @author David E Luna M <info@dlunire.dev>
 * @copyright (c) 2026 David E Luna M
 * @license AGPL-3.0 license
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
    private static ?string $hostname = null;

    private const KEYS = [
        "HTTP_X_FORWARDED_HOST",
        "HTTP_HOST",
        "SERVER_NAME"
    ];

    /**
     * Determina el host o dominio de la petición actual.
     *
     * No lanza excepciones. Si no es posible determinar el host, devuelve `null`; es lo que ocurre al
     * ejecutar desde la terminal (CLI), donde no existe un host de petición.
     *
     * Orden de resolución:
     *
     * 1. Si el host externo es obligatorio (`$required`), se devuelve ese valor y no se consulta `$_SERVER`.
     * 2. En otro caso, se recorren en orden las claves de {@see self::KEYS} (`HTTP_X_FORWARDED_HOST`,
     *    `HTTP_HOST` y `SERVER_NAME`) y se toma la primera cuyo valor sea una cadena no vacía, sin los
     *    espacios de los extremos.
     * 3. Si ninguna clave sirve, se usa el host externo opcional, si fue establecido con
     *    {@see self::set_external_host()}.
     * 4. Si tampoco existe, devuelve `null`.
     *
     * @return non-empty-string|null Host determinado, o `null` si no se pudo determinar.
     */
    private static function determine_host(): ?string {
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
            return null;
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

        self::$hostname = trim($host);
        self::$required = $required;
    }

    /**
     * Devuelve el host externo, si éste fue establecido previamente, de los contario,
     * devolverá un valor nulo.
     *
     * @return string|null
     */
    private static function get_external_host(): ?string {
        return self::$hostname;
    }

    /**
     * Devuelve el host de la aplicación.
     *
     * Orden de resolución:
     *
     * 1. El host que determina {@see self::determine_host()}: el host externo obligatorio, o el de la
     *    petición (`HTTP_X_FORWARDED_HOST`, `HTTP_HOST` o `SERVER_NAME`), o el host externo opcional.
     * 2. Si no hay ninguno (por ejemplo, en la terminal), el nombre de la máquina que devuelve
     *    `gethostname()`.
     * 3. Si `gethostname()` falla o devuelve una cadena vacía o solo espacios, el valor de `$hostname`.
     *
     * Si `$hostname` está vacío o solo tiene espacios, se sustituye por `dlunire`, de modo que el
     * resultado nunca es una cadena vacía.
     *
     * En la terminal (CLI), el resultado del paso 2 es el nombre de la máquina, no el dominio de la
     * aplicación, y no se usa DNS inverso para inferirlo. Para fijar el dominio, use
     * {@see self::set_external_host()}.
     *
     * @param string $hostname Valor de último recurso, usado solo cuando `gethostname()` no devuelve un
     *                         nombre utilizable. Si está vacío o solo tiene espacios, se usa `dlunire`.
     *                         Por defecto: `dlunire`.
     * @return non-empty-string Host determinado.
     */
    public static function get_host(string $hostname = "dlunire"): string {
        /** @var string|false $inner_hostname */
        $inner_hostname = \gethostname();

        if (\trim($hostname) === '') {
            $hostname = 'dlunire';
        }

        $host = self::determine_host() ?? ($inner_hostname === false || \trim($inner_hostname) === ''
            ? $hostname
            : $inner_hostname
        );

        return $host;
    }
}
