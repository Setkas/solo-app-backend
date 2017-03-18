<?php

require_once("client-controller.php");

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use \Commons\Authorization\Auth;
use Respect\Validation\Validator;

$app->get('/client/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

    if (!Validator::numeric()
        ->length(1, 9)
        ->validate($args['id'])
    ) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $cc = new clientController();


    $client = $cc->loadClient($auth['practice'], $args['id']);

    if ($client === false) {
        return jsonResponse($response, 500, [
            'code' => 500,
            'message' => 'CLIENT_LOAD_ERROR'
        ]);
    }

    return jsonResponse($response, 200, $client);
});

$app->get('/client-search', function (ServerRequestInterface $request, ResponseInterface $response) {
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

    $params = $request->getQueryParams();

    if (!isset($params["query"]) || !Validator::stringType()
            ->validate($params['query'])
    ) {
        return jsonResponse($response, 400, [
            'code' => 400,
            'message' => 'INVALID_PARAMETERS_PROVIDED'
        ]);
    }

    $cc = new clientController();

    $clients = $cc->findClients($auth['practice'], $params['query']);

    if ($clients === false) {
        return jsonResponse($response, 404, [
            'code' => 404,
            'message' => 'NO_CLIENTS_FOUND'
        ]);
    }

    if (isset($params["limit"]) && Validator::numeric()
            ->validate($params['limit']) && $params["limit"] > 0
    ) {
        $clients = array_slice($clients, 0, $params["limit"]);
    }

    return jsonResponse($response, 200, $clients);
});

$app->post('/client', function (ServerRequestInterface $request, ResponseInterface $response) {
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

    $params = $request->getParsedBody();

    $clientValidator = Validator::key('name', Validator::stringType())
        ->key('surname', Validator::stringType())
        ->key('address', Validator::stringType())
        ->key('phone', Validator::stringType(), false)
        ->key('birth_date', Validator::stringType())
        ->key('email', Validator::email(), false)
        ->key('gender', Validator::numeric()
            ->length(1));

    if (!$clientValidator->validate($params)) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $cc = new clientController();

    if (!$cc->newClient($auth['practice'], $params)) {
        return jsonResponse($response, 500, [
            'code' => 500,
            'message' => 'CLIENT_CREATION_ERROR'
        ]);
    }

    return jsonResponse($response, 200, [
        'code' => 200,
        'message' => 'NEW_CLIENT_CREATED'
    ]);
});

$app->patch('/client/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

    $params = $request->getParsedBody();

    $clientValidator = Validator::key('name', Validator::stringType(), false)
        ->key('surname', Validator::stringType(), false)
        ->key('address', Validator::stringType(), false)
        ->key('phone', Validator::stringType(), false)
        ->key('birth_date', Validator::date(), false)
        ->key('email', Validator::email(), false)
        ->key('gender', Validator::numeric()
            ->length(1), false)
        ->key('changes_reminder', Validator::date(), false)
        ->key('password', Validator::regex('/^([a-zA-Z0-9]{8,30})$/'), false);

    if (!$clientValidator->validate($params)) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    if (!Validator::numeric()
        ->length(1, 9)
        ->validate($args['id'])
    ) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $cc = new clientController();

    if (!$cc->updateClient($auth['practice'], $args['id'], $params)) {
        return jsonResponse($response, 500, [
            'code' => 500,
            'message' => 'CLIENT_UPDATE_ERROR'
        ]);
    }

    return jsonResponse($response, 200, [
        'code' => 200,
        'message' => 'CLIENT_EDIT_SUCCESS'
    ]);
});

$app->delete('/client/{id}', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
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

    if (!Validator::numeric()
        ->length(1, 9)
        ->validate($args['id'])
    ) {
        return jsonResponse($response, 400, [
            "code" => 400,
            "message" => "INVALID_PARAMETERS_PROVIDED"
        ]);
    }

    $cc = new clientController();

    if (!$cc->deleteClient($auth['practice'], $args['id'])) {
        return jsonResponse($response, 500, [
            'code' => 500,
            'message' => 'CLIENT_DELETE_ERROR'
        ]);
    }

    return jsonResponse($response, 200, [
        'code' => 200,
        'message' => 'CLIENT_DELETED'
    ]);
});

