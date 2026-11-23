<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

use DLRoute\Errors\RouteException;

/**
 * Representa los parámetros capturados durante la resolución de una ruta.
 *
 * Encapsula una colección de objetos {@see Param}, indexados por el nombre de cada parámetro,
 * y permite consultarlos mediante acceso a propiedades virtuales.
 *
 * La instancia se crea exclusivamente mediante la fábrica estática {@see RouteParam::new()},
 * ya que el constructor es privado.
 *
 * Implementa los métodos mágicos {@see __get()} y {@see __set()} para proporcionar una interfaz
 * de acceso controlado a los parámetros:
 *
 * - La lectura devuelve siempre una instancia de {@see Param}. Si el parámetro existe, se devuelve
 *   el objeto asociado; si no existe, se devuelve un {@see Param} vacío, con `name`, `value` y
 *   `decoded_value` en `null`. Quien consulta nunca recibe `null` y no necesita comprobar la
 *   existencia del parámetro antes de acceder a él.
 * - La escritura está restringida y produce una {@see RouteException}, evitando que los
 *   parámetros capturados sean modificados mediante asignaciones externas.
 *
 * Sustituye el uso de `stdClass` para representar los parámetros de una ruta, proporcionando
 * un tipo específico que permite verificar su procedencia mediante `instanceof` y mantener
 * una representación tipada de los datos capturados.
 *
 * @package DLRoute\Core\Data\RouteData
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RouteParam {

    /**
     * Construye una instancia con los parámetros capturados por el enrutador.
     *
     * El constructor es privado: para crear una instancia se debe usar {@see RouteParam::new()}.
     *
     * Los campos se reciben indexados por el nombre de cada parámetro y contienen
     * instancias de {@see Param}. La colección se establece durante la construcción
     * y no puede reasignarse posteriormente.
     *
     * @param array<string, Param> $fields Parámetros capturados, indexados por nombre.
     */
    private function __construct(
        private readonly array $fields = []
    ) {
    }

    /**
     * Impide la asignación de propiedades sobre la instancia.
     *
     * Este método intercepta los intentos de escritura mediante el acceso a propiedades
     * virtuales, rechazando cualquier modificación de los parámetros almacenados.
     *
     * @param string $name Nombre de la propiedad que se intenta modificar.
     * @param mixed $value Valor que se intenta asignar.
     *
     * @return never
     *
     * @throws RouteException Siempre que se intenta asignar una propiedad.
     */
    public function __set(string $name, mixed $value): never {
        throw new RouteException(
            message: "No está permitido escribir propiedades nuevas.",
            code: 500
        );
    }

    /**
     * Recupera un parámetro mediante su nombre.
     *
     * Busca el parámetro solicitado en la colección de campos y devuelve su instancia
     * {@see Param}. Si el nombre no existe, devuelve un {@see Param} vacío (con `name`,
     * `value` y `decoded_value` en `null`) en lugar de `null`.
     *
     * Este comportamiento permite consultar parámetros opcionales sin provocar excepciones
     * ni advertencias por claves inexistentes, y sin comprobar su existencia previamente.
     *
     * @param string $name Nombre del parámetro que se desea consultar.
     *
     * @return Param Instancia del parámetro capturado, o un {@see Param} vacío si no existe.
     */
    public function __get(string $name): Param {
        /** @var Param|null $param */
        $param = $this->fields[$name] ?? null;

        return $param instanceof Param
            ? $param
            : Param::new(
                name: null,
                value: null,
                decoded_value: null
            );
    }

    /**
     * Crea una instancia con los parámetros capturados por el enrutador.
     *
     * Es el único punto de creación público, dado que el constructor es privado.
     *
     * @param array<string, Param> $fields Parámetros capturados, indexados por nombre.
     *
     * @return static Nueva instancia con los parámetros recibidos.
     */
    public static function new(array $fields = []): static {
        return new static($fields);
    }
}
