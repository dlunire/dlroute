<?php

declare(strict_types=1);

namespace DLRoute\Core\Data;

use DLRoute\Requests\DLParamValueType;

/**
 * Encapsula el manejador completo de una ruta registrada en DLRoute.
 *
 * Agrupa en un único objeto tipado e inmutable todos los argumentos
 * necesarios para registrar una ruta: la URI, el controlador o callback
 * que se ejecutará al coincidir, los datos adicionales opcionales, el
 * tipo MIME explícito de la respuesta y los filtros de tipo para los
 * parámetros dinámicos.
 *
 * Al extender DLParamValueType, hereda el sistema de filtrado de parámetros
 * dinámicos de la ruta, permitiendo encadenar validaciones directamente
 * sobre la instancia mediante filter_by_type().
 *
 * Uso básico:
 * ```php
 * DLRoute::match(new RouteHandler(
 *     uri:        '/productos/{uuid}',
 *     controller: [ProductController::class, 'show'],
 *     mime_type:  'application/json',
 * ));
 * ```
 *
 * Uso con filtros de tipo declarados en el constructor:
 * ```php
 * DLRoute::match(new RouteHandler(
 *     uri:             '/productos/{uuid}',
 *     controller:      [ProductController::class, 'show'],
 *     handler_filters: ['uuid' => 'uuid'],
 * ));
 * ```
 *
 * @package DLRoute\Core\Data
 * @author  David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @copyright (c) 2026 DLUnire
 * @license MIT
 */
final class RouteHandler extends DLParamValueType {

    /**
     * Cantidad de filtros de tipo registrados para los parámetros dinámicos.
     *
     * Se calcula de forma diferida en «get_quantity()» y se almacena en O(1)
     * para evitar llamadas repetidas a «count()» sobre «$handler_filters».
     *
     * @var int
     */
    private readonly int $count;

    /**
     * @param string $uri URI de la ruta a registrar (e.g. `/productos/{uuid}`).
     *        Admite parámetros dinámicos obligatorios (`{param}`) y opcionales
     *        (`{param?}`).
     * @param mixed $controller Controlador o callback que se ejecutará cuando
     *        la ruta coincida con la petición HTTP. Acepta:
     *        - Un closure:  `fn(object $params) => []`
     *        - Un array:    `[ProductController::class, 'show']`
     *        - Un string:   `'nombre_de_funcion'`
     *        PHP no permite `callable` como tipo de propiedad, por lo que
     *        la validación del tipo se delega a DLRoute al momento de despachar
     *        la petición.
     * @param array|object $data Datos adicionales que se inyectarán en la
     *        respuesta. Por defecto array vacío.
     * @param string|null $mime_type Tipo MIME explícito de la respuesta.
     *        Si es null, DLRoute lo determina automáticamente según el tipo
     *        de dato devuelto por el controlador (array → JSON, string → text/html,
     *        etc.).
     * @param array<string, string> $handler_filters Filtros de tipo para los
     *        parámetros dinámicos de la ruta. La clave es el nombre del parámetro
     *        y el valor es el tipo predefinido (`uuid`, `email`, `integer`, `float`,
     *        `numeric`, `boolean`, `string`) o una expresión regular personalizada.
     *        Por defecto array vacío — sin filtros.
     */
    public function __construct(
        public readonly string $uri,
        public readonly mixed $controller,
        public readonly array|object $data = [],
        public readonly ?string $mime_type = null,
        public readonly array $handler_filters = []
    ) {}

    /**
     * Devuelve la cantidad de filtros de tipo registrados para los parámetros
     * dinámicos de la ruta.
     *
     * El valor se calcula de forma diferida en la primera llamada y se almacena
     * en «$this->count» para accesos posteriores en O(1), evitando llamadas
     * repetidas a «count()» sobre «$handler_filters».
     *
     * @return int Número de filtros registrados. Vale 0 si «$handler_filters»
     *             está vacío.
     */
    public function get_quantity(): int {
        if (!isset($this->count)) {
            $this->count = \count($this->handler_filters);
        }

        return $this->count;
    }
}