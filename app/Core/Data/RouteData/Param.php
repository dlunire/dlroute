<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

/**
 * Representa un parámetro obtenido durante el análisis léxico y la resolución de rutas.
 *
 * Almacena el nombre del parámetro, su valor y su representación decodificada, manteniendo
 * separados los datos recibidos y los obtenidos mediante los procesos de decodificación o
 * traducción aplicados durante el análisis.
 *
 * El tipo de `value` es `mixed` porque depende del tipo declarado para el parámetro mediante
 * `set_type()` (véase {@see \DLRoute\Enums\Type}): un parámetro declarado como entero
 * contiene un entero, uno declarado como cadena contiene una cadena, y así sucesivamente.
 * El editor no puede inferir ese tipo por sí solo, por lo que se indica anotando la variable
 * con `@var`; por ejemplo, `@var int $page` al leer `$params->page->value`.
 *
 * `decoded_value` solo se establece cuando el tipo del parámetro es cadena; para cualquier
 * otro tipo vale `null`.
 *
 * El nombre y `decoded_value` admiten `null`, y `value` es `mixed`, por lo que también puede
 * ser `null`. Una instancia con los tres campos en `null` representa la ausencia de un
 * parámetro: es el valor que {@see RouteParam::__get()} devuelve cuando el nombre solicitado
 * no fue capturado, de modo que quien consulta siempre recibe un {@see Param} y nunca `null`.
 *
 * Sus propiedades son públicas y de solo lectura (`readonly`), por lo que sus valores
 * se establecen durante la construcción y no pueden reasignarse posteriormente.
 *
 * La construcción directa está restringida mediante un constructor privado. Las instancias
 * se crean a través de {@see self::new()}, proporcionando un punto de entrada uniforme
 * para su inicialización.
 *
 * @package DLRoute\Core\Data\RouteData
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class Param {

    /**
     * Construye una instancia con los datos del parámetro.
     *
     * El constructor es privado para centralizar la creación de instancias
     * mediante {@see self::new()}.
     *
     * Todos los argumentos son opcionales y valen `null` por defecto. Si se omiten todos,
     * la instancia resultante representa un parámetro inexistente.
     *
     * @param string|null $name Nombre asociado al parámetro, o `null` si no existe.
     * @param mixed $value Valor del parámetro, con el tipo declarado mediante `set_type()`,
     *                     o `null` si no existe.
     * @param string|null $decoded_value Valor obtenido después de la decodificación o
     *                                   traducción; solo se establece cuando el tipo del
     *                                   parámetro es cadena, y es `null` en otro caso.
     */
    private function __construct(
        public readonly ?string $name = null,
        public readonly mixed $value = null,
        public readonly ?string $decoded_value = null
    ) {
    }

    /**
     * Crea una nueva instancia de {@see self}.
     *
     * Permite inicializar los datos del parámetro sin exponer directamente
     * el constructor de la clase.
     *
     * Si se invoca sin argumentos, o con todos en `null`, devuelve un parámetro vacío,
     * que es la representación de un parámetro no capturado.
     *
     * @param string|null $name Nombre asociado al parámetro.
     * @param mixed $value Valor del parámetro, con el tipo declarado mediante `set_type()`.
     * @param string|null $decoded_value Valor decodificado o traducido; solo aplica a
     *                                   parámetros de tipo cadena.
     *
     * @return static Nueva instancia de la clase.
     */
    public static function new(
        ?string $name = null,
        mixed $value = null,
        ?string $decoded_value = null
    ): static {
        return new static(
            name: $name,
            value: $value,
            decoded_value: $decoded_value
        );
    }
}
