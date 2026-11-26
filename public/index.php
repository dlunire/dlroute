<?php
ini_set(
    option: "display_errors",
    value: 1
);

use DLRoute\Errors\RouteException;
use DLRoute\Requests\Route;
use DLRoute\Requests\AbstractRoute;
use DLRoute\Test\TestController;

require_once dirname(
    path: __DIR__
) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

header(
    header: "Content-type: text/plain"
);


try {
    Route::get(
        route: '/persons/{firstname?}/{lastname?}',
        controller: [TestController::class, 'persons'],
        mimetype: 'application/json'
    );
    Route::cli(
        route: '/add/{domain?}', // Una prueba para Claude
        controller: [TestController::class, 'index']
    );
} catch (RouteException $error) {
    http_response_code(
        response_code: $error->getCode()
    );
    echo $error->getMessage();
    echo "\n\n";
    exit;
} finally {
    AbstractRoute::dispatch();
}