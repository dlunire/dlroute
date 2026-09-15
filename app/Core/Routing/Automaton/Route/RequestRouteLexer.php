<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton\Route;

use DLRoute\Requests\DLOutput;
use DLRoute\Server\DLServer;

/**
 * Analiza léxicamente la URI de la petición HTTP actual.
 *
 * Extiende el analizador léxico base ({@see RouterLexer}) para tokenizar la ruta enviada por el cliente
 * HTTP en el momento de la instanciación, reutilizando el mismo autómata utilizado para analizar las rutas
 * registradas por el programador.
 *
 * A diferencia de una ruta registrada, la URI aquí analizada nunca contiene segmentos delimitados por llaves:
 * son los valores reales enviados por el cliente, no parámetros dinámicos.
 *
 * @package DLRoute\Core\Routing\Automaton\Route
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
final class RequestRouteLexer extends RouterLexer {

    /**
     * Tokens capturados con la metadata de la ruta registrada por el programador.
     *
     * @var array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int}> $route_tokens
     */
    private readonly array $route_tokens;

    /**
     * Cantidad de tokens de la ruta enviada por el cliente HTTP.
     *
     * @var int $request_token_quantity
     */
    private readonly int $request_token_quantity;

    /**
     * Cantidad de tokens de la ruta registrada.
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
     * Ruta registrada que coincide con la petición actual.
     *
     * Contiene la ruta registrada cuyo patrón ha sido reconocido como
     * coincidente con la semántica de la petición procesada por el analizador
     * léxico.
     *
     * El valor se establece cuando el procesamiento determina una ruta
     * registrada coincidente y permanece como `null` mientras no exista una
     * coincidencia.
     *
     * Ejemplo:
     *
     *     Petición:        /profile/1200
     *     Ruta coincidente: /profile/{algo}
     *
     * @var ?string
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
     * @param array<int, array{lexeme: string, length: int, optional: boolean, tokentype: TokenType, offset: int}> $tokens Tokens de la ruta registrada por el desarrollador.
     */
    public function __construct(array $tokens) {
        parent::__construct(DLServer::get_route());
        $this->route_tokens = $tokens;
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

        /** @var array<string> $tokens Lexemas extraídos de cada token */
        $tokens = [];

        foreach ($request_tokens as $token) {
            $tokens[] = $token['lexeme'];
        }

        return $tokens;
    }

    /**
     * Carga los valores asociados a los parámetros de la petición HTTP.
     *
     * Obtiene los tokens de la URI enviada por el cliente y los relaciona con los tokens de
     * tipo {@see TokenType::PARAM} de la ruta registrada.
     *
     * Si la cantidad de tokens de la petición no coincide con la cantidad de tokens de la ruta registrada,
     * no se cargan valores de parámetros.
     *
     * @return void
     */
    private function load_params_values(): void {
        /** @var array<string,string> $params */
        $params = [];

        /** @var non-empty-string $route */
        $route = "";

        /**
         * Componentes de ruta
         * 
         * @var string[] $route_components
         */
        $route_components = [];

        /** @var array<string> $request_tokens */
        $request_tokens = $this->get_request_tokens();

        $this->request_token_quantity = \count($request_tokens);

        if ($this->request_token_quantity !== $this->route_tokens_quantity) {
            $this->params_values = $params;

            $this->matched_route = null;
            $this->static_route = null;

            return;
        }

        foreach ($this->route_tokens as $key => $token) {
            /** @var TokenType $type */
            $type = $token['tokentype'];

            /** @var non-empty-string $lexeme */
            $lexeme = $token['lexeme'];

            $route_components[] = $lexeme;

            $this->remove_bracket($lexeme);

            if ($type !== TokenType::PARAM) continue;
            $params[$lexeme] = $request_tokens[$key] ?? null;
        }

        /**
         * Rutas registradas
         * 
         * @var non-empty-string $route
         */
        $route = "/" . join("/", $route_components);

        $this->static_route = $this->get_uri() === $route
            ? $route    
            : null;

        $this->matched_route = $this->static_route === null && \count($params) > 0
            ? $route
            : null;

        $test = [
            "static_route" => $this->static_route,
            "matched_route" => $this->matched_route,
            "params" => $params
        ];

        $this->params_values = $params;
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
