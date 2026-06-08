<?php

declare(strict_types=1);

namespace DLRoute\Interfaces\Routing;

/**
 * Contrato para el analizador léxico de rutas.
 *
 * Define las constantes de los tokens utilizados durante el análisis
 * léxico de los patrones de ruta. Estas constantes representan los
 * caracteres especiales que delimitan y modifican los segmentos
 * paramétricos en una URI.
 *
 * Ejemplo de patrón analizado:
 * ```
 * /users/{id}     → parámetro obligatorio
 * /users/{id?}    → parámetro opcional
 * ```
 *
 * @package DLRoute\Interfaces\Routing
 *
 * @version v1.0.6 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
interface RouteLexerInterface {

    /** 
     * Llave de apertura `{` — marca el inicio de un segmento paramétrico en la ruta.
     * 
     * @var non-empty-string
     */
    public const BRACKET_OPEN = "\x7b";

    /**
     * Llave de cierre `}` — marca el fin de un segmento paramétrico en la ruta.
     *
     * @var non-empty-string
     */
    public const BRACKET_CLOSE = "\x7d";

    /**
     * Signo de interrogación `?` — indica que el parámetro precedente es opcional.
     *
     * @var non-empty-string 
     */
    public const OPTIONAL_PARAMETER = "\x3f";
}