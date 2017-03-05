<?php

require_once("user-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;

$app->get('/user', function (ServerRequestInterface $request, ResponseInterface $response) {
    if(!$request->hasHeader('Authorization')) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'INVALID_ACCESS']);
    }

    $auth = Auth::checkToken($request->getHeader('Authorization')[0]);

    if(!$auth) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'ACCESS_TOKEN_INVALID']);
    }

    $uc = new userController();

    $user = $uc->loadUser($auth['user']);

    return jsonResponse($response, 200, $user);
});
