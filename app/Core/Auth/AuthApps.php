<?php

declare(strict_types=1);

namespace DLRoute\Core\Auth;

use DLAuth\Auth\DLAuth;
use DLRoute\Interfaces\Routing\RouteAuth;
use DLRoute\Requests\DLRoute;
use Override;

final class AuthApps extends DLAuth implements RouteAuth {
    /**
     * Marca las rutas que deben ser autenticadas.
     *
     * @var boolean $mark_routes
     */
    private bool $mark_routes = false;

    public function __construct(string $field = '__DLUNIRE__') {
        parent::__construct(field: $field);
    }

    #[Override]
    public function authenticated(callable $fn): void {
        parent::authenticated(fn: $fn);
    }

    #[Override]
    public function unauthenticated(callable $fn): void {
        parent::unauthenticated(fn: $fn);
    }

    public function require_auth(callable $fn): void {
        $auth = $this->get_session_data();

        DLRoute::set_authentication_context(
            session: $auth,
            requires_authentication: true
        );

        try {
            $fn();
        } finally {
            DLRoute::set_authentication_context(
                session: $auth,
                requires_authentication: false
            );
        }
    }
}
