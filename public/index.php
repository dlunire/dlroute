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
        route: '/products/{firstname?}/{lastname?}',
        controller: [TestController::class, 'index'],
        mimetype: 'text/plain'
    );
    Route::cli(
        route: '/products/{firstname?}/{lastname?}',
        controller: [TestController::class, 'index']
    );
} catch (RouteException $error) {
    echo $error->getMessage();
    echo "\n\n";
}


AbstractRoute::dispatch();
