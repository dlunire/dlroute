<?php

declare(strict_types=1);

namespace DLRoute\Core\Data\RouteData;

/**
 * Representa un parámetro extraído durante el análisis léxico y el enrutamiento.
 *
 * Conserva de forma separada el lexema original tal como llega en el flujo de
 * bytes (`$lexeme`) y su representación decodificada/traducida (`$decoded_lexeme`),
 * evitando mutaciones destructivas o pérdidas de trazabilidad en la capa de datos.
 *
 * @package DLRoute\Core\Data\RouteData
 */
final class Param {

    /**
     * Inicializa una instancia inmutable de parámetro de ruta.
     *
     * El constructor es privado para forzar la creación controlada a través de
     * {@see self::new()}.
     *
     * @param string|null $lexeme Lexema crudo capturado del segmento de ruta.
     * @param string|null $decoded_lexeme Lexema tras aplicar decodificación o traducción de bytes.
     */
    private function __construct(
        public readonly ?string $lexeme = null,
        public readonly ?string $decoded_lexeme = null
    ) {
    }

    /**
     * Crea una nueva instancia de {@see self}.
     *
     * @param string|null $lexeme Lexema crudo.
     * @param string|null $decoded_lexeme Lexema decodificado.
     * @return static
     */
    public static function new(?string $lexeme = null, ?string $decoded_lexeme = null): static {
        return new static(
            lexeme: $lexeme,
            decoded_lexeme: $decoded_lexeme
        );
    }
}
