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

    /**
     * Espacio en blanco
     * 
     * @var non-empty-string
     */
    public const WHITE_SPACE = "\x20";

    /**
     * Barra diagonal derecha `/`
     * 
     * @var non-empty-string
     */
    public const SLASH = "\x2f";

    /**
     * Separador entre parámetros del querystring.
     *
     * Corresponde al carácter «&» (0x26 en ASCII), que delimita cada par
     * «nombre=valor» en la cadena de parámetros de la petición HTTP.
     *
     * Ejemplo: en «nombre=David&rol=admin», el byte «&» indica al autómata
     * que el parámetro actual ha terminado y comienza uno nuevo.
     *
     * @var string
     */
    public const QUERY_SEPARATOR = "\x26";

    /**
     * Separador entre el nombre y el valor de un parámetro del querystring.
     *
     * Corresponde al carácter «=» (0x3D en ASCII), que delimita el nombre
     * del parámetro de su valor en cada par «nombre=valor».
     *
     * Ejemplo: en «nombre=David&rol=admin», el byte «=» indica al autómata
     * que el nombre del parámetro ha terminado y comienza su valor.
     *
     * @var string
     */
    public const QUERY_ASSIGN = "\x3d";

    /**
     * Carácter de guion bajo (underscore).
     *
     * Representa el valor hexadecimal "\x5f". Se utiliza durante la fase de
     * normalización o análisis léxico para sustituir los espacios en blanco
     * presentes en los nombres de las claves (keys). Esto garantiza que el
     * analizador semántico reciba identificadores válidos, continuos y
     * seguros para su posterior procesamiento o asignación.
     *
     * @var string
     */
    public const UNDERSCORE = "\x5f";
}
