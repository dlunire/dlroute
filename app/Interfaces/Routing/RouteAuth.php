<?php

declare(strict_types=1);

namespace DLRoute\Interfaces\Routing;

interface RouteAuth {
    /**
     * Registra un conjunto de rutas dentro de un contexto que requiere autenticación.
     *
     * El contexto se establece antes de ejecutar el callback y se restaura automáticamente al finalizar
     * su ejecución. De esta forma, todas las rutas registradas dentro del callback adquieren la semántica de
     * autenticación requerida sin necesidad de modificar individualmente sus declaraciones.
     *
     * El contexto únicamente afecta al proceso de registro de rutas. La autenticación de la sesión se determina
     * posteriormente durante la resolución de la ruta y no se realiza como parte de este método.
     *
     * El uso de un contexto evita que el desarrollador tenga que manipular directamente el estado interno de
     * autenticación y permite agrupar múltiples rutas bajo una misma declaración semántica:
     *
     * ```
     * <?php
     * $auth->require_auth(function () {
     *     DLRoute::get('/profile', ...);
     *     DLRoute::get('/settings', ...);
     *     DLRoute::get('/dashboard', ...);
     * });
     * ```
     *
     * Las rutas registradas dentro del contexto son representadas internamente mediante el identificador
     * definido por {@see RouteType::AUTH}, sin modificar la URI expuesta al cliente.
     *
     * El bloque {@see finally} garantiza que el contexto sea restaurado incluso cuando la ejecución del callback
     * produce una excepción, evitando que el estado de registro se propague accidentalmente a
     * declaraciones posteriores.
     *
     * @param callable $fn Callback que contiene las rutas que requieren autenticación.
     * @return void
     */
    public function require_auth(callable $fn): void;
}
