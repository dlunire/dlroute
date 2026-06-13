<?php

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