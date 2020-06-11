<?php

session_start();

require __DIR__ . "/vendor/autoload.php";

use gingerberry\api\v1\handler\PresentationHandler;
use gingerberry\router\Request;
use gingerberry\router\Router;

$router = new Router(new Request());

$pptHandler = new PresentationHandler($router);

$pptHandler->discoverEndpoints();
