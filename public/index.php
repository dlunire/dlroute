<?php
ini_set("display_errors", 1);

use DLRoute\Errors\RouteException;
use DLRoute\Requests\DLRoute;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

header("Content-type: text/plain");

try {
    DLRoute::get(
        route: '/api/v1/products///////////////////{page}/{rows?}',
        controller: fn() => ["status" => "Ok"]
    );
} catch (RouteException $error) {
    echo $error->getMessage();
} finally {
    echo "\n\nFinalmente, no hay más nada que mostrar\n";
}