<?php

require_once("practice-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;

$app->get('/practice', function (ServerRequestInterface $request, ResponseInterface $response) {
    if(!$request->hasHeader('Authorization')) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'INVALID_ACCESS']);
    }

    $auth = Auth::checkToken($request->getHeader('Authorization')[0]);

    if(!$auth) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'ACCESS_TOKEN_INVALID']);
    }

    $pc = new practiceController();

    $practice = $pc->loadPractice($auth['practice']);

    if($practice === false) {
        return jsonResponse($response, 404, ['code' => 401, 'message' => 'PRACTICE_NOT_FOUND']);
    }

    return jsonResponse($response, 200, $practice);
});

$app->patch('/practice', function (ServerRequestInterface $request, ResponseInterface $response) {
    if(!$request->hasHeader('Authorization')) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'INVALID_ACCESS']);
    }

    $auth = Auth::checkToken($request->getHeader('Authorization')[0]);

    if(!$auth) {
        return jsonResponse($response, 401, ['code' => 401, 'message' => 'ACCESS_TOKEN_INVALID']);
    }

    $params = $request->getParsedBody();

    $pc = new practiceController();

    if(!$pc->editPractice($auth['practice'], $params)) {
        return jsonResponse($response, 500, ['code' => 500, 'message' => 'PRACTICE_EDIT_ERROR']);
    }

    return jsonResponse($response, 200, ['code' => 200, 'message' => 'PRACTICE_SAVED']);
});

$app->post('/practice', function (ServerRequestInterface $request, ResponseInterface $response) {
    $params = $request->getParsedBody();

    $pc = new practiceController();

    if(!$pc->newPractice($params)) {
        return jsonResponse($response, 500, ['code' => 500, 'message' => 'PRACTICE_CREATION_ERROR']);
    }

    //@TODO: Send email about practice creation

    return jsonResponse($response, 200, ['code' => 200, 'message' => 'PRACTICE_CREATED_EMAIL_SENT', 'email' => $params['contact_email']]);
});
