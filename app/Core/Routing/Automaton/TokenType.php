<?php

declare(strict_types=1);

namespace DLRoute\Core\Routing\Automaton;

/**
 * Tipos de token producidos por el analizador léxico de rutas.
 *
 * Clasifica cada segmento de una URI durante el proceso de tokenización,
 * distinguiendo entre texto literal y segmentos paramétricos.
 *
 * Ejemplos de tokenización:
 * ```
 * /users/profile   → [TEXT_PLAIN("/users/profile")]
 * /users/{id}      → [TEXT_PLAIN("/users/"), PARAM("id")]
 * /posts/{slug?}   → [TEXT_PLAIN("/posts/"), PARAM("slug")]
 * ```
 *
 * @package DLRoute\Core\Routing\Automaton
 *
 * @version v1.0.6 (release)
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright (c) 2026 David E Luna M
 * @license MIT
 */
enum TokenType {

    /** Segmento de texto literal en la URI, sin variación dinámica. */
    case TEXT_PLAIN;

    /** Segmento paramétrico en la URI, puede ser obligatorio u opcional. */
    case PARAM;
}
