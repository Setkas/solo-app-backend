<?php

require_once("login-controller.php");

use Commons\Authorization\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;

$app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    $params = $request->getParsedBody();

    $loginValidator = Validator::key('practice', Validator::stringType()->length(1, 10)->noWhitespace())
        ->key('user', Validator::stringType()->length(1, 9)->noWhitespace())
        ->key('password', Validator::stringType()->length(1, 255)->noWhitespace());

    if (!$loginValidator->validate($params)) {
        return jsonResponse($response, 400, ["code" => 400, "message" => "INVALID_PARAMETERS_PROVIDED"]);
    }

    $lc = new loginController();
    $result = $lc->login($params['practice'], $params['user'], $params['password']);

    if($result === false) {
        return jsonResponse($response, 400, ["code" => 400, "message" => "INVALID_LOGIN_DETAILS"]);
    }

    return jsonResponse($response, 200, $result);
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    if(!$request->hasHeader('Authorization')) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'INVALID_ACCESS']);
    }

    $auth = Auth::checkToken($request->getHeader('Authorization')[0]);

    if(!$auth) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'ACCESS_TOKEN_INVALID']);
    }

    return jsonResponse($response, 200, ['code' => 200, 'message' => "TOKEN_VALID"]);
});
