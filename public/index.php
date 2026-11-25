<?php
ini_set(
    option: "display_errors",
    value: 1
);

use DLRoute\Errors\RouteException;
use DLRoute\Requests\DLRoute;
use DLRoute\Test\TestController;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

header("Content-type: text/plain");

try {
    // DLRoute::post(
    //     route: '/api/v1/products///////////////////{page}/{rows?}',
    //     controller: fn() => ["status" => "Ok"]
    // );
    DLRoute::cli(
        route: '/products/{firstname?}/{lastname?}',
        controller: [TestController::class, 'index']
    );
} catch (RouteException $error) {
    echo $error->getMessage();
    echo "\n\n";
} finally {
    echo "Finalmente, no hay más nada que mostrar\n";
}
