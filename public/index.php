<?php
// ini_set("display_errors", 1);

use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;
use DLRoute\Errors\RouteException;
use DLRoute\Server\DLServer;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// header("Content-type: text/plain");

$r = RequestURILexer::from();

print_r($r->get_route_resolution());

echo "\nURL Base: " . DLServer::get_base_url();
echo PHP_EOL;

try {

    $method = DLServer::get_method();
    echo "type: " . gettype($method);
    
    echo 'method: ' . DLServer::get_method() . PHP_EOL;
    
    var_dump($method);
} catch (RouteException $error) {
    echo $error->getMessage();
} finally {
    echo "Finalmente, no hay nada que mostrar\n";
}

