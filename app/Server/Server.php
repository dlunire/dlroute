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

use DLRoute\Config\RealPath;
use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;
use DLRoute\Enums\Methods;
use DLRoute\Errors\RouteException;
use DLRoute\Interfaces\Routing\LexerSymbolInterface;
use DLRoute\Interfaces\ServerInterface;

class Server implements ServerInterface, LexerSymbolInterface {
    use Domain, IPAddress, PortCandidate;

    /**
     * Devuelve el valor de cada caso de {@see Methods}, en el orden de declaración.
     *
     * Es una lista global: no depende de la ruta solicitada ni de `PHP_SAPI`, y no representa los
     * métodos permitidos para una ruta concreta.
     *
     * @param bool $exclude_special_methods Si es `true`, omite los métodos de
     *        {@see self::EXCLUDES_METHODS} (por ejemplo, `CLI`).
     *
     * @return list<non-empty-string>
     */
    private static function get_all_methods(bool $exclude_special_methods = false): array {
        $methods = Methods::cases();
        $processed_methods = [];

        foreach ($methods as $method) {
            if ((self::EXCLUDES_METHODS[$method->name] ?? false) && $exclude_special_methods) continue;
            $processed_methods[] = $method->value;
        }

        return $processed_methods;
    }

    /** {@inheritDoc} */
    public static function get_http_allow_methods(): array {
        return self::get_all_methods(exclude_special_methods: true);
    }

    /** {@inheritDoc} */
    public static function get_allow_methods(): array {
        return self::get_all_methods();
    }

    /** {@inheritDoc} */
    public static function get_uri(): string {
        return RequestURILexer::from()->get_uri();
    }

    /**
     * Obtiene la URI decodificada mediante el analizador léxico de solicitudes.
     *
     * @return non-empty-string URI decodificada.
     */
    public static function get_decoded_uri(): string {
        return RequestURILexer::from()->get_decoded_uri();
    }

    /** {@inheritDoc} */
    public static function get_method(): string {
        /**
         * Método de solicitud capturado durante la ejecución.
         *
         * `CLI` es el valor predeterminado cuando no existe un método HTTP disponible en el
         * entorno de ejecución.
         *
         * @var non-empty-string $method
         */
        $method = "CLI";

        /** @var non-empty-string|null $http_method */
        $http_method = $_SERVER['REQUEST_METHOD'] ?? NULL;

        if (\is_string($http_method)) {
            $method = $http_method;
        }

        # Es posible que esta excepción nunca se lance, pero se implementa por si de alguna forma
        # se cuela un "método" vacío.
        if (\trim($method) === '') {
            throw new RouteException("No hay método definido", 400);
        }

        if (Methods::tryFrom(value: $method) === null) {
            /** @var non-empty-string $allowed_methods */
            $allowed_methods = join(
                separator: ", ",
                array: self::get_all_methods(exclude_special_methods: true)
            );

            header(header: "Allow: {$allowed_methods}");
            throw new RouteException("El método HTTP «{$method}» no está permitido. Métodos permitidos: {$allowed_methods}.", 405);
        }

        return $method;
    }

    public static function get_ipaddress(): ?string {
        return self::get_ip();
    }

    public static function get_user_agent(): string {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? \php_uname();
        return $user_agent;
    }

    public static function get_document_root(): string {
        return RealPath::from()->get_document_root();
    }

    public function get_entry_point(): string {
        return RealPath::from()->get_entry_point();
    }

    public function get_entry_point_file(): string {
        return RealPath::from()->get_entry_point_file();
    }

    public static function is_query(): bool {
        return self::get_method() === 'QUERY';
    }

    public static function is_get(): bool {
        return self::get_method() === "GET";
    }

    public static function is_post(): bool {
        return self::get_method() === "POST";
    }

    public static function is_head(): bool {
        return self::get_method() === "HEAD";
    }

    public static function is_options(): bool {
        return self::get_method() === "OPTIONS";
    }

    public static function is_put(): bool {
        return self::get_method() === "PUT";
    }

    public static function is_patch(): bool {
        return self::get_method() === "PATCH";
    }

    public static function is_delete(): bool {
        return self::get_method() === "DELETE";
    }

    public static function is_cli(): bool {
        return PHP_SAPI === self::CLI;
    }

    public static function get_base_url(): string {
        /** @var string $http_host */
        $http_host = self::get_host();

        /** @var string $scheme */
        $scheme = self::get_scheme();

        /** @var non-empty-string $dir */
        $dir = self::get_dir();

        /** @var string $port */
        $port = self::HIDDEN_PORT[self::get_port()] ?? false
            ? ""
            : ":" . self::get_port();

        /** @var string $host */
        $host = "{$scheme}://{$http_host}{$port}";

        if ($dir !== self::SLASH) {
            $host = $dir[0] === self::SLASH
                ? "{$host}{$dir}"
                : "{$host}/{$dir}";
        }

        return $host;
    }

    public static function get_route(): string {
        return RequestURILexer::from()->get_route();
    }

    /** @inheritDoc */
    public static function get_dir(): string {
        return RequestURILexer::from()->get_dir();
    }
}
