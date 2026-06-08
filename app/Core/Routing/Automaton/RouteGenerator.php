<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

use DLRoute\Errors\RouteException;

/**
 * Generador de variantes de rutas a partir de parámetros opcionales.
 *
 * Extiende el analizador léxico {@see RouterLexer} para consumir los tokens
 * producidos y generar todas las variantes válidas de una URI cuando contiene
 * parámetros opcionales. Cada parámetro opcional produce una ruta adicional
 * que omite ese segmento y los siguientes opcionales.
 *
 * Ejemplo de generación:
 * ```php
 * $generator = new RouteGenerator('/users/{id}/{slug?}');
 * $generator->get_routes();
 * // Rutas generadas:
 * // ["/users/{id}", "/users/{id}/{slug}"]
 * ```
 *
 * @package DLRoute\Core\Routing\Automaton
 *
 * @version v1.0.6 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
final class RouteGenerator extends RouterLexer {

    /**
     * Rutas generadas a partir de parámetros opcionales.
     * 
     * @var non-empty-string[]
     */
    private array $routes = [];

    /**
     * Inicializa el generador con la URI a procesar.
     *
     * Invoca al analizador léxico del padre para tokenizar la URI,
     * escanea los tokens y genera de inmediato todas las variantes
     * de ruta disponibles mediante {@see RouteGenerator::generate()}.
     *
     * @param string $uri URI del patrón de ruta a procesar.
     */
    public function __construct(string $uri) {
        parent::__construct($uri);
        $this->scanner();
        $this->generate();
    }

    /**
     * Genera todas las variantes de ruta a partir de los tokens producidos por el lexer.
     *
     * Recorre la lista de tokens e, cada vez que encuentra un parámetro opcional,
     * emite una ruta con los segmentos acumulados hasta ese punto. Al finalizar,
     * emite la ruta completa con todos los segmentos.
     *
     * @throws RouteException Si el tipo de token encontrado no es una instancia válida de {@see TokenType}.
     * @return void
     */
    private function generate(): void {

        /** @var array{lexeme: string, length: int, optional: bool, tokentype: TokenType, offset: int}[] $tokens */
        $tokens = $this->get_tokens();

        /** @var array $buffer */
        $buffer = [];

        foreach ($tokens as $token) {
            /** @var non-empty-string $lexeme */
            $lexeme = \strval($token['lexeme']);

            /** @var int $length */
            $length = \intval($token['length']);

            /** @var boolean $optional */
            $optional = \boolval($token['optional']);

            /** @var TokenType $tokentype */
            $tokentype = $token['tokentype'];

            /** @var int $offset */
            $ofsset = \intval($token['offset']);

            if (!$tokentype instanceof TokenType) {
                throw new RouteException("El token «{$lexeme}» es inesperado en la posición «{$ofsset}»", 500);
            }

            $this->remove_param($lexeme, $length);

            if ($optional && $tokentype === TokenType::PARAM) {
                $this->routes[] = self::SLASH . implode(self::SLASH, $buffer);
            }

            $buffer[] = $lexeme;
        }

        $this->routes[] = self::SLASH . implode(self::SLASH, $buffer);
    }

    /**
     * Remueve el marcador de opcionalidad `?` del lexema.
     *
     * Si el penúltimo carácter del lexema es `?`, lo elimina y conserva
     * el cierre `}`, normalizando el parámetro a su forma obligatoria.
     *
     * @param string  $lexeme Lexema a ser depurado, pasado por referencia.
     * @param integer $length Longitud en bytes del lexema.
     * @return void
     */
    private function remove_param(string &$lexeme, int $length): void {
        if (self::OPTIONAL_PARAMETER === $lexeme[$length - 2]) {
            $lexeme = \substr($lexeme, 0, $length - 2) . "}";
        }
    }

    /**
     * Devuelve todas las variantes de ruta generadas.
     *
     * Las rutas son producidas durante la construcción del objeto,
     * por lo que este método simplemente retorna el listado ya calculado.
     *
     * @return non-empty-string[] Lista de rutas generadas.
     */
    public function get_routes(): array {
        return $this->routes;
    }

    /**
     * Itera sobre las rutas generadas e invoca el callback para cada una.
     *
     * Permite registrar o procesar cada variante de ruta de forma declarativa
     * sin exponer directamente el listado interno.
     *
     * @param callable $callback Función invocada por cada ruta generada.
     *                           Recibe la ruta como único argumento `string`.
     * @return void
     */
    public function load_routes(callable $callback): void {
        foreach ($this->routes as $route) {
            $callback($route);
        }
    }
}
