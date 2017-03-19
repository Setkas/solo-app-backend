<?php

require_once("login-controller.php");

use Commons\Authorization\Auth;
use Firebase\JWT\JWT;
use Moment\Moment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validator;
use Commons\Variables\LockKeys;

$app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    $params = $request->getParsedBody();

    $loginValidator = Validator::key('practice', Validator::stringType()
        ->length(1, 10)
        ->noWhitespace())
        ->key('user', Validator::stringType()
            ->length(1, 9)
            ->noWhitespace())
        ->key('password', Validator::stringType()
            ->length(1, 255)
            ->noWhitespace());

    if (!$loginValidator->validate($params)) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $lc = new loginController();
    $result = $lc->login($params['practice'], $params['user'], $params['password']);

    if ($result === false) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_LOGIN_DETAILS"
        ]);
    } else {
        if ($result === null) {
            return jsonResponse($response, 401, [
                "code" => 401,
                "message" => "PRACTICE_VALIDITY_EXPIRED"
            ]);
        }
    }

    return jsonResponse($response, 200, $result);
});

$app->get('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    if (!$request->hasHeader('Authorization')) {
        return jsonResponse($response, 401, [
            'code' => 401,
            'message' => 'INVALID_ACCESS'
        ]);
    }

    $auth = Auth::checkToken($request->getHeader('Authorization')[0]);

    if (!$auth) {
        return jsonResponse($response, 401, [
            'code' => 401,
            'message' => 'ACCESS_TOKEN_INVALID'
        ]);
    }

    return jsonResponse($response, 200, [
        'code' => 200,
        'message' => "TOKEN_VALID"
    ]);
});

$app->post('/password-reset', function (ServerRequestInterface $request, ResponseInterface $response) {
    $params = $request->getParsedBody();

    $loginValidator = Validator::key('practice', Validator::stringType())
        ->key('user', Validator::stringType())
        ->key('url', Validator::url());

    if (!$loginValidator->validate($params)) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $lc = new loginController();

    $practiceUser = $lc->findPracticeUser($params['practice'], $params['user']);

    if ($practiceUser === false || $practiceUser === null) {
        return jsonResponse($response, 404, [
            "code" => 404,
            "message" => "NON_EXISTENT_COMBINATION"
        ]);
    }

    $token = $lc->resetPassword($practiceUser["id_practice"], $practiceUser["id_user"]);

    if ($token === false) {
        return jsonResponse($response, 500, [
            "code" => 500,
            "message" => "PASSWORD_RESET_FAILED"
        ]);
    }

    $resetUrl = $params['url'] . "token=" . $token['jwt'];

    //@TODO: Send email about password reset

    return jsonResponse($response, 200, [
        'code' => 200,
        'message' => 'PASSWORD_RESET_SUCCESS',
        'expire' => $token['expire'],
        'token' => $token['jwt']
    ]);
});

$app->post('/password-update', function (ServerRequestInterface $request, ResponseInterface $response) {
    $params = $request->getParsedBody();

    $loginValidator = Validator::key('token', Validator::stringType())
        ->key('password', Validator::regex('/^([a-zA-Z0-9]{8,30})$/'));

    if (!$loginValidator->validate($params)) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $lc = new loginController();

    $result = $lc->updatePassword($params['token'], $params['password']);

    if ($result === false) {
        return jsonResponse($response, 500, [
            "code" => 500,
            "message" => "PASSWORD_UPDATE_ERROR"
        ]);
    }

    return jsonResponse($response, 200, [
        'code' => 200,
        'message' => 'PASSWORD_UPDATE_SUCCESS'
    ]);
});
