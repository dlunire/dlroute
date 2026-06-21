<?php

declare(strict_types=1);

namespace DLRoute\Enums;

/**
 * Representa el tipo de un token capturado durante el análisis léxico
 * de una URI registrada por el desarrollador.
 *
 * `TokenType` es el vocabulario formal con el que el autómata del `RouteLexer`
 * descompone una ruta como `/{uuid?}/usuarios/{id}` en unidades atómicas antes
 * de registrarla en el despachador. El autómata emite exactamente un caso de
 * este enum por cada token detectado durante el escaneo byte a byte de la URI.
 *
 * A diferencia de `QueryStringTokenType` — que clasifica los tokens del
 * querystring de la petición HTTP entrante — `TokenType` clasifica los
 * segmentos estructurales de la URI definida en el código fuente.
 *
 * ---
 *
 * Flujo de tokens para `/api/{uuid?}/usuarios/{id}`:
 * ```
 * [0] SEPARATOR  → "/"
 * [1] LITERAL    → "api"
 * [2] SEPARATOR  → "/"
 * [3] OPTIONAL   → "uuid"
 * [4] SEPARATOR  → "/"
 * [5] LITERAL    → "usuarios"
 * [6] SEPARATOR  → "/"
 * [7] PARAM      → "id"
 * [8] END
 * ```
 *
 * @see \DLRoute\Route\RouteLexer         Autómata que consume este enum
 * @see \DLRoute\Enums\QueryStringTokenType Enum equivalente para el querystring
 *
 * @package DLRoute\Enums
 * @since   1.0.0
 */
enum TokenType {

    /**
     * Separador de segmento de ruta.
     *
     * Corresponde al carácter `/` cuando aparece como delimitador estructural
     * de la URI. El autómata lo usa para delimitar el inicio de cada segmento
     * y avanzar el cursor al siguiente bloque.
     *
     * ```
     * /{id}/usuarios
     * ─              ← SEPARATOR
     *       ─        ← SEPARATOR
     * ```
     */
    case SEPARATOR;

    /**
     * Segmento estático de la ruta.
     *
     * Representa texto fijo que debe coincidir exactamente, byte a byte, con
     * el segmento equivalente de la URI de la petición entrante. No contiene
     * llaves ni modificadores dinámicos.
     *
     * ```
     * /api/usuarios/{id}
     *  ───               ← LITERAL: "api"
     *      ────────      ← LITERAL: "usuarios"
     * ```
     */
    case LITERAL;

    /**
     * Parámetro dinámico obligatorio.
     *
     * Representa un segmento encerrado entre llaves sin el modificador `?`.
     * Debe estar presente en la URI de la petición; su ausencia impide que
     * la ruta coincida. El valor capturado queda disponible como propiedad
     * del objeto `$params` inyectado en el controlador.
     *
     * ```
     * /usuarios/{id}/perfil
     *            ──          ← PARAM: "id"
     * ```
     */
    case PARAM;

    /**
     * Parámetro dinámico opcional.
     *
     * Representa un segmento encerrado entre llaves con el modificador `?`.
     * Su presencia genera el registro simultáneo de dos rutas en el despachador:
     * una con el parámetro y otra sin él.
     *
     * ```
     * /productos/{uuid?}/detalle
     *             ────            ← OPTIONAL: "uuid"
     *
     * Registra:
     *   → /productos/detalle
     *   → /productos/{uuid}/detalle
     * ```
     *
     * Si el modificador `?` va seguido de cualquier carácter distinto de `}`,
     * el `RouteLexer` lanza un `RouteException` con la posición exacta del
     * byte problemático, el fragmento recibido y el formato correcto esperado:
     *
     * ```
     * // ❌ Inválido
     * /{ciencia?=algo}/usuarios
     *
     * RouteException: Se esperaba una llave de cierre (}) después del símbolo
     * «?» (posición 9). En su lugar, se recibió «?=algo}/usuarios».
     * Los parámetros opcionales deben tener el formato → «{parametro?}»
     * ```
     */
    case OPTIONAL;

    /**
     * Delimitador de inicio del querystring.
     *
     * Corresponde al carácter `?` cuando aparece fuera de una definición de
     * parámetro, marcando el límite entre la estructura de la ruta y el
     * querystring. A partir de este token, el análisis de la URI registrada
     * concluye y el control pasa al subsistema `QueryStringLexer`.
     *
     * ```
     * /api/usuarios?filtro=activo
     *              ─              ← QUERY_SEPARATOR
     * ```
     */
    case QUERY_SEPARATOR;

    /**
     * Contenido completo del querystring.
     *
     * Representa todo lo que sigue al `QUERY_SEPARATOR` en la URI registrada
     * por el desarrollador. Su análisis detallado — extracción de pares
     * nombre → valor, cálculo de offsets, normalización — es responsabilidad
     * del `QueryStringLexer`, no del `RouteLexer`.
     *
     * ```
     * /api/usuarios?filtro=activo&pagina=2
     *               ──────────────────────  ← QUERY_STRING: "filtro=activo&pagina=2"
     * ```
     */
    case QUERY_STRING;

    /**
     * Señal de terminación del análisis léxico.
     *
     * No representa ningún carácter de la URI. Es emitido cuando el cursor
     * del autómata alcanza `strlen($uri)`, indicando que la descomposición
     * en tokens ha concluido y la ruta puede ser registrada formalmente en
     * el despachador.
     *
     * ```
     * /api/{id}
     *          ← END (offset === size)
     * ```
     */
    case END;
}
