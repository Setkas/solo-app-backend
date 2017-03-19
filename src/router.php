<?php

use Psr\Http\Message\ResponseInterface;

function jsonResponse(ResponseInterface $response, $code = 200, array $data = null) {
    $newResponse = $response->withStatus($code)
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    if ($data === null) {
        return $newResponse;
    } else {
        return $newResponse->write(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}

//Register all route groups
require_once("./src/routes/login/login-handler.php");
require_once("./src/routes/user/user-handler.php");
require_once("./src/routes/practice/practice-handler.php");
require_once("./src/routes/language/language-handler.php");
require_once("./src/routes/position/position-handler.php");
require_once("./src/routes/client/client-handler.php");
require_once("./src/routes/setup/setup-handler.php");
require_once("./src/routes/term/term-handler.php");
