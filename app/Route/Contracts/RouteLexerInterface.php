<?php

declare(strict_types=1);

namespace DLRoute\Route\Contracts;

interface RouteLexerInterface {

    /**
     * Barra diagonal que separa la ruta en sus componentes
     * 
     * @var non-empty-string
     */
    public const SEPARATOR = "\x2f";

    /**
     * Marca de parámetro opcional en la definición de una ruta.
     * Se utiliza dentro de segmentos de ruta con la sintaxis `{param?}`
     * para indicar que el parámetro precedente puede estar ausente.
     *
     * @var non-empty-string
     */
    public const OPTIONAL_MARK = "\x3f";

    /**
     * Separador de query string en una URI.
     * Todo lo que sigue a este carácter forma parte de los parámetros
     * de consulta y debe ser excluido del análisis de segmentos de ruta.
     *
     * @var non-empty-string
     */
    public const QUERY_SEPARATOR = "\x3f";

    /**
     * Espacio en blanco a ser ignorado en el analizador léxico
     * 
     * @var non-empty-string
     */
    public const WHITE_SPACE = "\x20";

    /**
     * Subguión que será utilizado para reemplazar el espacio en blanco por él
     * durante el análisis léxio.
     * 
     * @var non-empty-string
     */ 
    public const UNDESCORE = "\x5f";
}
