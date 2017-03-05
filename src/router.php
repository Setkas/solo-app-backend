<?php

use Psr\Http\Message\ResponseInterface;

function jsonResponse(ResponseInterface $response, $code = 200, array $data = null) {
    $newResponse = $response
        ->withStatus($code)
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

    if($data === null) {
        return $newResponse;
    } else {
        return $newResponse->write(json_encode($data));
    }
}

require_once("../src/routes/user/user-handler.php");