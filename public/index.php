<?php
ini_set("display_errors", 1);

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
    DLRoute::get(
        route: '/products/{name?}/{rows?}',
        controller: [TestController::class, 'index']
    );
} catch (RouteException $error) {
    echo $error->getMessage();
} finally {
    echo "\n\nFinalmente, no hay más nada que mostrar\n";
}