<?php

require_once("user-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;

$app->get('/user', function (ServerRequestInterface $request, ResponseInterface $response) {
    if(!$request->hasHeader('Authorization') || !Auth::checkToken($request->getHeader('Authorization')[0])) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'INVALID_ACCESS']);
    }

    return jsonResponse($response, 200, []);
});
