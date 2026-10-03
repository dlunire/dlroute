<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Enums\Methods;
use DLRoute\Requests\DLOutput;
use DLRoute\Server\DLServer;

/**
 * Reescribiendo por completo este analizador semántico que utiliza el analizador léxico `RouterLexer`.
 * 
 * La documentación todavía no se encuentra completa, por lo que cambiará en cuanto se haya 
 * terminado de escribir el código fuente.
 *
 * @package DLRoute\Core\Routing\Automaton\Route
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RequestRouteLexer extends RouterLexer {

    /**
     * Cantidad de tokens de la ruta enviada por el cliente HTTP.
     *
     * @var int $request_token_quantity
     */
    private readonly int $request_token_quantity;

    /**
     * Cantidad de tokens de la ruta registrada por el desarrollador.
     *
     * @var int $route_tokens_quantity
     */
    private readonly int $route_tokens_quantity;

    /**
     * Valores asociados a los parámetros de la petición.
     *
     * @var array<string, string|null> $params_values
     */
    private readonly array $params_values;

    /**
     * Ruta compuesta por segmentos dinámicos (no todos los segmentos son dinámicos) que coinciden
     * con la ruta de la petición hecha por el cliente HTTP.
     *
     * @var string|null
     */
    private readonly ?string $matched_route;

    /**
     * Ruta registrada compuesta exclusivamente por segmentos estáticos.
     *
     * Contiene la ruta registrada que no define parámetros normales ni parámetros opcionales, por lo que
     * su patrón está constituido únicamente por segmentos literales.
     *
     * Este valor permite identificar directamente una ruta estática durante la resolución de la petición,
     * sin necesidad de procesar parámetros de ruta.
     *
     * Ejemplo:
     *
     *     /profile
     *
     * Las rutas como `/profile/{id}` o `/profile/{id?}` no corresponden a este valor.
     *
     * @var ?string $static_route
     */
    private readonly ?string $static_route;


    /**
     * Construye el analizador léxico de la petición HTTP actual.
     *
     * Toma la ruta de la petición actual mediante {@see DLServer::get_route()}
     * y ejecuta de inmediato el análisis léxico sobre ella.
     *
     * Recibe los tokens de una ruta registrada y los métodos HTTP de la ruta
     * y de la petición para permitir su comparación durante el reconocimiento.
     * La comparación de método ocurre en {@see self::load_params_values()}
     * confrontando `$method` (método de la ruta registrada) contra el resultado
     * de `$this->get_method()` (método de la petición actual, ya resuelto por
     * la clase base a partir de `$request_method`); ninguna clave relacionada
     * con el método HTTP se espera dentro de `$tokens`.
     *
     * @param array<int, array{
     *     lexeme: string,
     *     length: int,
     *     optional: boolean,
     *     tokentype: TokenType,
     *     offset: int,
     *     method: Methods
     * }> $route_tokens Tokens de la ruta registrada.
     * 
     * @param bool $has_param Indica si la ruta a analizar contiene segmentos dinámicos (parámetros)
     */
    public function __construct(
        private readonly array $route_tokens,
        private readonly bool $has_param = false
    ) {
        parent::__construct(uri: DLServer::get_route());
        $this->scanner();
        $this->init();
    }

    /**
     * Inicializa el estado de la solicitud.
     *
     * Calcula la cantidad de tokens de la ruta registrada y carga los valores
     * correspondientes a los parámetros obtenidos de la solicitud.
     *
     * Este método se ejecuta durante la inicialización del objeto y establece
     * el estado derivado necesario para las consultas posteriores.
     *
     * @return void
     */
    private function init(): void {
        $this->route_tokens_quantity = \count($this->route_tokens);
        $this->load_params_values();
    }

    /**
     * Devuelve los segmentos de la URI de la petición HTTP actual.
     *
     * Extrae únicamente el lexema de cada token capturado durante el análisis
     * léxico, descartando el resto de los metadatos del token.
     *
     * @return array<string> Segmentos de la ruta de la petición actual, en el
     * orden en que aparecen en la URI.
     */
    private function get_request_tokens(): array {
        /** @var array $request_tokens Tokens capturados de la URI enviada por el cliente HTTP */
        $request_tokens = $this->get_tokens();

        print_r($request_tokens);
        exit;

        /** @var array<string> $tokens Lexemas extraídos de cada token */
        $tokens = [];

        foreach ($request_tokens as $token) {
            $tokens[] = $token['lexeme'];
        }

        return $tokens;
    }

    /**
     * Permite cargar los parámetros de la petición por medio del autómata. Este método
     * se encuentra en desarrollo en este momento, por lo que esta documentación se 
     * actualizalizará en cuanto esté completamente terminado.
     *
     * @return void
     */
    private function load_params_values(): void {
    }

    /**
     * Remueve las llaves y signos de interrogación del token marcado como parámetro
     *
     * @param string $lexeme Lexema capturado durante el análisis léxico de la ruta.
     * @return void
     */
    private function remove_bracket(string &$lexeme): void {
        $lexeme = \trim($lexeme, " \n\r\t\v\x00{}\?");
    }

    /**
     * Devuelve los parámetros de la petición. Es decir, los valores de las
     * rutas dinámicas capturados a partir de la URI enviada por el cliente HTTP.
     *
     * @return array<string, string|null> Valores de los parámetros indexados
     * por el nombre definido en la ruta registrada.
     */
    public function get_params_values(): array {
        return $this->params_values;
    }

    /**
     * Devuelve la ruta que ha hecho match con la ruta de la petición, sin importar
     * si son rutas dinámicas o estáticas, pero dando prioridad a las rutas estáticas.
     *
     * @return string|null
     */
    public function get_matched_route(): ?string {
        return $this->static_route ?? $this->matched_route;
    }
}
