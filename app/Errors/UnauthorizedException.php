<?php

declare(strict_types=1);

namespace DLRoute\Errors;

use Exception;

/**
 * HTTP 401 Unauthorized exception.
 *
 * Represents an HTTP request that requires valid authentication
 * credentials before the requested resource can be accessed.
 *
 * @package DLRoute
 * @license AGPL-3.0-or-later
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 */
class UnauthorizedException extends Exception {
    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $code = 401;

    /**
     * Creates a new unauthorized exception.
     *
     * @param string $message Exception message.
     */
    public function __construct(string $message = 'Unauthorized') {
        parent::__construct($message, 401);
    }
}
