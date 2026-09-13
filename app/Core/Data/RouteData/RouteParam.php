<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

use DLRoute\Errors\RouteException;

/**
 * Representa los parámetros de una ruta resueltos por el enrutador.
 *
 * Sustituye al uso de `stdClass` (`(object) [...]`) para exponer los parámetros capturados de una ruta a los
 * controladores, ofreciendo un tipo específico que permite verificar su procedencia mediante `instanceof`, en
 * lugar de aceptar cualquier `object` sin garantía de origen.
 *
 * El acceso a un campo no definido devuelve `null` en lugar de lanzar una excepción, preservando el mismo
 * comportamiento que ya tenían los controladores existentes al leer parámetros opcionales mediante
 * `$params->campo ?? null`.
 *
 * Los campos se cargan una única vez durante la construcción. Cualquier intento de asignar una propiedad
 * nueva o existente sobre la instancia, ya sea un campo declarado en `$fields` o una propiedad dinámica ajena
 * a él, es rechazado mediante `RouteException`, garantizando que la instancia permanezca inmutable después de
 * construida.
 *
 * @package DLRoute\Core\Data\RouteData
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RouteParam {

    /**
     * Construye los parámetros de una ruta a partir de los campos ya resueltos
     * por el enrutador.
     *
     * @param array $fields Campos a ser cargados, indexados por nombre de
     * parámetro.
     */
    public function __construct(private readonly array $fields = []) {
    }

    /**
     * Impide la asignación de cualquier propiedad sobre la instancia.
     *
     * `RouteParam` es inmutable una vez construida: los parámetros de la ruta
     * ya vienen resueltos por el enrutador en el momento de la instanciación,
     * por lo que no existe un caso legítimo para modificarlos después.
     *
     * @param string $name Nombre de la propiedad que se intentó asignar.
     * @param mixed $value Valor que se intentó asignar.
     * @return never
     *
     * @throws RouteException Siempre que se invoque este método.
     */
    public function __set(string $name, mixed $value): never {
        throw new RouteException("No está permitido escribir propiedades nuevas", 500);
    }

    /**
     * Devuelve el valor de un parámetro por nombre.
     *
     * Si el parámetro solicitado no fue capturado por el enrutador, devuelve
     * `null` en lugar de lanzar una excepción o una advertencia, permitiendo su
     * uso directo en parámetros opcionales (por ejemplo, `$params->id ?? null`).
     *
     * @param string $name Nombre del parámetro a consultar.
     * @return mixed Valor del parámetro, o `null` si no fue definido.
     */
    public function __get(string $name): mixed {
        return $this->fields[$name] ?? null;
    }
}
