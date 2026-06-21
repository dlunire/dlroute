<?php

declare(strict_types=1);

namespace DLRoute\Enums;

enum TokenType {

    case SEPARATOR;

    case LITERAL;

    case PARAM;

    case OPTIONAL;

    case QUERY_SEPARATOR;

    case QUERY_STRING;

    case END;
}