<?php

require_once("login-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;

$app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    $params = $request->getParsedBody();

    $loginValidator = Validator::key('practice', Validator::stringType()->length(1, 5)->noWhitespace())
        ->key('user', Validator::stringType()->length(1, 5)->noWhitespace())
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
