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

namespace DLRoute\Interfaces\Telemetry;

use DLRoute\Core\Data\Telemetry;

interface TelemetryInterface {
    /**
     * Devuelve una instantánea inmutable de la telemetría de la petición actual.
     *
     * Captura en caliente el estado del entorno de ejecución, los metadatos de red,
     * las cabeceras HTTP y el mapa del enrutador en un objeto de diagnóstico dedicado,
     * permitiendo evaluar el comportamiento y rendimiento del servidor en cualquier
     * punto del ciclo de vida de la petición.
     *
     * @param string $message Etiqueta descriptiva que se adjunta al objeto de telemetría
     *                        para identificar el punto de diagnóstico. Se incluye como
     *                        campo en el objeto `Telemetry` retornado. Si se omite, el
     *                        campo queda vacío.
     * @return Telemetry Instantánea inmutable del entorno de ejecución en el momento de la llamada.
     */
    public static function telemetry(string $message = ""): Telemetry;
}