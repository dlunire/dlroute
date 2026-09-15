<?php

declare(strict_types=1);

namespace DLRoute\Enums;

/**
 * Tipos predefinidos admitidos para la validación de parámetros dinámicos de una ruta.
 *
 * Se utiliza junto a `RouteBuilder::set_type()` para declarar el tipo esperado de un
 * parámetro capturado de la URI, en lugar de aceptar un nombre de tipo suelto como
 * cadena de texto. Cada caso corresponde a un método de validación (`is_integer()`,
 * `is_uuid()`, etc.) que el enrutador ejecuta sobre el valor capturado.
 *
 * @example
 * ```php
 * DLRoute::get('/productos/{id}', [ProductoController::class, 'show'])
 *     ->set_type(['id' => Type::INTEGER]);
 * ```
 *
 * @package DLRoute\Enums
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
enum Type {

    /**
     * Valida que el valor del parámetro sea un número entero.
     */
    case INTEGER;

    /**
     * Valida que el valor del parámetro sea un número de punto flotante.
     */
    case FLOAT;

    /**
     * Valida que el valor del parámetro sea numérico, con o sin decimales.
     */
    case NUMERIC;

    /**
     * Valida que el valor del parámetro sea un booleano.
     */
    case BOOLEAN;

    /**
     * Valida que el valor del parámetro sea una cadena de texto.
     */
    case STRING;

    /**
     * Valida que el valor del parámetro tenga el formato de un UUID.
     */
    case UUID;

    /**
     * Valida que el valor del parámetro tenga el formato de un correo electrónico.
     */
    case EMAIL;
}
