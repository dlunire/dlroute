<?php

use DLRoute\Core\Routing\Automaton\Route\RequestURILexer;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$r = RequestURILexer::from();

print_r($r->get_route_resolution());