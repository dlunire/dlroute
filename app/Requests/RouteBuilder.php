<?php

declare(strict_types=1);

namespace DLRoute\Requests;

use DLRoute\Enums\Type;
use DLRoute\Errors\RouteException;
use OutOfBoundsException;

final class RouteBuilder {

    /**
     * Permite establecer el nombre de las variables que se establecerá en el motor
     * de plantillas de DLUnire o de cualquier otro framework
     *
     * @var array<string,mixed> $varnames
     */
    private array $varnames = [];

    /**
     * Permite definir `mimetype` que se será enviará a través de la cabecera HTTP.
     *
     * @var string|null $mimetype
     */
    private ?string $mimetype = null;

    /**
     * Tipo de datos para los parámetros de la petición
     *
     * @var array<string, Type> $types
     */
    private array $types = [];

    /**
     * Filtra por expresiones regulares
     *
     * @var array<string,string> $filter_by_regex
     */
    private array $filter_by_regex = [];

    /**
     * Permite almacenar los nombres de rutas registradas.
     * 
     * @var array<string,string> $route_names
     */
    private array $route_names = [];


    public function __construct(
        private readonly string $route
    ) {
    }

    public function set_varnames(array $varnames = []): self {
        $this->varnames = $varnames;
        return $this;
    }

    public function set_mimetype(?string $mimetype = null): self {
        $this->mimetype = $mimetype === null
            ? null
            : \trim($mimetype);

        return $this;
    }

    public function get_route(): string {
        return $this->route;
    }

    public function set_type(array $types): self {

        foreach ($types as $key => $value) {
            if (!\is_string($key)) {
                throw new RouteException(
                    "El nombre del parámetro debe ser una cadena de texto; se recibió «" . \gettype($key) . "»",
                    500
                );
            }

            if (!($value instanceof Type)) {
                throw new RouteException(
                    "El parámetro «{$key}» debe recibir un caso del enumerador «Type» (por ejemplo, «Type::INTEGER»); se recibió «" . (\is_object($value) ? \get_class($value) : \gettype($value)) . "»",
                    500
                );
            }
        }

        $this->types = $types;

        return $this;
    }

    /**
     * Devuelve los tipos 
     *
     * @return array
     */
    public function get_types(): array {
        return $this->types;
    }

    /**
     * Establece el nombre de la ruta registrada
     *
     * @param string $name
     * @return self
     */
    public function set_name(string $name): self {
        $this->route_names[$name] = $this->route;
        return $this;
    }

    /**
     * Filtra los parámetros por expresiones regulares
     *
     * @param array $regexs Expresiones regulares
     * @return self
     */
    public function filter_by_refex(array $regexs): self {

        foreach ($regexs as $key => $value) {
            if (!\is_string($key)) {
                throw new RouteException(
                    message: "El nombre del parámetro debe ser un 'string'. Se recibió '" . \gettype($key) . "'",
                    code: 500
                );
            }

            if (!\is_string($value)) {
                throw new RouteException(
                    message: "Se esperaba un 'string' como valor de la expresión regular. Se recibió '" . \gettype($value) . "'",
                    code: 500
                );
            }
        }

        $this->filter_by_regex = $regexs;
        return $this;
    }
}
