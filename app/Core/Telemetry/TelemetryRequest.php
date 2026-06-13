<?php

declare(strict_types=1);

namespace DLRoute\Interfaces\Telemetry;

use DLRoute\Core\Data\Telemetry;
use Override;

/**
 * Implementación concreta de `TelemetryInterface` para la captura de telemetría
 * en el contexto de una petición HTTP.
 *
 * Delega la construcción del objeto de diagnóstico a `Telemetry`, que encapsula
 * el estado inmutable del entorno de ejecución en el momento de la llamada.
 *
 * @package DLRoute\Interfaces\Telemetry
 * @author  David E. Narváez <david.narvaez@dlunire.dev>
 * @license MIT
 */
final class TelemetryRequest implements TelemetryInterface {

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function telemetry(string $message = ""): Telemetry {
        return new Telemetry($message);
    }
}