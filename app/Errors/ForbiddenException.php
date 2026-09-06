<?php

declare(strict_types=1);

namespace DLRoute\Errors;

use Exception;

/**
 * HTTP 403 Forbidden exception.
 *
 * Represents an HTTP request that has been understood by the server,
 * but the client is not permitted to access the requested resource.
 *
 * @package DLRoute
 * @author David Eduardo Luna Montilla <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David Eduardo Luna Montilla
 * @license AGPL-3.0-or-later
 */
class ForbiddenException extends Exception {
    /**
     * Creates a new forbidden exception.
     *
     * @param string $message Exception message.
     */
    public function __construct(string $message = 'Forbidden') {
        parent::__construct($message, 403);
    }
}